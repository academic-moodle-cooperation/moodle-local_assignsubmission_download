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
 * @module    local_assignsubmission_download/printpreviewer
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
        // Initialize empty constructor
    };

    var instance = new Printpreviewer();

    instance.initializer = function() {
        // Woraround: prevent second loading of javascript code, which happens don't know why (AK).
        if (window.washere) {
            return true;
        }
        window.washere = 1;
        log.info('Initialise printpreview handling js...', 'local_assignsubmission_download');

        var filterelement = $('#id_filter');
        var groupelement = $('#id_group');
        var selectall = $('#selectall');
        var select = $('td.cell input[type=checkbox]');

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

        selectall.prop('checked', 'checked');
        select.prop('checked', 'checked');

        return true;
    };

    return instance;
});
