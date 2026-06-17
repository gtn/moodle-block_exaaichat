<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Keeps a block instance's uploaded documents in sync with an OpenAI vector store.
 *
 * @package    block_exaaichat
 * @copyright  2026 GTN Solutions https://gtn-solutions.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_exaaichat;

use OpenAI;
use OpenAI\Client;
use OpenAI\Exceptions\ErrorException;

defined('MOODLE_INTERNAL') || die();

require_once __DIR__ . '/../vendor/autoload.php';

class vector_store_sync {
    /**
     * Sync the block's uploaded documents (the "documents" file area) with a managed OpenAI vector
     * store, using OpenAI as the source of truth: the files currently in the store are listed and
     * compared with the local files by filename. Each store file keeps its filename in its
     * attributes (the store-file list does not return it otherwise), so the comparison needs no
     * local bookkeeping. New files are uploaded, files no longer present locally are removed.
     *
     * Matching is by filename: replacing a file with new content under the same name is not
     * re-synced (renames/additions/removals are).
     *
     * @return string the managed vector store id to persist on the block ('' if nothing is stored)
     */
    public static function sync(\context $context, object $config): string {
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'block_exaaichat', 'documents', 0, 'filename', false);

        $storeid = $config->managed_vector_store_id ?? '';
        $group = 'vectorsync:' . $context->instanceid;

        // No documents uploaded: delete any managed store (and its files), then there is nothing
        // left to sync.
        if (!$files) {
            if ($storeid) {
                logger::debug_grouped($group, 'no files, deleting vector store:', $storeid);
                static::cleanup($config);
            }
            return '';
        }

        $client = OpenAI::client(static::get_apikey($config));

        // Local files keyed by filename.
        $local = [];
        foreach ($files as $file) {
            $local[$file->get_filename()] = $file;
        }

        // Files currently in the store, keyed by the filename kept in their attributes. If the store
        // id is stale because the store was deleted in OpenAI (404), forget it so it is recreated
        // below. Any other API error is rethrown so we don't spawn a duplicate store on a transient
        // failure.
        $remote = [];
        if ($storeid) {
            try {
                logger::debug_grouped($group, 'listing vector store files:', $storeid);
                foreach ($client->vectorStores()->files()->list($storeid, ['limit' => 100])->data as $vsfile) {
                    $remote[$vsfile->attributes['filename'] ?? ''] = $vsfile->id;
                }
            } catch (ErrorException $e) {
                if ($e->getStatusCode() !== 404) {
                    throw $e;
                }
                logger::debug_grouped($group, 'vector store not found (404), recreating:', $storeid);
                $storeid = '';
            }
        }

        // Create the managed store on first upload, or to replace one deleted in OpenAI. Name it with
        // the course shortname (when the block lives under a course) so it is identifiable in OpenAI.
        if (!$storeid && $files) {
            global $DB;
            $coursecontext = $context->get_course_context(false);
            $shortname = substr($coursecontext ? $DB->get_field('course', 'shortname', ['id' => $coursecontext->instanceid]) : '', 0, 30);
            $name = "block_exaaichat {$context->instanceid} (Course: $shortname)";
            $storeid = $client->vectorStores()->create(['name' => $name])->id;
            logger::debug_grouped($group, 'created vector store:', $storeid, $name);
        }

        // Upload local files not yet in the store.
        foreach ($local as $filename => $file) {
            if (isset($remote[$filename])) {
                continue;
            }

            logger::debug_grouped($group, 'uploading file:', $filename);

            // Copy to a request-scoped temp path under the real name so OpenAI shows a meaningful filename.
            $temppath = make_request_directory() . '/' . $filename;
            $file->copy_content_to($temppath);
            $stream = fopen($temppath, 'r');
            try {
                $fileid = $client->files()->upload(['purpose' => 'assistants', 'file' => $stream])->id;
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
            $client->vectorStores()->files()->create($storeid, [
                'file_id' => $fileid,
                'attributes' => ['filename' => $filename],
            ]);

            logger::debug_grouped($group, 'uploaded and attached file:', $filename, $fileid);
        }

        // Remove store files that are no longer present locally.
        foreach ($remote as $filename => $fileid) {
            if (isset($local[$filename])) {
                continue;
            }
            logger::debug_grouped($group, 'removing file:', $filename, $fileid);
            static::delete_remote_file($client, $storeid, $fileid);
        }

        return $storeid;
    }

    /**
     * Delete the managed vector store and all its uploaded files from OpenAI. Called when the block
     * is deleted, regardless of whether the documents feature is currently enabled, so we never
     * orphan (and keep paying for) a vector store in OpenAI.
     */
    public static function cleanup(object $config): void {
        $storeid = $config->managed_vector_store_id ?? '';
        if (!$storeid) {
            return;
        }

        $client = OpenAI::client(static::get_apikey($config));

        // Delete the underlying OpenAI files too; deleting the store alone leaves them in the account.
        try {
            foreach ($client->vectorStores()->files()->list($storeid, ['limit' => 100])->data as $vsfile) {
                try {
                    $client->files()->delete($vsfile->id);
                } catch (\Throwable $e) {
                    // Already gone.
                }
            }
        } catch (\Throwable $e) {
            // Store may already be gone; nothing to list.
        }

        try {
            $client->vectorStores()->delete($storeid);
        } catch (\Throwable $e) {
            // Store may already be gone.
        }
    }

    private static function get_apikey(object $config): string {
        $apikey = ($config->apikey ?? '') ?: get_config('block_exaaichat', 'apikey');
        if (!$apikey) {
            throw new \Exception('block_exaaichat: no OpenAI API key configured for the block or site');
        }
        return $apikey;
    }

    private static function delete_remote_file(Client $client, string $storeid, string $fileid): void {
        if (!$fileid) {
            return;
        }
        try {
            if ($storeid) {
                $client->vectorStores()->files()->delete($storeid, $fileid);
            }
        } catch (\Throwable $e) {
            // Already detached from the store.
        }
        try {
            $client->files()->delete($fileid);
        } catch (\Throwable $e) {
            // Already deleted.
        }
    }
}
