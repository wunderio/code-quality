<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task\PhpCheckSyntax;

use GrumPHP\Collection\ProcessArgumentsCollection;
use Wunderio\GrumPHP\Task\AbstractSinglePathProcessingTask;

/**
 * Class PhpCheckSyntaxTask.
 *
 * Builds php_check_syntax task.
 *
 * @package Wunderio\GrumPHP\Task
 */
class PhpCheckSyntaxTask extends AbstractSinglePathProcessingTask {

  /**
   * {@inheritdoc}
   */
  public function buildArgumentsFromPath(string $path): ProcessArgumentsCollection {
    $arguments = $this->processBuilder->createArgumentsForCommand('php');
    $arguments->add('-l');
    $arguments->add($path);

    return $arguments;
  }

}
