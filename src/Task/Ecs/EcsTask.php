<?php

declare(strict_types=1);

namespace Wunderio\GrumPHP\Task\Ecs;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;
use GrumPHP\Task\AbstractExternalTask;
use GrumPHP\Runner\TaskResultInterface;
use Wunderio\GrumPHP\Task\ContextFileExternalTaskBase;

/**
 * Class EcsTask.
 *
 * @package Wunderio\GrumPHP\Task\Ecs
 */
class EcsTask extends ContextFileExternalTaskBase
{
  /**
   * {@inheritdoc}
   */
  public function getName(): string
  {
    return 'ecs';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurableOptions(): OptionsResolver
  {
    $resolver = new OptionsResolver();
    $resolver->setDefaults([
      'ignore_patterns' => ['*/vendor/*','*/node_modules/*', '*/core/*', '*/modules/contrib/*', '*/themes/contrib/*'],
      'extensions' => ['php', 'inc', 'module', 'install'],
      'run_on' => ['.'],
      'clear-cache' => false,
      'no-progress-bar' => true,
      'config' => null,
      'level' => null,
    ]);

    $resolver->addAllowedTypes('ignore_patterns', ['array']);
    $resolver->setAllowedTypes('extensions', 'array');
    $resolver->setAllowedTypes('run_on', ['array']);
    $resolver->addAllowedTypes('clear-cache', ['bool']);
    $resolver->addAllowedTypes('no-progress-bar', ['bool']);
    $resolver->addAllowedTypes('config', ['null', 'string']);
    $resolver->addAllowedTypes('level', ['null', 'string']);

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
    if ($files->isEmpty()) {
      return TaskResult::createSkipped($this, $context);
    }

    $arguments = $this->processBuilder->createArgumentsForCommand('ecs');
    $arguments->add('check');

    if ($context instanceof GitPreCommitContext) {
      $arguments->addFiles($files);
    }
    else {
      foreach ($config['run_on'] as $whitelistPattern) {
        $arguments->add($whitelistPattern);
      }
    }

    $arguments->addOptionalArgument('--config=%s', $config['config']);
    $arguments->addOptionalArgument('--level=%s', $config['level']);
    $arguments->addOptionalArgument('--clear-cache', $config['clear-cache']);
    $arguments->addOptionalArgument('--no-progress-bar', $config['no-progress-bar']);
    $arguments->addOptionalArgument('--ansi', true);
    $arguments->addOptionalArgument('--no-interaction', true);

    $process = $this->processBuilder->buildProcess($arguments);
    $process->run();

    if (!$process->isSuccessful()) {
      return TaskResult::createFailed($this, $context, $this->formatter->format($process));
    }

    return TaskResult::createPassed($this, $context);
  }
}
