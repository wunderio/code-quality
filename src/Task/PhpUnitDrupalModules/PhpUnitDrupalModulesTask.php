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

    $moduleRoots = $this->getModuleRoots($config);
    $modules = $this->collectModulesFromPaths($paths, $moduleRoots);

    [$modulesWithTests, $modulesWithoutTests] = $this->splitModulesByTests($modules);

    $this->printModulesWithoutTests($modulesWithoutTests);

    if (!$modulesWithTests) {
      return TaskResult::createSkipped($this, $context);
    }

    $this->printModulesWithTests($modulesWithTests);

    $process = $this->processBuilder->buildProcess($this->buildArguments($modulesWithTests));
    $process->run();

    return $this->getTaskResult($process, $context);
  }

  /**
   * Determine which directory roots should be treated as Drupal module roots.
   *
   * Defaults to web/modules/custom for backward compatibility, but can be
   * configured via the run_on option in tasks.yml.
   *
   * @param array $config
   *   Task configuration options.
   *
   * @return string[]
   *   Normalised module root paths.
   */
  private function getModuleRoots(array $config): array {
    $moduleRoots = $config['run_on'] ?? ['web/modules/custom'];
    $normalisedRoots = [];
    foreach ($moduleRoots as $root) {
      $normalisedRoots[] = rtrim((string) $root, '/');
    }
    return array_values($normalisedRoots);
  }

  /**
   * Collect all affected modules from the changed file paths.
   *
   * @param iterable $paths
   *   Changed paths.
   * @param string[] $moduleRoots
   *   Module root directories.
   *
   * @return string[]
   *   Module paths keyed by path for uniqueness.
   */
  private function collectModulesFromPaths(iterable $paths, array $moduleRoots): array {
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

    return $modules;
  }

  /**
   * Split modules into ones with and without tests directories.
   *
   * @param string[] $modules
   *   All affected modules.
   *
   * @return array{0: string[], 1: string[]}
   *   First array contains modules with tests, second without.
   */
  private function splitModulesByTests(array $modules): array {
    $modulesWithTests = [];
    foreach ($modules as $modulePath) {
      if (is_dir($modulePath . '/tests')) {
        $modulesWithTests[] = $modulePath;
      }
    }

    $modulesWithoutTests = array_values(array_diff($modules, $modulesWithTests));

    return [$modulesWithTests, $modulesWithoutTests];
  }

  /**
   * Print a note about affected modules that do not have tests.
   *
   * @param string[] $modulesWithoutTests
   *   Affected modules without tests.
   */
  private function printModulesWithoutTests(array $modulesWithoutTests): void {
    if (!$modulesWithoutTests) {
      return;
    }

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

  /**
   * Print a list of modules for which tests will be executed.
   *
   * @param string[] $modulesWithTests
   *   Affected modules with tests.
   */
  private function printModulesWithTests(array $modulesWithTests): void {
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
