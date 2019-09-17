<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\AbstractExternalTask;
use GrumPHP\Task\Context\ContextInterface;
use Symfony\Component\Process\Process;

/**
 * Class AbstractProcessingTask.
 *
 * @package Wunderio\GrumPHP\Task
 */
abstract class AbstractProcessingTask extends AbstractExternalTask implements ConfigurableTaskInterface {

  use ConfigurableTaskTrait;

  /**
   * AbstractProcessingTask constructor.
   *
   * @param \GrumPHP\Configuration\GrumPHP $grum_php
   *   Grumphp.
   * @param \GrumPHP\Process\ProcessBuilder $process_builder
   *   ProcessBuilder.
   * @param \GrumPHP\Formatter\ProcessFormatterInterface $formatter
   *   Formatter.
   */
  public function __construct(GrumPHP $grum_php, ProcessBuilder $process_builder, ProcessFormatterInterface $formatter) {
    parent::__construct($grum_php, $process_builder, $formatter);
    $this->configure();
  }

  /**
   * Returns task result.
   *
   * @param \Symfony\Component\Process\Process $process
   *   Process.
   * @param \GrumPHP\Task\Context\ContextInterface $context
   *   Current context.
   *
   * @return \GrumPHP\Runner\TaskResult
   *   Result.
   */
  public function getTaskResult(Process $process, ContextInterface $context): TaskResult {
    if (!$process->isSuccessful()) {
      return TaskResult::createFailed($this, $context, $this->formatter->format($process));
    }

    return TaskResult::createPassed($this, $context);
  }

}
