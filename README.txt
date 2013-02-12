simplytest.me
=============

An online service that provides on-demand sandbox environments for
evaluating drupal.org project like modules, themes and distributions.

Simple, fast and for free!


Service architecture
====================

The service basically has a main web-server, which in the official case
is simplytest.me and several worker servers for the temporary sandbox sites.
The list of existing projects is imported data from the update API at
updates.drupal.org and the current list of Tags and Branches is extracted
from drupalcode.org.

If a submission was received, the worker server with the least active
submissions will be selected and a configuration file with detailed
submission-information will be transferred by SSH. Then a build-ShellScript
is triggered which will clone the selected project, checkout the specified
version and build a compatible Drupal site.


Modules
=======

## simplytest_import
  Config: /admin/simplytest/import
  Import project data from XML; Possible methods:
  - Path to file: Imports the file from a local directory.
  - Upload: Imports the file after uploading it.
  - Automatic download: Downloads and imports the current project list.

## simplytest_projects
  Provides an API to import and fetch project data from drupal.org as well
  as available versions (branches and tags).
  Drupal.org does currently not offer an official API to access such data,
  therefore the project pages are parsed for getting data of unknown projects
  (projects that were not imported initially by XML). Also the list of current
  heads and tags is parsed from the drupal.org repository viewer.
  Config: /admin/simplytest/project
  List: /admin/simplytest/projects

## simplytest_launch
  Provides the basic submission form.
  Contains the probably most important form with an autocomplete textfield and
  a project version selection.
  Also handles flood protection, configure: /admin/simplytest/flood

## simplytest_advanced
  Extends the basic submission form with the possibility to add additional
  modules/themes and apply patches on the selected main project.
  Config: /admin/simplytest/advanced

## simplytest_submissions
  Manages the submissions made through the launcher.
  Config: /admin/simplytest/submission
  Submission monitor: /admin/simplytest/submissions/monitor

## simplytest_progress
  Provides a progress bar, showing the current state of a submission.
  (Based on Batch API, unfortunately it was not good for observing states).

## simplytest_servers
  Manages the available servers, their selections and the execution of commands
  It basically generates a submission configuration file and transfers it to
  the selected sandbox server. The build shellscript will be executed which
  builds the sandbox environment for the project specified in the submission
  configuration file (more information about the worker server site and scripts
  can be found at ./scripts/README.txt).
  Config: /admin/simplytest/servers

## simplytest_sponsors
  Provides two "sponsor" blocks.
  - Block "simplytest sponsors - sponsor list":
    Shows a list of all sponsors by small logos below the submission block.
  - Block "simplytest sponsors - advertisement":
    Shows a slideshow of sponsor advertisement in random order.
  The list of sponsors, their order, logo and advertisement is configurable at:
    /admin/simplytest/sponsors

## simplytest_issues
  Provides the "simplytest issues" block that fetches the current state of
  the issue queue from drupal.org/project/simplytest and caches it.


Setup
=====

  1. Importing initial project data.
      The submission autocomplete textfield for choosing a project should
      have some initial data to work with.
     Method A: Import during installation.
      If not done yet, download the project list XML automatically by executing
      the .make script with drush. The file must be in the projects root and
      called projects.xml. It will automatically be imported during the
      installation of the profile.
     Method B: Manual download and import after installation.
      After the usual drupal installation you download the current project list
      $ wget http://updates.drupal.org/release-history/project-list/all
      Go to /admin/simplytest/import, enter the path to the downloaded XML and
      hit 'Start'. Importing the initial project data will take several minutes
      NOTE: It's faster to import the list from an existing database dump.


  2. Setup the/a worker server.

  To actually provide any functionality, submissions must be executed on a
  external worker server with the build scrips set up and executable.
  Follow the documentation in ./scripts/README.txt for further information.


  3. Referencing the servers on the site.

  Configure the simplytest.me site to make use of the server, by adding a new
  server on /admin/simplytest/servers.
  Most fields should be self explainable:
  Id:               Short identification string.
  Name:             Only for referencing a human readable name on the
                    simplytest.me site.
  Server Hostname:  The hostname of the server to connect to by ssh2, also used
                    as main hostname for sandbox sites.
                    Eg.: "s1.simplytest.me" -> "[id].s1.simplytest.me"
  Slots:            The current count of slots available on the server.
                    One slot stands for one sandbox environment.
  Spawn script:     The absolute path to the script for building the site.
                    Eg.: /home/spawner/spawn.sh

This is the basic configuration to make the service itself work.
You should now be able to submit a project and launch a sandbox environment.