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
 * @package moodlecore
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restore steps that will be used by the restore_hottopics_activity_task
 */

/**
 * Structure step to restore one hottopics activity
 */
class restore_hottopics_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('hottopics', '/activity/hottopics');
        if ($userinfo) {
            $paths[] = new restore_path_element('hottopics_question', '/activity/hottopics/questions/question');
            $paths[] = new restore_path_element('hottopics_round', '/activity/hottopics/rounds/round');
            $paths[] = new restore_path_element('hottopics_vote', '/activity/hottopics/questions/question/votes/vote');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_hottopics($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // insert the hottopics record
        $newitemid = $DB->insert_record('hottopics', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function process_hottopics_question($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->hottopics = $this->get_new_parentid('hottopics');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->time = $this->apply_date_offset($data->time);

        $newitemid = $DB->insert_record('hottopics_questions', $data);
        $this->set_mapping('hottopics_question', $oldid, $newitemid);
    }

    protected function process_hottopics_round($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->hottopics = $this->get_new_parentid('hottopics');
        $data->starttime = $this->apply_date_offset($data->starttime);
        $data->endtime = $this->apply_date_offset($data->endtime);

        $newitemid = $DB->insert_record('hottopics_rounds', $data);
        // No need to save this mapping as far as nothing depend on it
        // (child paths, file areas nor links decoder)
    }

    protected function process_hottopics_vote($data) {
        global $DB;

        $data = (object)$data;

        $data->question = $this->get_new_parentid('hottopics_question');
        $data->voter = $this->get_mappingid('user', $data->voter);

        $newitemid = $DB->insert_record('hottopics_votes', $data);
        // No need to save this mapping as far as nothing depend on it
        // (child paths, file areas nor links decoder)
    }

    protected function after_execute() {
        // Add hottopics related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_hottopics', 'intro', null);
    }
}
