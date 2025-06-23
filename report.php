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
 * Log report table
 *
 * @package    block_exaaichat
 * @copyright  2025 GTN Solutions https://gtn-solutions.com
 * @copyright  based on work by Limekiller https://github.com/Limekiller/moodle-block_openai_chat
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \block_exaaichat\report;

require_once('../../config.php');
require_once($CFG->libdir.'/tablelib.php');
global $DB;

$courseid = required_param('courseid', PARAM_INT);
$download = optional_param('download', '', PARAM_ALPHA);
$user = optional_param('user', '', PARAM_TEXT);
$starttime = optional_param('starttime', '', PARAM_TEXT);
$endtime = optional_param('endtime', '', PARAM_TEXT);
$tsort = optional_param('tsort', '', PARAM_TEXT);

$pageurl = $CFG->wwwroot . "/blocks/exaaichat/report.php?courseid=$courseid" .
    "&user=$user" .
    "&starttime=$starttime" .
    "&endtime=$endtime";
$starttime_ts = strtotime($starttime);
$endtime_ts = strtotime($endtime);
$course = $DB->get_record('course', ['id' => $courseid]);

$PAGE->set_url($pageurl);
require_login($course);
$context = context_course::instance($courseid);
require_capability('block/exaaichat:viewreport', $context);

$datetime = new DateTime();
$table = new \block_exaaichat\report(time());
$table->show_download_buttons_at(array(TABLE_P_BOTTOM));
$table->is_downloading(
    $download,
    get_string('downloadfilename', 'block_exaaichat')
        . '_'
        . $datetime->format(DateTime::ATOM)
);

if (!$table->is_downloading()) {
    $PAGE->set_pagelayout('report');
    $PAGE->set_title(get_string('exaaichat_logs', 'block_exaaichat'));
    $PAGE->set_heading(get_string('exaaichat_logs', 'block_exaaichat'));
    $PAGE->navbar->add($course->shortname, new moodle_url('/course/view.php', ['id' => $course->id]));
    $PAGE->navbar->add(get_string('exaaichat_logs', 'block_exaaichat'), new moodle_url($pageurl));

    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('block_exaaichat/report_page', [
        "courseid" => $courseid,
        "user" => $user,
        "starttime" => $starttime,
        "endtime" => $endtime,
        "link" => (new moodle_url("/blocks/exaaichat/report.php"))->out()
    ]);
}

$where = "1=1";
$out = 10;

// If courseid is 1, we're assuming this is an admin report wanting the entire log table
// otherwise, we'll limit it to responses in the course context for this course
if ($courseid !== 1) {
    $where = "c.contextlevel = 50 AND co.id = $courseid";
}

// filter by user, starttime, endtime

$params = [];

if ($user) {
    $userlike = $DB->sql_like($DB->sql_concat('u.firstname', "' '", 'u.lastname'), '?', false);
    $where .= " AND $userlike";
    $params[] = "%$user%";
}
if ($starttime_ts) {
    $where .= " AND ocl.timecreated > ?";
    $params[] = $starttime_ts;
}
if ($endtime_ts) {
    $where .= " AND ocl.timecreated < ?";
    $params[] = $endtime_ts;
}

$table->set_sql(
    "ocl.*, " . $DB->sql_concat('u.firstname', "' '", 'u.lastname') . " AS user_name",
    "{block_exaaichat_log} ocl
        JOIN {user} u ON u.id = ocl.userid
        JOIN {context} c ON c.id = ocl.contextid
        LEFT JOIN {course} co ON co.id = c.instanceid",
    $where,
    $params
);

// Let Moodle handle sorting
$table->sortable(true, 'timecreated');

$table->define_baseurl($pageurl);
$table->out($out, true);

if (!$table->is_downloading()) {
    echo $OUTPUT->footer();
}
