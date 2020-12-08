<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task;

use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;

/**
 * Class AbstractMultiPathProcessingTask.
 *
 * Provides a base implementation for processing task with multiple paths.
 *
 * @package Wunderio\GrumPHP\Task
 */
abstract class AbstractMultiPathProcessingTask extends AbstractProcessingTask implements MultiPathArgumentsBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function run(ContextInterface $context): TaskResultInterface {
    $paths = $this->getPathsOrResult($context, $this->getConfig()->getOptions(), $this);
    if ($paths instanceof TaskResultInterface) {
      return $paths;
    }

    $process = $this->processBuilder->buildProcess($this->buildArguments($paths));
    $process->run();

    return $this->getTaskResult($process, $context);
  }

}
