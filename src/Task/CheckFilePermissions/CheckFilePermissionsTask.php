<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task\CheckFilePermissions;

use GrumPHP\Collection\ProcessArgumentsCollection;
use Wunderio\GrumPHP\Task\AbstractMultiPathProcessingTask;

/**
 * Class CheckFilePermissionsTask.
 *
 * CheckFilePermissions task.
 *
 * @package Wunderio\GrumPHP\Task
 */
class CheckFilePermissionsTask extends AbstractMultiPathProcessingTask {

  /**
   * {@inheritdoc}
   */
  public function buildArguments(iterable $files): ProcessArgumentsCollection {
    /** @var \GrumPHP\Collection\FilesCollection $files */
    $arguments = $this->processBuilder->createArgumentsForCommand('check_perms');
    $arguments->addFiles($files);

    return $arguments;
  }

}
