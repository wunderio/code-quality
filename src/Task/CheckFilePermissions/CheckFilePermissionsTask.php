<?php

declare(strict_types=1);

namespace Wunderio\GrumPHP\Task\CheckFilePermissions;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\Finder\Finder;
use Symfony\Component\OptionsResolver\OptionsResolver;
use GrumPHP\Task\AbstractExternalTask;
use Wunderio\GrumPHP\Task\ContextFileExternalTaskBase;

/**
 * Class CheckFilePermissionsTask.
 *
 * @package Wunderio\GrumPHP\CheckFilePermissions
 */
class CheckFilePermissionsTask extends ContextFileExternalTaskBase
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
    $resolver = new OptionsResolver();
    $resolver->setDefaults([
      'ignore_patterns' => ['*/vendor/*','*/node_modules/*', '*/core/*', '*/modules/contrib/*', '*/themes/contrib/*'],
      'extensions' => ['sh', 'py'],
      'run_on' => ['.']
    ]);
    $resolver->addAllowedTypes('ignore_patterns', ['array']);
    $resolver->setAllowedTypes('extensions', 'array');
    $resolver->setAllowedTypes('run_on', ['array']);
    return $resolver;
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
    $config = $this->getConfiguration();
    $files = $this->getFiles($context, $config);

    if (0 === \count($files)) {
      return TaskResult::createSkipped($this, $context);
    }

    $arguments = $this->processBuilder->createArgumentsForCommand('check_perms');
    if (!$context instanceof GitPreCommitContext) {
      $files = Finder::create()->files();
      foreach ($config['extensions'] as $extension) {
       $files->name('*.' . $extension);
      }
      foreach ($config['run_on'] as $run_on) {
       $files->in($run_on);
      }
      foreach ($config['ignore_patterns'] as $ignore_pattern) {
       $files->notPath(str_replace(['*/', '/*'], '', $ignore_pattern));
      }
      $files_array = [];
      foreach ($files as $file) {
       $files_array[] = $file;
      }

      $files = new FilesCollection($files_array);
    }

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
