<?php

declare(strict_types=1);

namespace Wunderio\GrumPHP\Task\PhpCheckSyntaxTask;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;
use GrumPHP\Task\AbstractExternalTask;

/**
 * Class PhpCheckSyntaxTask.
 *
 * @package Wunderio\GrumPHP\Task\PhpCheckSyntaxTask
 */
class PhpCheckSyntaxTask extends AbstractExternalTask
{
  /**
   * {@inheritdoc}
   */
  public function getName(): string
  {
    return 'php_check_syntax';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurableOptions(): OptionsResolver
  {
    $resolver = new OptionsResolver();
    $resolver->setDefaults([
      'ignore_patterns' => [],
      'triggered_by' => ['php', 'inc', 'module', 'phtml', 'php3', 'php4', 'php5'],
    ]);
    $resolver->addAllowedTypes('ignore_patterns', ['array']);
    $resolver->setAllowedTypes('triggered_by', 'array');
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
    $files = $context
      ->getFiles()
      ->notPaths($config['ignore_patterns'])
      ->extensions($config['triggered_by']);
    if ($files->isEmpty()) {
      return TaskResult::createSkipped($this, $context);
    }

    $arguments = $this->processBuilder->createArgumentsForCommand('php');
    $arguments->add('-l');
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
