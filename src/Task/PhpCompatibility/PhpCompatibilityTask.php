<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task\PhpCompatibility;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wunderio\GrumPHP\Task\ContextFileExternalTaskBase;

/**
 * Class PhpCompatibilityTask.
 *
 * @package Wunderio\GrumPHP\Task\PhpCompatibilityTask
 */
class PhpCompatibilityTask extends ContextFileExternalTaskBase {

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return 'php_compatibility';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurableOptions(): OptionsResolver {
    $resolver = new OptionsResolver();
    $resolver->setDefaults([
      'ignore_patterns' => static::$ignorePatterns,
      'extensions' => static::$extensions,
      'run_on' => ['.'],
      'testVersion' => '7.3',
    ]);
    $resolver->addAllowedTypes('ignore_patterns', ['array']);
    $resolver->setAllowedTypes('extensions', 'array');
    $resolver->setAllowedTypes('run_on', ['array']);
    $resolver->addAllowedTypes('testVersion', 'string');
    return $resolver;
  }

  /**
   * {@inheritdoc}
   */
  public function run(ContextInterface $context): TaskResultInterface {
    $files = $this->getFiles($context);
    if ($context instanceof GitPreCommitContext && (empty($files) || \count($files) === 0)) {
      return TaskResult::createSkipped($this, $context);
    }

    $arguments = $this->processBuilder->createArgumentsForCommand('phpcs');
    $arguments = $this->addArgumentsFromConfig($arguments, $this->getConfiguration());
    $arguments->add('--standard=vendor/wunderio/code-quality/php-compatibility.xml');

    foreach ($files as $file) {
      $arguments->add($file);
    }

    $process = $this->processBuilder->buildProcess($arguments);
    $process->run();

    if (!$process->isSuccessful()) {
      $output = $this->formatter->format($process);
      return TaskResult::createFailed($this, $context, $output);
    }

    return TaskResult::createPassed($this, $context);
  }

  /**
   * Adds arguments from configuration.
   *
   * @param \GrumPHP\Collection\ProcessArgumentsCollection $arguments
   *   Current arguments.
   * @param array $config
   *   Configuration.
   *
   * @return \GrumPHP\Collection\ProcessArgumentsCollection
   *   Modified arguments.
   */
  protected function addArgumentsFromConfig(ProcessArgumentsCollection $arguments, array $config): ProcessArgumentsCollection {
    $arguments->addOptionalCommaSeparatedArgument('--extensions=%s', (array) $config['extensions']);
    $arguments->addSeparatedArgumentArray('--runtime-set', ['testVersion', (string) $config['testVersion']]);
    return $arguments;
  }

}
