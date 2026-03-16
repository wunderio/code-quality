<?php

declare(strict_types=1);

namespace Wunderio\GrumPHP\Task\PhpUnitDrupalModules;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use Wunderio\GrumPHP\Task\AbstractMultiPathProcessingTask;

/**
 * Class PhpUnitDrupalModulesTask.
 *
 * Runs phpunit only for affected Drupal custom modules.
 */
class PhpUnitDrupalModulesTask extends AbstractMultiPathProcessingTask {

  /**
   * {@inheritdoc}
   */
  public function run(ContextInterface $context): TaskResultInterface {
    $paths = $this->getPathsOrResult($context, $this->getConfig()->getOptions(), $this);

    if ($paths instanceof TaskResultInterface) {
      return $paths;
    }

    $modules = [];
    foreach ($paths as $file) {
      $path = (string) $file;
      // Only consider custom Drupal modules. Contrib modules are intentionally ignored.
      if (!str_starts_with($path, 'web/modules/custom/')) {
        continue;
      }

      if (preg_match('#^(web/modules/custom/[^/]+)#', $path, $matches)) {
        $modules[$matches[1]] = $matches[1];
      }
    }

    // No affected modules -> nothing to run.
    if (!$modules) {
      return TaskResult::createSkipped($this, $context);
    }

    $process = $this->processBuilder->buildProcess($this->buildArguments($modules));
    $process->run();

    return $this->getTaskResult($process, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function buildArguments(iterable $modules): ProcessArgumentsCollection {
    $config = $this->getConfig()->getOptions();

    $arguments = $this->processBuilder->createArgumentsForCommand('phpunit');

    if (!empty($config['config_file'])) {
      // Mirror GrumPHP's core phpunit task: allow passing a custom config file.
      $arguments->add('-c');
      $arguments->add($config['config_file']);
    }

    if (!empty($config['testsuite'])) {
      $arguments->add('--testsuite');
      $arguments->add($config['testsuite']);
    }

    foreach ($modules as $modulePath) {
      $arguments->add($modulePath);
    }

    return $arguments;
  }

}

