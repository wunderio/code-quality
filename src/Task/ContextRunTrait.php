<?php

namespace Wunderio\GrumPHP\Task;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;

/**
 * Trait ContextRunTrait.
 *
 * Provides helper functions for run context.
 *
 * @package Wunderio\GrumPHP\Task
 */
trait ContextRunTrait {

  /**
   * {@inheritdoc}
   */
  public function canRunInContext(ContextInterface $context): bool {
    return $context instanceof GitPreCommitContext || $context instanceof RunContext;
  }

}
