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
 * JS handling of the printpreview form
 *
 * @package   local_assignsubmission_download
 * @author    Andreas Krieger
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module local_assignsubmission_download/printpreviewer
 */
define(['jquery', 'core/log'], function($, log) {
    /**
     * @contructor
     * @alias module:local_assignsubmission_download/printpreviewer
     */
    var Printpreviewer = function() {
    };

    Printpreviewer.prototype.change_in_perpage = function(e) {
        // Stop the event's default behavior.
        e.preventDefault();
        // Stop the event from bubbling up the DOM tree.
        e.stopPropagation();

        if($(window.combo[1]).val() == 0) {
            // Check checkbox and disable text field.
            $(window.combo[0]).prop('checked', true);
            $(window.combo[1]).prop('disabled', true);
        } else {
            // Save last value, uncheck checkbox and enable textfield if checked/disabled.
            window.stdperpage = $(window.combo[1]).val();
            $(window.combo[0]).prop('checked', false);
            $(window.combo[1]).prop('disabled', false);
        }
    };

    Printpreviewer.prototype.change_in_optimum = function(e) {
        // Stop the event's default behavior.
        e.preventDefault();
        // Stop the event from bubbling up the DOM tree.
        e.stopPropagation();

        if($(window.combo[0]).prop('checked')) {
            // Save last value, set to 0 and disable textfield.
            window.stdperpage = $(window.combo[1]).val();
            $(window.combo[1]).val(0);
            $(window.combo[1]).prop('disabled', true);
        } else {
            // Restore last value and enable again.
            $(window.combo[1]).val(window.stdperpage);
            $(window.combo[1]).prop('disabled', false);
        }
    };

    var instance = new Printpreviewer();

    instance.initializer = function() {
        // Woraround: prevent second loading of javascript code, which happens don't know why (AK).
        if (window.washere) {return true;}
        window.washere = 1;
        log.info('Initialise printpreview handling js...', 'local_assignsubmission_download');

        var filterelement = $('#id_filter');
        var groupelement  = $('#id_group');
        var exportformat  = $('#id_exportformat');
        var selectall = $('#selectall');
        var select = $('td.cell input[type=checkbox]');

        var toggleprintsettings = function (exportformat) {
            if (exportformat == 0) {
                $('#id_grpperpage_perpage').prop('disabled', false);
                $('#id_grpperpage_optimum').prop('disabled', false);
                $('#id_textsize').prop('disabled', false);
                $('#id_pageorientation').prop('disabled', false);
                $('#id_printheader').prop('disabled', false);
            } else {
                $('#id_grpperpage_perpage').prop('disabled', true);
                $('#id_grpperpage_optimum').prop('disabled', true);
                $('#id_textsize').prop('disabled', true);
                $('#id_pageorientation').prop('disabled', true);
                $('#id_printheader').prop('disabled', true);
            }
        };

        if (filterelement) {
            filterelement.on('change', function() {
                $('form.mform').submit();
            });
        }

        if (groupelement) {
            groupelement.on('change', function() {
                $('form.mform').submit();
            });
        }

        if (exportformat) {
            exportformat.on('change', function() {
                toggleprintsettings(exportformat.val());
            });
        }

        selectall.prop('checked', 'checked');
        select.prop('checked', 'checked');

        window.combo = new Array();
        window.combo[0] = 'input[id=id_grpperpage_optimum]';
        window.combo[1] = 'input[id=id_grpperpage_perpage]';

        window.stdperpage = $(window.combo[1]).val();

        if($(window.combo[0]).checked) {
            $(window.combo[1]).prop('disabled', true);
        } else {
            $(window.combo[1]).prop('disabled', false);
        }

        $(window.combo[1]).change(this.change_in_perpage);
        $(window.combo[0]).change(this.change_in_optimum);

        toggleprintsettings(exportformat.val());
        return true;
    };

    return instance;
});
