<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task\Phpcs;

use GrumPHP\Collection\ProcessArgumentsCollection;
use Wunderio\GrumPHP\Task\AbstractMultiPathProcessingTask;

/**
 * Class PhpCompatibilityTask.
 *
 * @package Wunderio\GrumPHP\Task
 */
class PhpcsTask extends AbstractMultiPathProcessingTask {

  /**
   * {@inheritdoc}
   */
  public function buildArguments(iterable $files): ProcessArgumentsCollection {
    $arguments = $this->processBuilder->createArgumentsForCommand('phpcs');
    $config = $this->getConfig()->getOptions();
    $config['basepath'] = $config['basepath'] ?? '.';
    $arguments->addOptionalCommaSeparatedArgument('--standard=%s', (array) $config['standard']);
    $arguments->addOptionalCommaSeparatedArgument('--extensions=%s', (array) $config['extensions']);
    $arguments->addOptionalArgument('--tab-width=%s', $config['tab_width']);
    $arguments->addOptionalArgument('--encoding=%s', $config['encoding']);
    $arguments->addOptionalArgument('--report=%s', $config['report']);
    $arguments->addOptionalIntegerArgument('--report-width=%s', $config['report_width']);
    $arguments->addOptionalIntegerArgument('--severity=%s', $config['severity']);
    $arguments->addOptionalIntegerArgument('--error-severity=%s', $config['error_severity']);
    $arguments->addOptionalIntegerArgument('--warning-severity=%s', $config['warning_severity']);
    $arguments->addOptionalIntegerArgument('--parallel=%s', $config['parallel']);
    $arguments->addOptionalCommaSeparatedArgument('--sniffs=%s', $config['sniffs']);
    $arguments->addOptionalCommaSeparatedArgument('--ignore=%s', $config['ignore_patterns']);
    $arguments->addOptionalCommaSeparatedArgument('--exclude=%s', $config['exclude']);
    $arguments->add('--basepath=' . $config['basepath']);
    $arguments->add('-s');

    foreach ($files as $file) {
      $arguments->add($file);
    }

    return $arguments;
  }

}
