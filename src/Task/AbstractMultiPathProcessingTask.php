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
    $files = $result = $this->getFilesOrResult($context);
    if ($result  instanceof TaskResultInterface) {
      return $result;
    }

    $process = $this->processBuilder->buildProcess($this->buildArguments($files));
    $process->run();

    return $this->getTaskResult($context, $process);
  }

}
