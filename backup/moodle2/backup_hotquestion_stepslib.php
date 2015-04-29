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
 * Define all the backup steps that will be used by the backup_hottopics_activity_task
 */

/**
 * Define the complete hottopics structure for backup, with file and id annotations
 */
class backup_hottopics_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $hottopics = new backup_nested_element('hottopics', array('id'), array(
            'name', 'intro', 'introformat', 'timecreated',
            'timemodified', 'anonymouspost'));

        $questions = new backup_nested_element('questions');

        $question = new backup_nested_element('question', array('id'), array(
            'content', 'userid', 'time', 'anonymous'));

        $rounds = new backup_nested_element('rounds');

        $round = new backup_nested_element('round', array('id'), array(
            'starttime', 'endtime'));

        $votes = new backup_nested_element('votes');

        $vote = new backup_nested_element('vote', array('id'), array('voter'));

        // Build the tree
        $hottopics->add_child($questions);
        $questions->add_child($question);

        $hottopics->add_child($rounds);
        $rounds->add_child($round);

        $question->add_child($votes);
        $votes->add_child($vote);

        // Define sources
        $hottopics->set_source_table('hottopics', array('id' => backup::VAR_ACTIVITYID));

        // All the rest of elements only happen if we are including user info
        if ($userinfo) {
            $question->set_source_table('hottopics_questions', array('hottopics' => backup::VAR_PARENTID));
            $round->set_source_table('hottopics_rounds', array('hottopics' => backup::VAR_PARENTID));
            $vote->set_source_table('hottopics_votes', array('question' => backup::VAR_PARENTID));
        }

        // Define id annotations
        $question->annotate_ids('user', 'userid');
        $vote->annotate_ids('user', 'voter');

        // Define file annotations
        $hottopics->annotate_files('mod_hottopics', 'intro', null); // This file area hasn't itemid

        // Return the root element (hottopics), wrapped into standard activity structure
        return $this->prepare_activity_structure($hottopics);
    }
}
