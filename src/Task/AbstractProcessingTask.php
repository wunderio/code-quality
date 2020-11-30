<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task;

use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\AbstractExternalTask;
use GrumPHP\Task\Config\TaskConfigInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use Symfony\Component\Process\Process;

/**
 * Class AbstractProcessingTask.
 *
 * @package Wunderio\GrumPHP\Task
 */
abstract class AbstractProcessingTask extends AbstractExternalTask implements ConfigurableTaskInterface {

  use ConfigurableTaskTrait;
  use ContextRunTrait;

  /**
   * AbstractProcessingTask constructor.
   *
   * @param \GrumPHP\Process\ProcessBuilder $processBuilder
   * @param \GrumPHP\Formatter\ProcessFormatterInterface $formatter
   */
  public function __construct(ProcessBuilder $processBuilder, ProcessFormatterInterface $formatter) {
    parent::__construct($processBuilder, $formatter);
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
    $this->configure();

    if (!$process->isSuccessful()) {
      return TaskResult::createFailed($this, $context, $this->formatter->format($process));
    }

    return TaskResult::createPassed($this, $context);
  }

  public function getConfig(): TaskConfigInterface
  {
    return $this->config;
  }

  public function withConfig(TaskConfigInterface $config): TaskInterface
  {
    $new = clone $this;
    $new->config = $config;

    return $new;
  }
}
