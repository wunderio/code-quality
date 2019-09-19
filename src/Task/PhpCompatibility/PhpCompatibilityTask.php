<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task\PhpCompatibility;

use GrumPHP\Collection\ProcessArgumentsCollection;
use Wunderio\GrumPHP\Task\AbstractMultiPathProcessingTask;

/**
 * Class PhpCompatibilityTask.
 *
 * @package Wunderio\GrumPHP\Task
 */
class PhpCompatibilityTask extends AbstractMultiPathProcessingTask {

  /**
   * {@inheritdoc}
   */
  public function buildArguments(iterable $files): ProcessArgumentsCollection {
    $arguments = $this->processBuilder->createArgumentsForCommand('phpcs');
    $config = $this->getConfiguration();
    $arguments->addOptionalCommaSeparatedArgument('--extensions=%s', (array) $config[self::D_EXT]);
    $arguments->addOptionalIntegerArgument('--parallel=%s', $config['parallel']);
    $arguments->addSeparatedArgumentArray('--runtime-set', ['testVersion', (string) $config['testVersion']]);
    $arguments->add('--standard=' . $config['standard']);

    foreach ($files as $file) {
      $arguments->add($file);
    }

    return $arguments;
  }

}
