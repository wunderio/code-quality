<?php

namespace Wunderio\GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use Symfony\Component\Finder\Finder;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * Trait ConfigurableTaskTrait.
 *
 * @package Wunderio\GrumPHP\Task
 */
trait ConfigurableTaskTrait {

  /**
   * Name.
   *
   * @var string
   */
  public $name;

  /**
   * File separation.
   *
   * @var bool
   */
  public $isFileSpecific;

  /**
   * Configurable options.
   *
   * @var array[]
   */
  public $configurableOptions;

  /**
   * Configurable options.
   *
   * @var array[]
   */
  public $configuration;

  /**
   * Configure Task properties.
   */
  public function configure(): void {
    $tasks = Yaml::parseFile(__DIR__ . '/tasks.yml');
    $default_configuration = $tasks['default'];
    unset($default_configuration['name']);
    $configurations = $tasks[static::class] ?? $default_configuration;
    $this->configurableOptions = $configurations['options'] ?? $default_configuration['options'];
    $class_name = explode('\\', static::class);
    $default_name = strtolower(preg_replace('/\B([A-Z])/', '_$1', str_replace('Task', '', end($class_name))));
    $this->name = $configurations['name'] ?? $default_name;
    $this->isFileSpecific = $configurations['is_file_specific'] ?? $default_configuration['is_file_specific'];
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
   * Return appropriate files or directories based on context and configuration.
   *
   * @param \GrumPHP\Task\Context\ContextInterface $context
   *   Current context.
   * @param array $config
   *   Task configuration.
   * @param bool $isFileSpecific
   *   Flag indicating if directories can be used or just files.
   *
   * @return array|\GrumPHP\Collection\FilesCollection
   *   File collection.
   */
  public static function getPaths(ContextInterface $context, array $config, bool $isFileSpecific) {
    // Deal with pre commit files first.
    if ($context instanceof GitPreCommitContext) {
      return static::getContextFiles($context, $config);
    }

    // When not in git context and no separation requested - return directories.
    if (!$isFileSpecific) {
      return $config[ConfigurableTaskInterface::D_RUN];
    }

    // Finally find separate files from configured directories.
    return static::getFilesFromConfig($config);
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
  public static function getContextFiles(ContextInterface $context, array $config): FilesCollection {
    return $context->getFiles()
      ->extensions($config[ConfigurableTaskInterface::D_EXT])
      ->paths($config[ConfigurableTaskInterface::D_RUN])
      ->notPaths($config[ConfigurableTaskInterface::D_IGN]);
  }

  /**
   * File finder.
   *
   * @return \Symfony\Component\Finder\Finder
   *   File finder.
   */
  public static function getFileFinder(): Finder {
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
  public static function getFilesFromConfig(array $config): FilesCollection {
    $files = static::getFileFinder();
    $run_on = $config[ConfigurableTaskInterface::D_RUN] ?? ['.'];
    foreach ($run_on as $dir) {
      $files->in($dir);
    }
    $files = new FilesCollection(iterator_to_array($files->getIterator()));

    return $files->extensions($config[ConfigurableTaskInterface::D_EXT])
      ->notPaths($config[ConfigurableTaskInterface::D_IGN]);
  }

  /**
   * Get files or task result.
   *
   * @param \GrumPHP\Task\Context\ContextInterface $context
   *   Current context.
   * @param array $config
   *   Task configuration.
   * @param ConfigurableTaskInterface $task
   *   Flag if files should be separated or whole directories can be used.
   *
   * @return array|FilesCollection|TaskResult
   *   Files or task result.
   */
  public function getPathsOrResult(ContextInterface $context, array $config, ConfigurableTaskInterface $task) {
    $files = static::getPaths($context, $config, $task->isFileSpecific());
    if ($context instanceof GitPreCommitContext && \count($files) === 0) {
      return TaskResult::createSkipped($task, $context);
    }
    return $files;
  }

  /**
   * Flag indicating that paths should be retrieved as files or directories.
   *
   * @return bool
   *   True if only file paths can be used, False if directories.
   */
  public function isFileSpecific(): bool {
    return (bool) $this->isFileSpecific;
  }

}
