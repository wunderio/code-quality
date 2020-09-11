<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task\Ecs;

use GrumPHP\Collection\ProcessArgumentsCollection;
use Wunderio\GrumPHP\Task\AbstractMultiPathProcessingTask;

/**
 * Class EcsTask.
 *
 * Builds ecs task.
 *
 * @package Wunderio\GrumPHP\Task
 */
class EcsTask extends AbstractMultiPathProcessingTask {

  /**
   * {@inheritdoc}
   */
  public function buildArguments(iterable $files): ProcessArgumentsCollection {
    $arguments = $this->processBuilder->createArgumentsForCommand('ecs');
    $arguments->add('check');

    foreach ($files as $file) {
      $arguments->add($file);
    }

    $config = $this->getConfiguration();
    $arguments->addOptionalArgument('--config=%s', $config['config']);
    $arguments->addOptionalArgument('--level=%s', $config['level']);
    $arguments->addOptionalArgument('--clear-cache', $config['clear-cache']);
    $arguments->addOptionalArgument('--no-progress-bar', $config['no-progress-bar']);
    $arguments->addOptionalArgument('--ansi', TRUE);
    $arguments->addOptionalArgument('--no-interaction', TRUE);

    return $arguments;
  }

}
