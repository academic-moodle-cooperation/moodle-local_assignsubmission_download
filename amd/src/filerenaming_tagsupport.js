// This file is part of mod_grouptool for Moodle - http://moodle.org/
//
// It is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// It is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * JS handling insertion of tags for filerenaming
 *
 * @package   local_assignsubmission_download
 * @author    Philipp Hager
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module local_assignsubmission_download/filerenaming_tagsupport
 */
define(['jquery', 'core/log'], function($, log) {
    /**
     * @contructor
     * @alias module:local_assignsubmission_download/filerenaming_tagsupport
     */
    var Tagsupport = function() {
    };

    Tagsupport.prototype.add_tag = function(e) {
        e.stopPropagation();
        e.preventDefault();

        log.info('Add tag...', 'local_assignsubmission_download');

        var targetfield = $('input[name=filerenamingpattern]');

        var node = $(e.target);

        var tag = '';

        tag = node.data('nametag');

        var content = targetfield.val();
        var caretPos = targetfield[0].selectionStart;
        targetfield.val(content.substring(0, caretPos) + tag + content.substring(caretPos));

        // And now restore focus and caret position!
        targetfield.focus();
        var postpos = caretPos + tag.length;
        targetfield[0].selectionStart = postpos;
        targetfield[0].selectionEnd = postpos;
    };

    var instance = new Tagsupport();

    instance.initializer = function() {
        log.info('Initialise filerenaming tag handling js...', 'local_assignsubmission_download');
        // Add JS-Eventhandler for each tag!
        $('[data-nametag]').unbind('click');
        $('[data-nametag]').on('click', null, this, this.add_tag);
        $('[data-nametag]').css('cursor', 'pointer');
    };

    return instance;
});
