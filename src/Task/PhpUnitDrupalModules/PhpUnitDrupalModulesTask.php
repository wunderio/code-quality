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
    $config = $this->getConfig()->getOptions();
    $paths = $this->getPathsOrResult($context, $config, $this);

    if ($paths instanceof TaskResultInterface) {
      return $paths;
    }

    // Determine which directory roots should be treated as Drupal module roots.
    // Defaults to web/modules/custom for backward compatibility, but can be
    // configured via the run_on option in tasks.yml.
    $moduleRoots = $config['run_on'] ?? ['web/modules/custom'];
    $normalisedRoots = [];
    foreach ($moduleRoots as $root) {
      $normalisedRoots[] = rtrim((string) $root, '/');
    }
    $moduleRoots = array_values($normalisedRoots);

    $modules = [];
    foreach ($paths as $file) {
      $path = (string) $file;
      foreach ($moduleRoots as $root) {
        $rootWithSlash = $root . '/';

        if (!str_starts_with($path, $rootWithSlash)) {
          continue;
        }

        if (preg_match('#^(' . preg_quote($root, '#') . '/[^/]+)#', $path, $matches)) {
          $modules[$matches[1]] = $matches[1];
        }
      }
    }

    // Further restrict to modules that actually have a tests directory.
    $modulesWithTests = [];
    foreach ($modules as $modulePath) {
      if (is_dir($modulePath . '/tests')) {
        $modulesWithTests[] = $modulePath;
      }
    }

    // If there are affected modules without tests, let the user know.
    $modulesWithoutTests = array_values(array_diff($modules, $modulesWithTests));
    if ($modulesWithoutTests) {
      $lines = [];
      foreach ($modulesWithoutTests as $modulePath) {
        $lines[] = '  - ' . $modulePath;
      }
      fwrite(
        STDOUT,
        "\nphpunit_drupal_modules: NOTE: affected modules without tests:\n" .
        implode("\n", $lines) .
        "\n\n"
      );
    }

    // No affected modules with tests -> nothing to run.
    if (!$modulesWithTests) {
      return TaskResult::createSkipped($this, $context);
    }

    // Provide a short hint about which modules will be tested.
    // This mirrors GrumPHP's own task output style without being too noisy.
    $lines = [];
    foreach ($modulesWithTests as $modulePath) {
      $lines[] = '  - ' . $modulePath;
    }
    fwrite(
      STDOUT,
      "phpunit_drupal_modules: running tests for modules:\n" .
      implode("\n", $lines) .
      "\n\n"
    );

    $process = $this->processBuilder->buildProcess($this->buildArguments($modulesWithTests));
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
