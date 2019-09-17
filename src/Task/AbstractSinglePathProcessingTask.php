<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;

/**
 * Class AbstractSinglePathProcessingTask.
 *
 * @package Wunderio\GrumPHP\Task
 */
abstract class AbstractSinglePathProcessingTask extends AbstractProcessingTask implements SinglePathArgumentsBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function run(ContextInterface $context): TaskResultInterface {
    $files = $result = $this->getFilesOrResult($context, $this->getConfiguration(), $this);
    if ($result instanceof TaskResultInterface) {
      unset($files);
      return $result;
    }

    $output = '';
    foreach ($files as $file) {
      $process = $this->processBuilder->buildProcess($this->buildArgumentsFromPath((string) $file));
      $process->run();

      if (!$process->isSuccessful()) {
        $output .= PHP_EOL . $this->formatter->format($process);
      }
    }

    if ($output !== '') {
      return TaskResult::createFailed($this, $context, $output);
    }

    return TaskResult::createPassed($this, $context);
  }

}
