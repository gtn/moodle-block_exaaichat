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
 * @copyright  based on work by Limekiller https://github.com/Limekiller/moodle-block_openai_chat
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_exaaichat;

defined('MOODLE_INTERNAL') || die;

require_once $CFG->dirroot . '/course/externallib.php';
require_once $CFG->dirroot . '/enrol/externallib.php';
require_once $CFG->libdir . '/gradelib.php';
require_once $CFG->dirroot . '/grade/lib.php';
require_once $CFG->dirroot . '/grade/report/user/lib.php';

/**
 * This class contains methods, which can be called by the AI assisstant
 */
class actions {
    /**
     * Get the firstname, lastname, email, etc. of the current user
     * @return object the current user's id, username, firstname, lastname and email
     */
    public static function get_current_user_info(): object {
        global $USER;

        // don't return the whole user object, because it also contains the password hash etc.
        return (object)[
            'id' => $USER->id,
            'username' => $USER->username,
            'firstname' => $USER->firstname,
            'lastname' => $USER->lastname,
            'email' => $USER->email,
        ];
    }

    /**
     * Get information about the course the user is currently in
     * @return object the current course record (id, fullname, shortname, summary, start/end dates, etc.)
     */
    public static function get_current_course(): object {
        global $COURSE;

        return $COURSE;
        // global $DB, $PAGE;
        //
        // $course_context = $PAGE->context->get_course_context(false);
        // if ($course_context) {
        //     return $DB->get_record('course', ['id' => $course_context->instanceid]);
        // } else {
        //     return null;
        // }
    }

    /**
     * Get a list of all courses of the current user
     * @return array the courses the current user is enrolled in
     */
    public static function get_current_users_courses(): array {
        global $USER;
        return \core_enrol_external::get_users_courses($USER->id);
    }

    /**
     * Get the list of users enrolled in a course (the participants), including their roles
     * @param int $courseid the course id, use 0 for the current course
     * @return array the enrolled users (participants) of the course, including their roles
     */
    public static function get_enrolled_users(int $courseid = 0): array {
        global $COURSE;

        if (!$courseid) {
            $courseid = $COURSE->id;
        }

        return \core_enrol_external::get_enrolled_users($courseid);
    }

    /**
     * Get the current user's own grades and assessments for the current course
     * @return array the user's grade items for the course; sub-items are nested under each parent's "subs"; empty if no grades are available
     */
    public static function get_student_grades_for_course(): array {
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
            return [];
            // return 'No grades available';
        }

        // OLD: Print the page
        // this doesn't work, because the output can be very long (100kb)
        // which is to much for chatGPT (30k limit)
        // return $report->print_table(true);

        // NEW: parse the table into objects
        // this is probably better, than to copy the whole logic of fill_table() and fill_table_recursive() over to here

        $data = [];
        $parents = [];

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

            $level = $matches[1];
            $parents[$level] = $row;

            // Set the row cells.
            foreach ($report->tablecolumns as $tablecolumn) {
                $content = $rowdata[$tablecolumn]['content'] ?? null;

                if (!is_null($content)) {
                    $content_text = $content;
                    // Leerzeichen bei divs einfügen, damit die Texte nicht zusammenkleben
                    $content_text = str_replace('<div', ' <div', $content_text);
                    // Filter out the action menu items, which also filters out the whole action menu
                    $content_text = preg_replace('!(menu-action-text[^>]+)[^<]+!', '$1', $content_text);
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

            if ($level == 1) {
                $data[] = $row;
            } else {
                // add all rows of level > 1 to it's parent
                if (!isset($parents[$level - 1]->subs)) {
                    $parents[$level - 1]->subs = [];
                }
                $parents[$level - 1]->subs[] = $row;
            }
        }

        return $data;
    }

    /**
     * Get the contents (sections and activities) of a course
     * @param int $courseid the course id, use 0 for the current course
     * @return array the course sections, each with its activities and resources
     */
    public static function get_course_contents(int $courseid = 0): array {
        global $COURSE;

        if (!$courseid) {
            $courseid = $COURSE->id;
        }

        return \core_course_external::get_course_contents($courseid);
    }

    /**
     * Get the most recently accessed courses of the current user
     * @return array the courses the current user accessed most recently
     */
    public static function get_recent_courses(): array {
        global $USER;
        return \core_course_external::get_recent_courses($USER->id);
    }

    /**
     * Get the list of all course categories
     * @return array all course categories
     */
    public static function get_categories(): array {
        return \core_course_external::get_categories();
    }
}
