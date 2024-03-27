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
 * JavaScript handling insertion of tags for ZIP archive renaming
 *
 * @module     local_assignsubmission_download/ziprenaming_tagsupport
 * @author     Clemens Marx
 * @copyright  2024 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This function adds an event listener to each tag element.
 * By that the tags become clickable and will be inserted at the current caret position.
 */
export const init = () => {
    window.console.log('Initializing ZIP renaming tag support.');
    // Add JS-Eventhandler for each tag.
    document.querySelectorAll('[data-zip-nametag]').forEach(tag => {
        tag.removeEventListener('click', addTag);
        tag.addEventListener('click', addTag);
        tag.style.cursor = 'pointer';
    });
};

const addTag = (e) => {
    e.stopPropagation();
    e.preventDefault();

    window.console.log('Add tag...');

    const targetfield = document.querySelector('input[name=nameofziparchive]');
    const node = e.target;

    let tag = '';
    tag = node.getAttribute('data-zip-nametag');

    const content = targetfield.value;
    const caretPos = targetfield.selectionStart;

    // Insert the tag at the current caret position.
    targetfield.value = content.substring(0, caretPos) + tag + content.substring(caretPos);

    // And now restore focus and caret position!
    targetfield.focus();
    const postpos = caretPos + tag.length;
    targetfield.setSelectionRange(postpos, postpos);
};
