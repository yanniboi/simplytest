simplytest.me
=============

An online service that provides on-demand sandbox environments for
evaluating drupal.org project like modules, themes and distributions.

Simple, fast and for free!


Service architecture
====================

Simplytest.me will build a shell script for setting up Drupal and use
spawn.sh online service to create temporary containers.
(spawn.sh was specifically created as independend abstraction layer for
simplytest.me)

TODO
- make this an actual installation profile
- create a theme duh.
- see all the @todos in the modules

Setup
=====

At the moment only simplytest_submission module exists for spawning
submissions. After installing you'll need to set the spawn.sh access
token with

$ drush sset simplytest_submission.service_token "TOKEN"

Ask patrickd to get such a token if you want to contribute to the
project.
