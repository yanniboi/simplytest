<?php

namespace Drupal\simplytest_import;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface for defining Project entities.
 *
 * @ingroup simplytest_import
 */
interface ProjectInterface extends ContentEntityInterface {


  /**
   * Base url for drupal.org.
   */
  const SIMPLYTEST_DRUPAL_ORG = 'https://www.drupal.org/';

  /**
   * Drupal.org update projects url.
   */
  const SIMPLYTEST_DRUPAL_ORG_UPDATE = 'https://updates.drupal.org/release-history/project-list/all';

  /**
   * Project type identifier for core.
   */
  const SIMPLYTEST_PROJECTS_TYPE_CORE = 'core';

  /**
   * Project type identifier for a module.
   */
  const SIMPLYTEST_PROJECTS_TYPE_MODULE = 'module';

  /**
   * Project type identifier for a theme.
   */
  const SIMPLYTEST_PROJECTS_TYPE_THEME = 'theme';

  /**
   * Project type identifier for a distribution.
   */
  const SIMPLYTEST_PROJECTS_TYPE_DISTRO = 'distro';

  /**
   * Gets the Project name.
   *
   * @return string
   *   Name of the Project.
   */
  public function getName();

}
