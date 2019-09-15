<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task\CheckFilePermissions;

use GrumPHP\Collection\ProcessArgumentsCollection;
use Wunderio\GrumPHP\Task\AbstractConfigurableContextFileExternalTask;

/**
 * Class CheckFilePermissionsTask.
 *
 * @package Wunderio\GrumPHP\CheckFilePermissions
 */
class CheckFilePermissionsTask extends AbstractConfigurableContextFileExternalTask {

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
