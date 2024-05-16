<?php

declare(strict_types=1);

namespace Wunderio\GrumPHP\Task;

use GrumPHP\Task\TaskInterface;

/**
 * Interface ConfigurableTaskInterface.
 *
 * Defines the interface for class that extends \GrumPHP\Task\TaskInterface.
 *
 * @package Wunderio\GrumPHP\Task
 */
interface ConfigurableTaskInterface extends TaskInterface {

  /**
   * Option Extensions.
   *
   * @var string
   */
  public const D_EXT = 'extensions';

  /**
   * Option Ignore patterns.
   *
   * @var string
   */
  public const D_IGN = 'ignore_patterns';

  /**
   * Option Run on.
   *
   * @var string
   */
  public const D_RUN = 'run_on';

  /**
   * Flag indicating that paths should be retrieved as files or directories.
   *
   * @return bool
   *   True if only file paths can be used, False if directories.
   */
  public function isFileSpecific(): bool;

}
