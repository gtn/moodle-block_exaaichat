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
 * @package    block_exaaichat
 * @copyright  2025 GTN Solutions https://gtn-solutions.com
 * @link       https://github.com/Limekiller/moodle-block_openai_chat Based on block openai_chat by Limekiller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_exaaichat;

defined('MOODLE_INTERNAL') || die;

require_once $CFG->libdir . '/gradelib.php';
require_once $CFG->dirroot . '/grade/lib.php';
require_once $CFG->dirroot . '/grade/report/user/lib.php';

class helper {
    /**
     * clean the text response from the assistant
     * @param string $text
     * @return string
     */
    public static function clean_text_response(string $text): string {
        // Prevent the assistant from printing references (citing sources)
        // https://community.openai.com/t/prevent-the-assistant-from-citing-sources/599121/8
        $text = preg_replace('/【\d+(:\d+)?+†.*】/U', '', $text);

        return $text;
    }

    /**
     * Get my grade information of the current course
     * Abruf der Noten und Bewertungen für den aktuellen Kurs
     */
    public static function get_student_grades_for_course_flattened(): mixed {
        global $COURSE, $USER;

        $courseid = 0;

        if (!$courseid) {
            $courseid = $COURSE->id;
        }

        $userid = $USER->id;
        $context = \context_course::instance($courseid);
        $gpr = new \grade_plugin_return(['type' => 'report', 'plugin' => 'user', 'courseid' => $courseid, 'userid' => $userid]);
        $report = new \gradereport_user\report\user($courseid, $gpr, $context, $userid);

        if (!$report->fill_table()) {
            return 'No grades available';
        }

        // OLD: Print the page
        // this doesn't work, because the output can be very long (100kb)
        // which is to much for chatGPT (30k limit)
        // return $report->print_table(true);

        // NEW: parse the table into objects
        // this is probably better, than to copy the whole logic of fill_table() and fill_table_recursive() over to here

        $data = [];

        // Set the table body data.
        foreach ($report->tabledata as $rowdata) {

            if ($rowdata['spacer'] ?? false) {
                // ignore spacer row
                continue;
            }

            $row = (object)[];
            // var_dump($rowdata);
            if (!preg_match('!\blevel([0-9]+)\b!', $rowdata['itemname']['class'], $matches)) {
                continue;
            }

            // Set the row cells.
            foreach ($report->tablecolumns as $tablecolumn) {
                $content = $rowdata[$tablecolumn]['content'] ?? null;

                if (!is_null($content)) {
                    $content_text = $content;
                    // Leerzeichen bei divs einfügen, damit die Texte nicht zusammenkleben
                    $content_text = str_replace('<div', ' <div', $content_text);
                    // Filter out the action menu items, which also filters out the whole action menu
                    $content_text = preg_replace('!(menu-action-text[^>]+)[^<]+!', '$1', $content_text);
                    // remove other info before the rowtitle
                    $content_text = preg_replace('!.*(<div class="rowtitle">)!', '$1', $content_text);

                    $content_text = strip_tags($content_text);
                    $content_text = html_entity_decode($content_text);
                    $content_text = trim($content_text);
                    // Filter out spaces
                    $content_text = preg_replace('![\s\r\n]+!', ' ', $content_text);

                    if ($tablecolumn == 'itemname') {
                        $tablecolumn = 'name';
                    }
                    $row->{$tablecolumn} = $content_text;
                }
            }
            $row->itemname = $rowdata['itemname']['content'] ?? null;

            $data[] = $row;
        }

        return $data;
    }

    public static function format_user_message(string $user_message): string {

        $gradedata = static::get_student_grades_for_course_flattened();

        return preg_replace_callback('!{(?<placeholder>[^}]+)}!', function($matches) use ($gradedata) {
            global $COURSE, $USER;

            $placeholder = $matches['placeholder'];

            if ($placeholder == 'user.fullname') {
                return fullname($USER);
            }
            if ($placeholder == 'userdate') {
                return userdate(time());
            }
            if (preg_match('!^user\.(.+)!', $placeholder, $matches)) {
                throw new \moodle_exception('todo #42422444');
            }
            if (preg_match('!^grade:(.+)!', $placeholder, $matches)) {
                $make_name_english = fn($value) => preg_replace('!( gesamt| ukupno)$!i', ' total', $value ?? '');
                foreach ($gradedata as $row) {
                    if ($make_name_english($row->name) == $make_name_english($matches[1])) {
                        return $row->grade;
                    }
                }

                // not found
                return 'not available';

                echo 'gradedata: ';
                var_dump($gradedata);

                die("grade item \"{$matches[1]}\" not found");
            }

            throw new \moodle_exception('Unknown placeholder ' . $placeholder);
        }, $user_message);
    }
}
