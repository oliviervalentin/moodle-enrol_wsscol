Block-wsscol
=======================

* Maintained by: Serge FELIX
* License: [GNU GPL v3 or later](http://www.gnu.org/copyleft/gpl.html)

WSSCOL for "WebServices de Scolarité" - Schooling Webservices

Description
===========
This plugin provides a new enrolment method for synchronise students from webservices.
This plugin does not provide the webservices to request

This plugin was initially created at University Lyon 2 and utilizes web services that access the educational applications Apogée and ADE.
It is still used to this day.
It is now also used in another school to access Aurion.


Installation
============

* Copy the module code directly to the enrol/wsscol directory.
* set tup the plugin

Capability
============
To add to your teacher role

* enrol/wsscol:config : Add or edit enrol-wsscol instance in course.

Tasks
============
For synchronise all wsscol enrolment method, you must activate this task : \enrol_wsscol\task\sync_enrolments to
synchronise them. This task utilyze adhoc tasks, so you need to set the cron.

Admin Settings
============
You can declare multiple schooling apps related to there webservices.
Each one can request 2 webservices :

* getstudent : (required) It manage the enrolments. It return the enrolled list of a schooling group (with the id of the
  group)
* searchgroups : (optionnal), If you want to bulk add enrolment methods. It return a schooling group list from a search
  string

For each webservices of app, you can specify the name of the attribute returned and if necessary, the mapping with
moodle field.

the request to webservices is construct like that :

* wshost/baseurl/getstudents_uri/codeofgroup
* wshost/baseurl/searchgroup_uri/searchstring

You can ommit baseurl. Do not add trailing slash or beginning slash.

