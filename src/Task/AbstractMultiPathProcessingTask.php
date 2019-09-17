<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task;

use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;

/**
 * Class AbstractMultiPathProcessingTask.
 *
 * @package Wunderio\GrumPHP\Task
 */
abstract class AbstractMultiPathProcessingTask extends AbstractProcessingTask implements MultiPathArgumentsBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function run(ContextInterface $context): TaskResultInterface {
    $files = $result = $this->getFilesOrResult($context, $this->getConfiguration(), $this);
    if ($result instanceof TaskResultInterface) {
      unset($files);
      return $result;
    }

    $process = $this->processBuilder->buildProcess($this->buildArguments($files));
    $process->run();

    return $this->getTaskResult($process, $context);
  }

}
