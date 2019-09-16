<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task\PhpCheckSyntax;

use GrumPHP\Collection\ProcessArgumentsCollection;
use Wunderio\GrumPHP\Task\AbstractSinglePathProcessingTask;

/**
 * Class PhpCheckSyntaxTask.
 *
 * @package Wunderio\GrumPHP\Task\PhpCheckSyntaxTask
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
