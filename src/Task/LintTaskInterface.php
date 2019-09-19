<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task;

use GrumPHP\Linter\LinterInterface;

/**
 * Interface LintTaskInterface.
 *
 * @package Wunderio\GrumPHP\Task
 */
interface LintTaskInterface {

  /**
   * Configures linter.
   *
   * @param \GrumPHP\Linter\LinterInterface $linter
   *   Linter.
   */
  public function configureLint(LinterInterface $linter): void;

}
