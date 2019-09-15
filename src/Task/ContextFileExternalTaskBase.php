<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\AbstractExternalTask;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ContextFileExternalTaskBase.
 *
 * @package Wunderio\GrumPHP\Task
 */
abstract class ContextFileExternalTaskBase extends AbstractExternalTask implements ArgumentsBuilderInterface {

  /**
   * Option Extensions.
   *
   * @var string
   */
  public const D_EXT = 'extensions';

  /**
   * Option Ignore patterns.
   *
   * @var string
   */
  public const D_IGN = 'ignore_patterns';

  /**
   * Option Run on.
   *
   * @var string
   */
  public const D_RUN = 'run_on';

  /**
   * Name.
   *
   * @var string
   */
  public $name = '';

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
  public $configurableOptions = [];

  /**
   * Construction arguments.
   *
   * @var array
   */
  public $arguments = [];

  /**
   * ContextFileExternalTaskBase constructor.
   *
   * @param \GrumPHP\Configuration\GrumPHP $grumPHP
   *   Grumphp.
   * @param \GrumPHP\Process\ProcessBuilder $processBuilder
   *   ProcessBuilder.
   * @param \GrumPHP\Formatter\ProcessFormatterInterface $formatter
   *   Formatter.
   *
   * @codeCoverageIgnore
   */
  public function __construct(GrumPHP $grumPHP, ProcessBuilder $processBuilder, ProcessFormatterInterface $formatter) {
    parent::__construct($grumPHP, $processBuilder, $formatter);
    $tasks = Yaml::parseFile(__DIR__ . '/tasks.yml');
    $configurations = $tasks[static::class] ?? $tasks[self::class];
    $this->configurableOptions = $configurations['options'] ?? $tasks[self::class]['options'];
    $this->name = $configurations['name'];
    $this->arguments = $configurations['arguments'] ?? $tasks[self::class]['arguments'];
    $this->isFileSpecific = $configurations['is_file_specific'] ?? $tasks[self::class]['is_file_specific'];
  }

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
      $defaults[$option_name] = $option['defaults'];
    }
    $resolver->setDefaults($defaults);
    foreach ($this->configurableOptions as $option_name => $option) {
      $resolver->addAllowedTypes($option_name, $option['allowed_types']);
    }

    return $resolver;
  }

  /**
   * {@inheritdoc}
   */
  public function run(ContextInterface $context): TaskResultInterface {
    $files = $result = $this->getFilesOrResult($context);
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
      return $config[self::D_RUN];
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
    return $context->getFiles()
      ->extensions($config[self::D_EXT])
      ->paths($config[self::D_RUN])
      ->notPaths($config[self::D_IGN]);
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
    foreach ($config[self::D_EXT] as $extension) {
      $files->name('*.' . $extension);
    }
    $run_on = $config[self::D_RUN] ?? ['.'];
    foreach ($run_on as $dir) {
      $files->in($dir);
    }
    foreach ($config[self::D_IGN] as $ignore_pattern) {
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
