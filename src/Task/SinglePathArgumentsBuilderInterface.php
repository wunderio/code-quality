<?php

declare(strict_types=1);

namespace Wunderio\GrumPHP\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;

/**
 * Interface SinglePathArgumentsBuilderInterface.
 *
 * Defines the interface for building Process arguments for single path.
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
