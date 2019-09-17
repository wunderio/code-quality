<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Configuration\GrumPHP;
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

  /**
   * AbstractLintTask constructor.
   *
   * @param \GrumPHP\Configuration\GrumPHP $grum_php
   *   GrumpPHP.
   * @param \GrumPHP\Linter\LinterInterface $linter
   *   Linter.
   */
  public function __construct(GrumPHP $grum_php, LinterInterface $linter) {
    parent::__construct($grum_php, $linter);
    $this->configure();
  }

  /**
   * {@inheritdoc}
   */
  public function run(ContextInterface $context): TaskResultInterface {
    $files = $result = $this->getFilesOrResult($context, $this->getConfiguration(), $this);
    if ($result instanceof TaskResultInterface) {
      unset($files);
      return $result;
    }

    $this->configureLint($this->linter);

    return $this->runLint($context, $files);
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
