<?php

/**
 * @file
 * Tests covering PhpUnitDrupalModulesTask.
 */

declare(strict_types=1);

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Task\Config\TaskConfigInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Wunderio\GrumPHP\Task\PhpUnitDrupalModules\PhpUnitDrupalModulesTask;

/**
 * Class PhpUnitDrupalModulesTaskTest.
 *
 * Tests covering PhpUnitDrupalModulesTask task.
 */
final class PhpUnitDrupalModulesTaskTest extends TestCase {

  /**
   * Test building arguments with custom config file and testsuite.
   *
   * @covers \Wunderio\GrumPHP\Task\PhpUnitDrupalModules\PhpUnitDrupalModulesTask::buildArguments
   */
  public function testBuildsProcessArgumentsWithConfigAndTestsuite(): void {
    $processBuilder = $this->createMock(ProcessBuilder::class);
    $stub = $this->getMockBuilder(PhpUnitDrupalModulesTask::class)->setConstructorArgs([
      $processBuilder,
      $this->createMock(ProcessFormatterInterface::class),
    ])
      ->setMethodsExcept(['buildArguments'])->getMock();

    $arguments = $this->createMock(ProcessArgumentsCollection::class);
    $taskConfig = $this->createMock(TaskConfigInterface::class);

    $modules = [
      'web/modules/custom/foo',
      'web/modules/custom/bar',
    ];

    $processBuilder->expects($this->once())
      ->method('createArgumentsForCommand')
      ->with('phpunit')
      ->willReturn($arguments);

    // Expect arguments for:
    // - config file (-c <file>)
    // - testsuite (--testsuite <name>)
    // - each module path.
    $arguments->expects($this->exactly(6))
      ->method('add')
      ->withConsecutive(
        ['-c'],
        ['phpunit.ddev.xml'],
        ['--testsuite'],
        ['unit'],
        ['web/modules/custom/foo'],
        ['web/modules/custom/bar']
      );

    $config = [];
    foreach ($this->getConfigurations() as $name => $option) {
      $config[$name] = $option['defaults'];
    }

    // Explicitly configure a custom phpunit config file and testsuite to
    // verify they are included in the arguments.
    $config['config_file'] = 'phpunit.ddev.xml';
    $config['testsuite'] = 'unit';

    $stub->expects($this->once())
      ->method('getConfig')
      ->willReturn($taskConfig);
    $taskConfig->method('getOptions')->willReturn($config);

    $actual = $stub->buildArguments($modules);
    $this->assertInstanceOf(ProcessArgumentsCollection::class, $actual);
  }

  /**
   * Test building arguments without optional config.
   *
   * @covers \Wunderio\GrumPHP\Task\PhpUnitDrupalModules\PhpUnitDrupalModulesTask::buildArguments
   */
  public function testBuildsProcessArgumentsWithOnlyModules(): void {
    $processBuilder = $this->createMock(ProcessBuilder::class);
    $stub = $this->getMockBuilder(PhpUnitDrupalModulesTask::class)->setConstructorArgs([
      $processBuilder,
      $this->createMock(ProcessFormatterInterface::class),
    ])
      ->setMethodsExcept(['buildArguments'])->getMock();

    $arguments = $this->createMock(ProcessArgumentsCollection::class);
    $taskConfig = $this->createMock(TaskConfigInterface::class);

    $modules = [
      'web/modules/custom/foo',
      'web/modules/custom/bar',
    ];

    $processBuilder->expects($this->once())
      ->method('createArgumentsForCommand')
      ->with('phpunit')
      ->willReturn($arguments);

    // Without config_file or testsuite, we should only see module paths.
    $arguments->expects($this->exactly(2))
      ->method('add')
      ->withConsecutive(
        ['web/modules/custom/foo'],
        ['web/modules/custom/bar']
      );

    $config = [];
    foreach ($this->getConfigurations() as $name => $option) {
      $config[$name] = $option['defaults'];
    }

    $stub->expects($this->once())
      ->method('getConfig')
      ->willReturn($taskConfig);
    $taskConfig->method('getOptions')->willReturn($config);

    $actual = $stub->buildArguments($modules);
    $this->assertInstanceOf(ProcessArgumentsCollection::class, $actual);
  }

  /**
   * Gets task configurations.
   *
   * @return array
   *   Array of options.
   */
  protected function getConfigurations(): array {
    $tasks = Yaml::parseFile(__DIR__ . '/../../src/Task/tasks.yml');
    return $tasks[PhpUnitDrupalModulesTask::class]['options'];
  }

}
