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

Use WSScol with PEGASE
============
Somes changes have been brought to fit to use with PEGASE new scol app.
In order to set a webservice with PEGASE, please proceed like this :
* chosse PEGASE in school application type
* in host, retrieve URL for CHC API.
* set baseurl as api/ext/chc/v1
* in getstudent_id_ws, add "codeApprenant"
* for auth URL, set you CAS app delivering token
* choose CAS method if it fits
* "Période" must be set as needed to call CHC API. It shoulf look like "PERIODE-XX-YY" for example.
* COde structure represents "code etablissement" needed to use PEGASE Swagger.

Those changes have been made in ordre to bring necessary auth for PEGASE API, but without changing whole  WSScol plugin code
and transform it in a specific plugin for PEGASE. WSScol basis can be applied to any enrolment webservice, such as Apogee, Aurion aso.
