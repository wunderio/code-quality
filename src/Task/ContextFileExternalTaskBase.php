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
  protected static $ignorePatterns = [
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
  protected static $extensions = ['php', 'inc', 'module', 'install'];

  /**
   * {@inheritdoc}
   */
  public function getConfigurableOptions(): OptionsResolver {
    $resolver = new OptionsResolver();
    $resolver->setDefaults([
      'ignore_patterns' => static::$ignorePatterns,
      'extensions' => static::$extensions,
      'run_on' => ['.'],
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
   * @param bool $useSeperateFiles
   *   Flag to indicate if files should be separated.
   *
   * @return \GrumPHP\Collection\FilesCollection|array
   *   File collection.
   */
  protected function getFiles(ContextInterface $context, $useSeperateFiles = FALSE) {
    $config = $this->getConfiguration();

    // Deal with pre commit files first.
    if ($context instanceof GitPreCommitContext) {
      $files = $context->getFiles()
        ->extensions($config['extensions'])
        ->paths($config['run_on'])
        ->notPaths($config['ignore_patterns']);
      if (\count($config['run_on'])) {
        $files = $files->paths($config['run_on']);
      }

      return $files;
    }

    // When not in git context and no separation requested - return directories.
    if (!$useSeperateFiles) {
      return $config['run_on'];
    }

    // Finally find separate files from configured directories.
    $files = Finder::create()->files();
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
    $files_array = [];
    foreach ($files as $file) {
      $files_array[] = $file;
    }

    return new FilesCollection($files_array);
  }

}
