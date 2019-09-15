<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;

/**
 * Class IndividualAbstractConfigurableContextFileExternalTask.
 *
 * @package Wunderio\GrumPHP\Task
 */
abstract class AbstractPerPathExternalTask extends AbstractConfigurableContextFileExternalTask {

  /**
   * {@inheritdoc}
   */
  public function run(ContextInterface $context): TaskResultInterface {
    $files = $result = $this->getFilesOrResult($context);
    if ($result  instanceof TaskResultInterface) {
      return $result;
    }

    $output = '';
    foreach ($files as $file) {
      $process = $this->processBuilder->buildProcess($this->buildArguments([$file]));
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
