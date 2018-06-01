Assign Submission Download
==========================

This file is part of the local_assignsubmission_download plugin for Moodle - <http://moodle.org/>

*Author:*    Andreas Krieger

*Copyright:* 2014 [Academic Moodle Cooperation](http://www.academic-moodle-cooperation.org)

*License:*   [GNU GPL v3 or later](http://www.gnu.org/copyleft/gpl.html)


Description
-----------

Basically, this project adds two assign extensions for download options.  
1. Download renamed submissions - you can download the submission files after they have been
 renamed according to the instance settings of the assignment.  
2. Export - download the table info for the assignment in different formats and settings  
Both menu items can be toggled show/hide in the admin settings of the plugin.


Example of usage
----------------

"Export"-Feature:

* In the "Export settings" choose your export format settings (pdf, xls, csv). 
* In the "Data settings" choose the filters.
* On the "Data preview" block filter for initials of (surname|first name).
* Press the "Download file" button to get the report.

"Download renamed submissions"-Feature:

* In the "Naming scheme" enter the desired (valid) pattern composed of the given tags and
 arbitrary characters.
* For easier usage, choose one of the tags by clicking on them - they will be inserted at the
 current position.
* In the "Clean filenames" checkbox, specify whether or not you want to clean up special
 characters in the file names.


Requirements
------------
The plugin/s were available including core patches for Moodle 2.5+.  
This core patch free version is for Moodle 3.5.


Installation
------------

* Copy the module code directly to the *moodleroot/local/assignsubmission_download* directory.

* Log into Moodle as administrator.

* Open the administration area (*http://your-moodle-site/admin*) to start the installation
  automatically.



Admin Settings
--------------
### Global Settings
* Default-Displayed Submissions  
    This sets the number of submissions which are displayed per page, when teachers are viewing
 assignment submissions.
    It is overwritten by the teacher's individual preferences. Input will be absolute-valued.

* Show 'Download renamed submissions'  
    Used to show or hide the 'Download renamed submissions' menu entry

* Show 'Export'  
    Used to show or hide the 'Export' menu entry


Documentation
-------------
See above.


Bug Reports / Support
---------------------
None.


License
-------

This plugin is free software: you can redistribute it and/or modify it under the terms of the GNU
General Public License as published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

The plugin is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
General Public License for more details.

You should have received a copy of the GNU General Public License with Moodle. If not, see
<http://www.gnu.org/licenses/>.


Good luck and have fun!
