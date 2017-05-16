simplytest.me
=============

An online service that provides on-demand sandbox environments for
evaluating drupal.org projects like modules, themes and distributions.

Simple, fast and for free! [Visit our website](https://simplytest.me/)


Service architecture
--------------------

Simplytest.me will build a shell script for setting up Drupal and use
[spawn.sh](https://spawn.sh/) online service to create temporary containers.
(spawn.sh was specifically created as independend abstraction layer for
simplytest.me)

**Todo**
- Make this an actual installation profile
- Create a theme duh.
- See all the @todos in the modules

Setup
-----

At the moment only simplytest_submission module exists for spawning
submissions. After installing you'll need to set the spawn.sh access
token with

```
$ drush sset simplytest_submission.service_token "TOKEN"
```

Ask patrickd to get such a token if you want to contribute to the
project.
