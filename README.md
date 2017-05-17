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


Contribute
----------

1. **Ask Patrick nicely for a token (@patrickd_de on twitter).**
    Spawn.sh is (currently) a closed service. You need an API token so that
    when you make requests, it allows your requests to pass. Patrick is in
    charge of spawn.sh and will manage who has tokens.
2. **Look for an issue.**
    We are currently tracking all issues for the D8 development phase on
    [github](https://github.com/yanniboi/simplytest/issues).
    Find an issue you are interested in and start working if it is not yet
    assigned to anyone.
    Feel free to ask questions!
3. **Boom! Start contributing...**


Setup
-----

1. **Install simplytest install profile.**
    Simplytest for Drupal 8 is an install profile. Which means that when you
    install it, it will set up a log of configuration for you so that you don't
    have to. Simply download it from drupal.org and place the profile directory
    in [drupal_root]/profiles. The when installing (either via drush or through
    the web ui) choose simplytest as your installation profile.
2. **Set API token.**
    The best way to do this is using Drush:

    `drush sset simplytest_submission.service_token "TOKEN"`

    If you haven't got drush, you can also place the token in your settings.php
    file:

```
$settings['simplytest_submission'] = [
  'service_token' => 'TOKEN',
];
```
