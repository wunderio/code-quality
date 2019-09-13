<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;

/**
 * Interface ArgumentsBuilderInterface.
 *
 * @package Wunderio\GrumPHP\Task
 */
interface ArgumentsBuilderInterface {

  /**
   * Builds Process arguments.
   *
   * @param iterable $files
   *   Files or directories.
   *
   * @return \GrumPHP\Collection\ProcessArgumentsCollection
   *   Process arguments.
   */
  public function buildArguments(iterable $files): ProcessArgumentsCollection;

}
