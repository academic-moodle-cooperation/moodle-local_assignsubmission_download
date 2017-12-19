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
 * Javascript functionality
 *
 * @package       local
 * @subpackage    assignsubmission_download
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Günther Bernsteiner
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.local_assignsubmission_download = M.local_assignsubmission_download || {};

M.local_assignsubmission_download = {

    init_tree: function(Y, expand_all, htmlid) {
        Y.use('yui2-treeview', function(Y) {
            var tree = new Y.YUI2.widget.TreeView(htmlid);

            tree.subscribe("clickEvent", function(node, event) {
                // We want normal clicking which redirects to url.
                return false;
            });

            if (expand_all) {
                tree.expandAll();
            }
            tree.render();
        });
    },

    /*
     * Used by assign
     */
    init_printpreview_settings: function(Y) {

        this.Y = Y;
        var filterelement = Y.one('#id_filter');
        var groupelement  = Y.one('#id_group');
        var exportformat  = Y.one('#id_exportformat');
        var selectall = Y.one('#selectall');
        var select = Y.all('td.cell input[type=checkbox]');

        var toggleprintsettings = function (exportformat) {
            if (exportformat == 0) {
                Y.one('#id_grpperpage_perpage').removeAttribute('disabled');
                Y.one('#id_grpperpage_optimum').removeAttribute('disabled');
                Y.one('#id_textsize').removeAttribute('disabled');
                Y.one('#id_pageorientation').removeAttribute('disabled');
                Y.one('#id_printheader').removeAttribute('disabled');
            } else {
                Y.one('#id_grpperpage_perpage').setAttribute('disabled', 'disabled');
                Y.one('#id_grpperpage_optimum').setAttribute('disabled', 'disabled');
                Y.one('#id_textsize').setAttribute('disabled', 'disabled');
                Y.one('#id_pageorientation').setAttribute('disabled', 'disabled');
                Y.one('#id_printheader').setAttribute('disabled', 'disabled');
            }
        }

        if (filterelement) {
            filterelement.on('change', function(e) {
                Y.one('form.mform').submit();
            });
        }

        if (groupelement) {
            groupelement.on('change', function(e) {
                Y.one('form.mform').submit();
            });
        }

        if (exportformat) {
            exportformat.on('change', function(e) {
                toggleprintsettings(this.get('value'));
            });
        }

        selectall.setAttribute('checked', 'checked');
        select.setAttribute('checked', 'checked');

        var combo = new Array();
        combo[0] = 'input[id=id_grpperpage_optimum]';
        combo[1] = 'input[id=id_grpperpage_perpage]';

        M.local_assignsubmission_download.stdperpage = Y.one(combo[1]).get('value');

        if(M.local_assignsubmission_download.Y.one(combo[0]).get('checked')) {
            M.local_assignsubmission_download.Y.one(combo[1]).setAttribute('disabled', 'disabled');
        } else {
            M.local_assignsubmission_download.Y.one(combo[1]).removeAttribute('disabled');
        }

        Y.one(combo[1]).on('change', M.local_assignsubmission_download.change_in_perpage, null, combo);
        Y.one(combo[0]).on('change', M.local_assignsubmission_download.change_in_optimum, null, combo);

        toggleprintsettings(exportformat.get('value'));
        return true;
    },

    change_in_perpage: function (e, combo) {
        // Stop the event's default behavior.
        e.preventDefault();
        // Stop the event from bubbling up the DOM tree.
        e.stopPropagation();

        if(M.local_assignsubmission_download.Y.one(combo[1]).get('value') == 0) {
            // Check checkbox and disable text field.
            M.local_assignsubmission_download.Y.one(combo[0]).set('checked', 'checked');
            M.local_assignsubmission_download.Y.one(combo[1]).setAttribute('disabled', 'disabled');
        } else {
            // Save last value, uncheck checkbox and enable textfield if checked/disabled.
            M.local_assignsubmission_download.stdperpage = M.local_assignsubmission_download.Y.one(combo[1]).get('value');
            M.local_assignsubmission_download.Y.one(combo[0]).set('checked', '');
            M.local_assignsubmission_download.Y.one(combo[1]).removeAttribute('disabled');
        }
    },

    change_in_optimum: function (e, combo) {
        // Stop the event's default behavior.
        e.preventDefault();
        // Stop the event from bubbling up the DOM tree.
        e.stopPropagation();

        if(M.local_assignsubmission_download.Y.one(combo[0]).get('checked')) {
            // Save last value, set to 0 and disable textfield.
            M.local_assignsubmission_download.stdperpage = M.local_assignsubmission_download.Y.one(combo[1]).get('value');
            M.local_assignsubmission_download.Y.one(combo[1]).set('value', 0);
            M.local_assignsubmission_download.Y.one(combo[1]).setAttribute('disabled', 'disabled');
        } else {
            // Restore last value and enable again.
            M.local_assignsubmission_download.Y.one(combo[1]).set('value', M.local_assignsubmission_download.stdperpage);
            M.local_assignsubmission_download.Y.one(combo[1]).removeAttribute('disabled');
        }
    },

    /*
     * Used by assign
     */
    init_filerenaming_settings: function(Y) {
        return true;
    }

}