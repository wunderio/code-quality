<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task;

use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;

/**
 * Class AbstractMultiPathProcessingTask.
 *
 * Abstract multi-path process task base.
 *
 * @package Wunderio\GrumPHP\Task
 */
abstract class AbstractMultiPathProcessingTask extends AbstractProcessingTask implements MultiPathArgumentsBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function run(ContextInterface $context): TaskResultInterface {
    $paths = $this->getPathsOrResult($context, $this->getConfiguration(), $this);
    if ($paths instanceof TaskResultInterface) {
      return $paths;
    }

    $process = $this->processBuilder->buildProcess($this->buildArguments($paths));
    $process->run();

    return $this->getTaskResult($process, $context);
  }

}
