/**
 * Handle submitting question and voting action of hottopics 
 * using Ajax of YUI
 *
 * @package   mod_hottopics
 * @copyright 2011 Sun Zhigang
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.mod_hottopics = {};

M.mod_hottopics.Y = {};

M.mod_hottopics.questionbox = {};
M.mod_hottopics.submitbutton = {};

M.mod_hottopics.init = function(Y) {
    M.mod_hottopics.Y = Y;

    // Init question box
    M.mod_hottopics.questionbox = Y.one('#id_question');
    M.mod_hottopics.questionbox.on('valueChange', M.mod_hottopics.questionchanged);

    // Init submit button
    M.mod_hottopics.submitbutton = Y.one('#id_submitbutton');
    if (M.mod_hottopics.getquestion() == '') {
        M.mod_hottopics.submitbutton.set('disabled', 'disabled');
    }
    Y.on("submit", M.mod_hottopics.submit, '#mform1');

    // bind toolbar buttons
    Y.on('click', M.mod_hottopics.refresh, '.hottopics_vote');
    Y.on('click', M.mod_hottopics.refresh, '.toolbutton');

    // bind io events
    Y.on('io:success', M.mod_hottopics.iocomplete);
    Y.on('io:failure', M.mod_hottopics.iofailure);
}

M.mod_hottopics.iocomplete = function(transactionid, response, arguments) {
    var Y = M.mod_hottopics.Y;

    // update questions
    var contentdiv = Y.one('#questions_list');
    contentdiv.set("innerHTML", response.responseText);

    // clean up form if this is a submit IO
    if (arguments.caller == 'submit') {
        M.mod_hottopics.questionbox.set('value', '');
        M.mod_hottopics.questionbox.removeAttribute('disabled');
        M.mod_hottopics.submitbutton.set('disabled', 'disabled');
    }

    // rebind buttons
    Y.on('click', M.mod_hottopics.refresh, '.hottopics_vote');
    Y.on('click', M.mod_hottopics.refresh, '.toolbutton');
}

M.mod_hottopics.iofailure = function(transactionid, response, arguments) {
    M.mod_hottopics.submitbutton.removeAttribute('disabled');
    M.mod_hottopics.questionbox.removeAttribute('disabled');
    alert(M.str.hottopics.connectionerror);
}

M.mod_hottopics.refresh = function(e) {
    e.preventDefault();

    var data = e.currentTarget.get('href').split('?',2)[1];
    data += '&ajax=1';
    var cfg = {
        method : "GET",
        data : data,
        arguments: {
            caller: 'refresh',
        }
    };

    var request = M.mod_hottopics.Y.io('view.php', cfg);
}

M.mod_hottopics.getquestion = function() {
    var question = M.mod_hottopics.questionbox.get('value');
    return YAHOO.lang.trim(question);
}

M.mod_hottopics.questionchanged = function(e) {
    var question = M.mod_hottopics.getquestion();
    var submitbutton = M.mod_hottopics.submitbutton;
    if (question == '') {
        submitbutton.set('disabled', 'disabled');
    } else {
        submitbutton.removeAttribute('disabled');
    }
}

M.mod_hottopics.submit = function(e) {
    e.preventDefault();

    var question = M.mod_hottopics.getquestion();
    if (question == '') {
        return; // ignore empty question
    }

    // To avoid multiple clicks and editing
    M.mod_hottopics.submitbutton.set('disabled', 'disabled');
    M.mod_hottopics.questionbox.set('disabled', 'disabled');

    // Get all input components
    var inputs = M.mod_hottopics.Y.all('#mform1 input');

    // construct post data
    var data = '';
    inputs.each(function(node, index, nodelist) {
        if (node.get('type') != 'checkbox') {
            data += node.get('name')+'='+node.get('value')+'&';
        } else {
            data += node.get('name')+'='+node.get('checked')+'&';
        }
    });
    data += 'question='+question+'&';
    data += 'ajax=1';

    var cfg = {
        method : "POST",
        data : data,
        arguments: {
            caller: 'submit',
        }
    };
    var request = M.mod_hottopics.Y.io('view.php', cfg);
}

