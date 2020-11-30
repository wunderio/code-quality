<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Linter\LinterInterface;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\AbstractLinterTask;
use GrumPHP\Task\Context\ContextInterface;

/**
 * Class AbstractLintTask.
 *
 * @package Wunderio\GrumPHP\Task
 */
abstract class AbstractLintTask extends AbstractLinterTask implements LintTaskInterface, ConfigurableTaskInterface {

  use ConfigurableTaskTrait;
  use ContextRunTrait;

  /**
   * AbstractLintTask constructor.
   *
   * @param \GrumPHP\Linter\LinterInterface $linter
   */
  public function __construct(LinterInterface $linter) {
    parent::__construct($linter);
    $this->configure();
  }

  /**
   * {@inheritdoc}
   */
  public function run(ContextInterface $context): TaskResultInterface {
    $paths = $this->getPathsOrResult($context, $this->getConfig()->getOptions(), $this);
    if ($paths instanceof TaskResultInterface) {
      return $paths;
    }

    $this->configureLint($this->linter);

    return $this->runLint($context, $paths);
  }

  /**
   * Run lint.
   *
   * @param \GrumPHP\Task\Context\ContextInterface $context
   *   Current context.
   * @param \GrumPHP\Collection\FilesCollection $files
   *   Files.
   *
   * @return \GrumPHP\Runner\TaskResultInterface
   *   Task result.
   */
  public function runLint(ContextInterface $context, FilesCollection $files): TaskResultInterface {
    try {
      $lintErrors = $this->lint($files);
    }
    catch (RuntimeException $e) {
      return TaskResult::createFailed($this, $context, $e->getMessage());
    }

    if ($lintErrors->count()) {
      return TaskResult::createFailed($this, $context, (string) $lintErrors);
    }

    return TaskResult::createPassed($this, $context);
  }

}
