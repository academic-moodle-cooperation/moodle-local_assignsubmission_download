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
 * JS handling group/grouping toggler for filerenaming
 *
 * @package   local_assignsubmission_download
 * @author    Philipp Hager
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module local_assignsubmission_download/filerenaming_grouptoggle
 */
define(['jquery', 'core/log'], function($, log) {
    /**
     * @contructor
     * @alias module:local_assignsubmission_download/filerenaming_grouptoggle
     */
    var Grouptoggle = function() {
    };

    var instance = new Grouptoggle();

    instance.initializer = function(groupings) {
        log.info('Initialise filerenaming grouping taggling ...', 'local_assignsubmission_download');

        var coursegroupsSelector = "#id_coursegroup";
        var coursegroups = $(coursegroupsSelector).first();

        var coursegroupingsSelector = "#id_coursegrouping";
        var coursegroupings = $(coursegroupingsSelector).first();
        if (coursegroupings) {
            coursegroupings.change(instance, function() {
                var success = $('.alert-success');
                if (success) {
                    success.remove();
                }

                var curgrouping = $(this).children("option:selected").val();
                if (coursegroups) {
                    $('#id_coursegroup').empty();
                    $.each(groupings, function (groupingid, groupingitem) {
                        if (groupingid == curgrouping) {
                            $.each(groupingitem, function (i, item) {
                                if (i == "groups") {
                                    $.each(item, function (unused, ginfo) {
                                        $('#id_coursegroup').append($('<option>', {value: ginfo.gid, text: ginfo.name}));
                                    });
                                }

                            });
                        }
                    });
                }
            });
        }
    };

    return instance;
});
