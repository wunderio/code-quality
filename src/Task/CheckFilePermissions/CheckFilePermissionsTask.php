<?php

declare(strict_types=1);

namespace Wunderio\GrumPHP\Task\CheckFilePermissions;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;
use GrumPHP\Task\AbstractExternalTask;

/**
 * Class CheckFilePermissionsTask.
 *
 * @package Wunderio\GrumPHP\CheckFilePermissions
 */
class CheckFilePermissionsTask extends AbstractExternalTask
{
  /**
   * {@inheritdoc}
   */
  public function getName(): string
  {
    return 'check_file_permissions';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurableOptions(): OptionsResolver
  {
    return new OptionsResolver();
  }

  /**
   * {@inheritdoc}
   */
  public function canRunInContext(ContextInterface $context): bool
  {
    return $context instanceof GitPreCommitContext || $context instanceof RunContext;
  }

  /**
   * {@inheritdoc}
   */
  public function run(ContextInterface $context): TaskResultInterface
  {
    $files = $context->getFiles();
    if ($files->isEmpty()) {
      return TaskResult::createSkipped($this, $context);
    }

    $arguments = $this->processBuilder->createArgumentsForCommand('check_perms');
    $arguments->addFiles($files);

    $process = $this->processBuilder->buildProcess($arguments);
    $process->run();

    if (!$process->isSuccessful()) {
      $output = $this->formatter->format($process);
      return TaskResult::createFailed($this, $context, $output);
    }

    return TaskResult::createPassed($this, $context);
  }

}
