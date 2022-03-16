<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task\Psalm;

use GrumPHP\Collection\ProcessArgumentsCollection;
use Wunderio\GrumPHP\Task\AbstractMultiPathProcessingTask;

/**
 * Class PsalmTask.
 *
 * Psalm task.
 *
 * @package Wunderio\GrumPHP\Task
 */
class PsalmTask extends AbstractMultiPathProcessingTask {

  /**
   * {@inheritdoc}
   */
  public function buildArguments(iterable $files): ProcessArgumentsCollection {
    $arguments = $this->processBuilder->createArgumentsForCommand('psalm');
    $config = $this->getConfig()->getOptions();
    $arguments->addOptionalArgument('--output-format=%s', $config['output_format']);
    $arguments->addOptionalArgument('--config=%s', $config['config']);
    $arguments->addOptionalArgument('--report=%s', $config['report']);
    $arguments->addOptionalArgument('--no-cache', $config['no_cache']);
    if (!extension_loaded('pcntl') || !extension_loaded('posix')) {
      // Psalm will error if we don't have pcntl or posix installed as
      // we have increased the threads in our default configs.
      $arguments->addOptionalArgument('--threads=%d', 1);
    }
    else {
      $arguments->addOptionalArgument('--threads=%d', $config['threads']);
    }
    $arguments->add('--find-unused-code');
    $arguments->addOptionalBooleanArgument('--show-info=%s', $config['show_info'], 'true', 'false');

    foreach ($files as $file) {
      $arguments->add($file);
    }

    return $arguments;
  }

}
