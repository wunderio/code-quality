<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task\YamlLint;

use GrumPHP\Linter\LinterInterface;
use Wunderio\GrumPHP\Task\AbstractLintTask;

/**
 * Class YamlLintTask.
 *
 * YamlLint task.
 *
 * @package Wunderio\GrumPHP\Task
 */
class YamlLintTask extends AbstractLintTask {

  /**
   * {@inheritdoc}
   */
  public function configureLint(LinterInterface $linter): void {
    $config = $this->getConfig()->getOptions();
    $linter->setObjectSupport($config['object_support']);
    $linter->setExceptionOnInvalidType($config['exception_on_invalid_type']);
    $linter->setParseCustomTags($config['parse_custom_tags']);
    $linter->setParseConstants($config['parse_constant']);
  }

}
