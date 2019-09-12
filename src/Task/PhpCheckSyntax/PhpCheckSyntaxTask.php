<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task\PhpCheckSyntax;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Wunderio\GrumPHP\Task\ContextFileExternalTaskBase;

/**
 * Class PhpCheckSyntaxTask.
 *
 * @package Wunderio\GrumPHP\Task\PhpCheckSyntaxTask
 */
class PhpCheckSyntaxTask extends ContextFileExternalTaskBase {

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return 'php_check_syntax';
  }

  /**
   * {@inheritdoc}
   */
  public function run(ContextInterface $context): TaskResultInterface {
    $files = $this->getFiles($context, TRUE);
    if ($context instanceof GitPreCommitContext && (empty($files) || \count($files) === 0)) {
      return TaskResult::createSkipped($this, $context);
    }

    $output = '';
    foreach ($files as $file) {
      $arguments = $this->processBuilder->createArgumentsForCommand('php');
      $arguments->add('-l');
      $arguments->add($file);
      $process = $this->processBuilder->buildProcess($arguments);
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
