<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;

/**
 * Class AbstractSinglePathProcessingTask.
 *
 * Provides a base implementation for processing task with single path.
 *
 * @package Wunderio\GrumPHP\Task
 */
abstract class AbstractSinglePathProcessingTask extends AbstractProcessingTask implements SinglePathArgumentsBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function run(ContextInterface $context): TaskResultInterface {
    $paths = $this->getPathsOrResult($context, $this->getConfig()->getOptions(), $this);
    if ($paths instanceof TaskResultInterface) {
      return $paths;
    }
    // Convert files collection to array.
    if ($paths instanceof FilesCollection) {
      $paths = iterator_to_array($paths->getIterator());
    }
    $output = '';
    // Split files in to executable chunks.
    $path_chunks = array_chunk($paths, $this->getConfig()->getOptions()['parallelism'] ?? 100);
    foreach ($path_chunks as $path_list) {
      $output .= $this->runInParallel($path_list);
    }
    // Fail task if there is output.
    if ($output !== '') {
      return TaskResult::createFailed($this, $context, $output);
    }

    return TaskResult::createPassed($this, $context);
  }

  /**
   * Runs multiple tasks in parallel.
   *
   * @param array $path_list
   *   List of paths.
   *
   * @return string
   *   Process output.
   */
  public function runInParallel(array $path_list): string {
    $output = '';
    // Start parallel execution.
    $runningProcesses = [];
    foreach ($path_list as $path) {
      // Create arguments list from paths and build process.
      $process = $this->processBuilder->buildProcess($this->buildArgumentsFromPath((string) $path));
      $process->start();
      $runningProcesses[] = $process;
    }
    // Handle each process from chunk that has been finalized.
    while (count($runningProcesses)) {
      $this->handleProcesses($runningProcesses, $output);
    }
    return $output;
  }

  /**
   * Handles running processes.
   *
   * @param \Symfony\Component\Process\Process[] $processes
   *   List of processes.
   * @param string $output
   *   Process output.
   */
  public function handleProcesses(array &$processes, string &$output): void {
    $output = '';
    foreach ($processes as $index => $process) {
      // Skip processes that are still running.
      if ($process->isRunning()) {
        continue;
      }
      // If process failed - report it.
      if (!$process->isSuccessful()) {
        $output .= $this->formatter->format($process) . PHP_EOL;
      }
      // Remove processes that are finished.
      unset($processes[$index]);
    }
  }

}
