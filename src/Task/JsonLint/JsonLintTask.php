<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task\JsonLint;

use GrumPHP\Linter\LinterInterface;
use Wunderio\GrumPHP\Task\AbstractLintTask;

/**
 * Class JsonLintTask.
 *
 * Builds json_lint task.
 *
 * @package Wunderio\GrumPHP\Task
 */
class JsonLintTask extends AbstractLintTask {

  /**
   * {@inheritdoc}
   */
  public function configureLint(LinterInterface $linter): void {
    $config = $this->getConfiguration();
    $linter->setDetectKeyConflicts($config['detect_key_conflicts']);
  }

}
