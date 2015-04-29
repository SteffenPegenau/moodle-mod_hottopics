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
 * Library of interface functions and constants for module hottopics
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the hottopics specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package   mod_hottopics
 * @copyright 2011 Sun Zhigang
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/** example constant */
//define('HOTQUESTION_ULTIMATE_ANSWER', 42);

/**
 * If you for some reason need to use global variables instead of constants, do not forget to make them
 * global as this file can be included inside a function scope. However, using the global variables
 * at the module level is not a recommended.
 */
//global $HOTQUESTION_GLOBAL_VARIABLE;
//$HOTQUESTION_QUESTION_OF = array('Life', 'Universe', 'Everything');

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $hottopics An object from the form in mod_form.php
 * @return int The id of the newly inserted hottopics record
 */
function hottopics_add_instance($hottopics) {
    global $DB;

    $hottopics->timecreated = time();

    # You may have to add extra stuff in here #

    $id = $DB->insert_record('hottopics', $hottopics);

    return $id;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $hottopics An object from the form in mod_form.php
 * @return boolean Success/Fail
 */
function hottopics_update_instance($hottopics) {
    global $DB;

    $hottopics->timemodified = time();
    $hottopics->id = $hottopics->instance;

    # You may have to add extra stuff in here #

    return $DB->update_record('hottopics', $hottopics);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function hottopics_delete_instance($id) {
    global $DB;

    if (! $hottopics = $DB->get_record('hottopics', array('id' => $id))) {
        return false;
    }

    if (! reset_instance($hottopics->id)) {
        return false;
    }

    if (! $DB->delete_records('hottopics', array('id' => $hottopics->id))) {
        return false;
    }

    return true;
}

/**
 * Clear all questions and votes
 * 
 * @param int $hottopicsid
 * @return boolean Success/Failure
 */
function reset_instance($hottopicsid) {
    global $DB;

    $questions = $DB->get_records('hottopics_questions', array('hottopics' => $hottopicsid));
    foreach ($questions as $question) {
        if (! $DB->delete_records('hottopics_votes', array('question' => $question->id))) {
            return false;
        }
    }

    if (! $DB->delete_records('hottopics_questions', array('hottopics' => $hottopicsid))) {
        return false;
    }

    if (! $DB->delete_records('hottopics_rounds', array('hottopics' => $hottopicsid))) {
        return false;
    }

    return true;
}

/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * @todo Finish documenting this function
 */
function hottopics_user_outline($course, $user, $mod, $hottopics) {
    $return = new stdClass;
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function hottopics_user_complete($course, $user, $mod, $hottopics) {
    return true;
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in hottopics activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function hottopics_print_recent_activity($course, $isteacher, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function hottopics_cron () {
    return true;
}

/**
 * Must return an array of users who are participants for a given instance
 * of hottopics. Must include every user involved in the instance,
 * independient of his role (student, teacher, admin...). The returned
 * objects must contain at least id property.
 * See other modules as example.
 *
 * @param int $hottopicsid ID of an instance of this module
 * @return boolean|array false if no participants, array of objects otherwise
 */
function hottopics_get_participants($hottopicsid) {
    return false;
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * This function will remove all posts from the specified forum
 * and clean up any related data.
 *
 * @global object
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function hottopics_reset_userdata($data) {
    global $DB;

    $status = array();
    if (!empty($data->reset_hottopics)) {
        $instances = $DB->get_records('hottopics', array('course' => $data->courseid));
        foreach ($instances as $instance) {
            if (reset_instance($instance->id)) {
                $status[] = array('component'=>get_string('modulenameplural', 'hottopics'), 'item'=>get_string('resethottopics','hottopics').': '.$instance->name, 'error'=>false);
            }
        }
    }

    return $status;
}

/**
 * Called by course/reset.php
 *
 * @param $mform form passed by reference
 */
function hottopics_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'hottopicsheader', get_string('modulenameplural', 'hottopics'));
    $mform->addElement('checkbox', 'reset_hottopics', get_string('resethottopics','hottopics'));
}

/**
 * Indicates API features that the hottopics supports.
 *
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_GROUPMEMBERSONLY
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_COMPLETION_HAS_RULES
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature
 * @return mixed True if yes (some features may use other values)
 */
function hottopics_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return false;
        case FEATURE_MOD_INTRO:               return true ;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
        case FEATURE_COMPLETION_HAS_RULES:    return false;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_RATE:                    return false;
        case FEATURE_BACKUP_MOODLE2:          return true;

        default: return null;
    }
}
