<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task\PhpstanCheckDeprecation;

use GrumPHP\Collection\ProcessArgumentsCollection;
use Wunderio\GrumPHP\Task\AbstractMultiPathProcessingTask;

/**
 * Class PhpstanCheckDeprecationTask.
 *
 * PhpstanCheckDeprecation task.
 *
 * @package Wunderio\GrumPHP\Task
 */
class PhpstanCheckDeprecationTask extends AbstractMultiPathProcessingTask {

  /**
   * {@inheritdoc}
   */
  public function buildArguments(iterable $files): ProcessArgumentsCollection {
    $arguments = $this->processBuilder->createArgumentsForCommand('phpstan');
    $config = $this->getConfig()->getOptions();
    $arguments->add('analyse');
    $arguments->addOptionalArgument('--autoload-file=%s', $config['autoload_file']);
    $arguments->addOptionalArgument('--configuration=%s', $config['configuration']);
    $arguments->addOptionalArgument('--memory-limit=%s', $config['memory_limit']);
    $arguments->addOptionalMixedArgument('--level=%s', $config['level']);
    $arguments->add('--no-ansi');
    $arguments->add('--no-interaction');
    $arguments->add('--no-progress');

    foreach ($files as $file) {
      $arguments->add($file);
    }
    return $arguments;
  }

}
