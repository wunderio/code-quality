<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\AbstractExternalTask;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

/**
 * Class ContextFileExternalTaskBase.
 *
 * @package Wunderio\GrumPHP\Task
 */
abstract class ContextFileExternalTaskBase extends AbstractExternalTask implements ArgumentsBuilderInterface {

  /**
   * Default.
   *
   * @var string
   */
  public const DEF = 'defaults';

  /**
   * Default.
   *
   * @var string
   */
  public const ALLOWED_TYPES = 'allowed_types';

  /**
   * Name.
   *
   * @var string
   */
  public $name = self::DEF;

  /**
   * Ignore patterns.
   *
   * @var array
   */
  public const IGNORE_PATTERNS = [
    '*/vendor/*',
    '*/node_modules/*',
    '*/core/*',
    '*/modules/contrib/*',
    '*/themes/contrib/*',
    '*/themes/contrib/*',
    '*/libraries/*',
  ];

  /**
   * Triggering extensions.
   *
   * @var array
   */
  public const EXTENSIONS = ['php', 'inc', 'module', 'install'];

  /**
   * Run on.
   *
   * @var array
   */
  public const RUN_ON = ['.'];

  /**
   * File separation.
   *
   * @var bool
   */
  public $isFileSpecific = FALSE;

  /**
   * Configurable options.
   *
   * @var array[]
   */
  public $configurableOptions = [
    'ignore_patterns' => [
      self::DEF => self::IGNORE_PATTERNS,
      self::ALLOWED_TYPES => ['array'],
    ],
    'extensions' => [
      self::DEF => self::EXTENSIONS,
      self::ALLOWED_TYPES => ['array'],
    ],
    'run_on' => [
      self::DEF => self::RUN_ON,
      self::ALLOWED_TYPES => ['array'],
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function canRunInContext(ContextInterface $context): bool {
    return $context instanceof GitPreCommitContext || $context instanceof RunContext;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurableOptions(): OptionsResolver {
    $resolver = new OptionsResolver();
    $defaults = [];
    foreach ($this->configurableOptions as $option_name => $option) {
      $defaults[$option_name] = $option[self::DEF];
    }
    $resolver->setDefaults($defaults);
    foreach ($this->configurableOptions as $option_name => $option) {
      $resolver->addAllowedTypes($option_name, $option[self::ALLOWED_TYPES]);
    }

    return $resolver;
  }

  /**
   * {@inheritdoc}
   */
  public function run(ContextInterface $context): TaskResultInterface {
    $files = $result = $this->getFilesOrResult($context, $this->isFileSpecific);
    if ($result  instanceof TaskResultInterface) {
      return $result;
    }

    $process = $this->processBuilder->buildProcess($this->buildArguments($files));
    $process->run();

    return $this->getTaskResult($context, $process);
  }

  /**
   * Return files.
   *
   * @param \GrumPHP\Task\Context\ContextInterface $context
   *   Current context.
   *
   * @return array|\GrumPHP\Collection\FilesCollection
   *   File collection.
   */
  public function getFiles(ContextInterface $context) {
    $config = $this->getConfiguration();

    // Deal with pre commit files first.
    if ($context instanceof GitPreCommitContext) {
      return $this->getContextFiles($context, $config);
    }

    // When not in git context and no separation requested - return directories.
    if (!$this->isFileSpecific) {
      return $config['run_on'];
    }

    // Finally find separate files from configured directories.
    return $this->getFilesFromConfig($config);
  }

  /**
   * Get files from current context.
   *
   * @param \GrumPHP\Task\Context\ContextInterface $context
   *   Context.
   * @param array $config
   *   Configuration.
   *
   * @return \GrumPHP\Collection\FilesCollection
   *   File collection.
   */
  public function getContextFiles(ContextInterface $context, array $config): FilesCollection {
    $files = $context->getFiles()
      ->extensions($config['extensions'])
      ->paths($config['run_on'])
      ->notPaths($config['ignore_patterns']);

    return $files;
  }

  /**
   * File finder.
   *
   * @return \Symfony\Component\Finder\Finder
   *   File finder.
   */
  public function getFileFinder(): Finder {
    return Finder::create()->files();
  }

  /**
   * Searches and returns files configured in configuration.
   *
   * @param array $config
   *   Configuration.
   *
   * @return \GrumPHP\Collection\FilesCollection
   *   File collection.
   */
  public function getFilesFromConfig(array $config): FilesCollection {
    $files = $this->getFileFinder();
    foreach ($config['extensions'] as $extension) {
      $files->name('*.' . $extension);
    }
    $run_on = $config['run_on'] ?? ['.'];
    foreach ($run_on as $dir) {
      $files->in($dir);
    }
    foreach ($config['ignore_patterns'] as $ignore_pattern) {
      $files->notPath(str_replace(['*/', '/*'], '', $ignore_pattern));
    }

    return new FilesCollection(iterator_to_array($files->getIterator()));
  }

  /**
   * Returns task result.
   *
   * @param \GrumPHP\Task\Context\ContextInterface $context
   *   Current context.
   * @param \Symfony\Component\Process\Process $process
   *   Process.
   *
   * @return \GrumPHP\Runner\TaskResult
   *   Result.
   */
  public function getTaskResult(ContextInterface $context, Process $process): TaskResult {
    if (!$process->isSuccessful()) {
      return TaskResult::createFailed($this, $context, $this->formatter->format($process));
    }

    return TaskResult::createPassed($this, $context);
  }

  /**
   * Get files or task result.
   *
   * @param \GrumPHP\Task\Context\ContextInterface $context
   *   Current context.
   *
   * @return array|FilesCollection|TaskResult
   *   Files or result.
   */
  public function getFilesOrResult(ContextInterface $context) {
    $files = $this->getFiles($context);
    if ($context instanceof GitPreCommitContext && \count($files) === 0) {
      return TaskResult::createSkipped($this, $context);
    }
    return $files;
  }

}
