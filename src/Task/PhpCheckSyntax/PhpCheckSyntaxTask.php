<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task\PhpCheckSyntax;

use GrumPHP\Collection\ProcessArgumentsCollection;
use Wunderio\GrumPHP\Task\IndividualContextFileExternalTaskBase;

/**
 * Class PhpCheckSyntaxTask.
 *
 * @package Wunderio\GrumPHP\Task\PhpCheckSyntaxTask
 */
class PhpCheckSyntaxTask extends IndividualContextFileExternalTaskBase {

  /**
   * Name.
   *
   * @var string
   */
  public $name = 'php_check_syntax';

  /**
   * {@inheritdoc}
   */
  public function buildArguments(iterable $files): ProcessArgumentsCollection {
    $arguments = $this->processBuilder->createArgumentsForCommand('php');
    $arguments->add('-l');
    foreach ($files as $file) {
      $arguments->add($file);
    }

    return $arguments;
  }

}
