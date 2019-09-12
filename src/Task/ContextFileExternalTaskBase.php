<?php

namespace Wunderio\GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Task\AbstractExternalTask;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Finder\Finder;

/**
 * Trait ContextFileTrait.
 *
 * @package Wunderio\GrumPHP\Task
 */
abstract class ContextFileExternalTaskBase extends AbstractExternalTask {

  /**
   * Ignore patterns.
   *
   * @var array
   */
  public static $ignorePatterns = [
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
  public static $extensions = ['php', 'inc', 'module', 'install'];

  /**
   * Run on.
   *
   * @var array
   */
  public static $runOn = ['.'];

  /**
   * {@inheritdoc}
   */
  public function getConfigurableOptions(): OptionsResolver {
    $resolver = new OptionsResolver();
    $resolver->setDefaults([
      'ignore_patterns' => static::$ignorePatterns,
      'extensions' => static::$extensions,
      'run_on' => static::$extensions,
    ]);
    $resolver->addAllowedTypes('ignore_patterns', ['array']);
    $resolver->setAllowedTypes('extensions', 'array');
    $resolver->setAllowedTypes('run_on', ['array']);
    return $resolver;
  }

  /**
   * Return files.
   *
   * @param \GrumPHP\Task\Context\ContextInterface $context
   *   Current context.
   * @param bool $useSeparateFiles
   *   Flag to indicate if files should be separated.
   *
   * @return array|\GrumPHP\Collection\FilesCollection
   *   File collection.
   */
  public function getFiles(ContextInterface $context, $useSeparateFiles = FALSE) {
    $config = $this->getConfiguration();

    // Deal with pre commit files first.
    if ($context instanceof GitPreCommitContext) {
      return $this->getContextFiles($context, $config);
    }

    // When not in git context and no separation requested - return directories.
    /** @var array $paths */
    $paths = $config['run_on'];
    if (!$useSeparateFiles) {
      return $paths;
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
  public function getContextFiles(ContextInterface $context, array $config) {
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
  public function getFilesFromConfig(array $config) {
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

}
