<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;

/**
 * Interface SinglePathArgumentsBuilderInterface.
 *
 * Provides single-path arguments builder requirements.
 *
 * @package Wunderio\GrumPHP\Task
 */
interface SinglePathArgumentsBuilderInterface {

  /**
   * Builds Process arguments.
   *
   * @param string $path
   *   Files or directories.
   *
   * @return \GrumPHP\Collection\ProcessArgumentsCollection
   *   Process arguments.
   */
  public function buildArgumentsFromPath(string $path): ProcessArgumentsCollection;

}
