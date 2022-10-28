<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task\ESLint;

use GrumPHP\Collection\ProcessArgumentsCollection;
use Wunderio\GrumPHP\Task\AbstractMultiPathProcessingTask;

/**
 * Class ESLint2Task.
 *
 * Eslint task.
 *
 * @package Wunderio\GrumPHP\Task
 */
class EslintTask extends AbstractMultiPathProcessingTask {

  /**
   * {@inheritdoc}
   */
  public function buildArguments(iterable $files): ProcessArgumentsCollection {
    $config = $this->getConfig()->getOptions();
    $arguments = ProcessArgumentsCollection::forExecutable($config['bin']);

    $arguments->add('--config=' . $config['config']);
    $arguments->addOptionalArgument('--debug', $config['debug']);
    $arguments->addOptionalCommaSeparatedArgument('--ext=%s', (array) $config['extensions']);
    $arguments->addOptionalArgument('--format=%s', $config['format']);
    // @todo Not sure if this works.
    $arguments->addOptionalIntegerArgument('--max-warnings=%d', $config['max_warnings']);
    $arguments->addOptionalBooleanArgument('--no-eslintrc=%s', $config['no_eslintrc'], 'true', 'false');
    $arguments->addOptionalBooleanArgument('--quiet=%s', $config['quiet'], 'true', 'false');
    foreach ($config['ignore_patterns'] as $ignore_pattern) {
      $arguments->add('--ignore-pattern=' . $ignore_pattern);
    }

    foreach ($files as $file) {
      $files_new[] = $file;
      $arguments->add($file);
    }

    return $arguments;
  }

}
