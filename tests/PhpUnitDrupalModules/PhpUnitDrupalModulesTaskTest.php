<?php

/**
 * @file
 * Tests covering PhpUnitDrupalModulesTask.
 */

declare(strict_types=1);

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\TaskConfigInterface;
use GrumPHP\Task\Context\ContextInterface;
use Symfony\Component\Process\Process;
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
   * Ensure run() skips when there are no modules with tests.
   *
   * @covers \Wunderio\GrumPHP\Task\PhpUnitDrupalModules\PhpUnitDrupalModulesTask::run
   */
  public function testRunSkipsWhenNoModulesWithTests(): void {
    $processBuilder = $this->createMock(ProcessBuilder::class);
    $formatter = $this->createMock(ProcessFormatterInterface::class);

    /** @var \PHPUnit\Framework\MockObject\MockObject|PhpUnitDrupalModulesTask $task */
    $task = $this->getMockBuilder(PhpUnitDrupalModulesTask::class)
      ->setConstructorArgs([$processBuilder, $formatter])
      ->onlyMethods(['getConfig', 'getPathsOrResult', 'collectModulesFromPaths', 'splitModulesByTests'])
      ->getMock();

    $config = [];
    foreach ($this->getConfigurations() as $name => $option) {
      $config[$name] = $option['defaults'];
    }

    $taskConfig = $this->createMock(TaskConfigInterface::class);
    $task->method('getConfig')->willReturn($taskConfig);
    $taskConfig->method('getOptions')->willReturn($config);

    $context = $this->createMock(ContextInterface::class);

    $paths = new \ArrayObject(['web/modules/custom/foo/src/Foo.php']);
    $task->method('getPathsOrResult')->willReturn($paths);
    $task->method('collectModulesFromPaths')->willReturn([
      'web/modules/custom/foo' => 'web/modules/custom/foo',
    ]);

    // No modules with tests, one without.
    $task->method('splitModulesByTests')->willReturn([[], ['web/modules/custom/foo']]);

    $result = $task->run($context);
    $this->assertInstanceOf(TaskResultInterface::class, $result);
    $this->assertFalse($result->isPassed());
  }

  /**
   * Ensure run() executes phpunit once per module and stops on first failure.
   *
   * @covers \Wunderio\GrumPHP\Task\PhpUnitDrupalModules\PhpUnitDrupalModulesTask::run
   */
  public function testRunExecutesPhpunitPerModuleAndStopsOnFailure(): void {
    $processBuilder = $this->createMock(ProcessBuilder::class);
    $formatter = $this->createMock(ProcessFormatterInterface::class);

    /** @var \PHPUnit\Framework\MockObject\MockObject|PhpUnitDrupalModulesTask $task */
    $task = $this->getMockBuilder(PhpUnitDrupalModulesTask::class)
      ->setConstructorArgs([$processBuilder, $formatter])
      ->onlyMethods([
        'getConfig',
        'getPathsOrResult',
        'collectModulesFromPaths',
        'splitModulesByTests',
        'buildArguments',
        'getTaskResult',
      ])
      ->getMock();

    $config = [];
    foreach ($this->getConfigurations() as $name => $option) {
      $config[$name] = $option['defaults'];
    }

    $taskConfig = $this->createMock(TaskConfigInterface::class);
    $task->method('getConfig')->willReturn($taskConfig);
    $taskConfig->method('getOptions')->willReturn($config);

    $context = $this->createMock(ContextInterface::class);

    $paths = new \ArrayObject([
      'web/modules/custom/foo/src/Foo.php',
      'web/modules/custom/bar/src/Bar.php',
    ]);
    $task->method('getPathsOrResult')->willReturn($paths);
    $task->method('collectModulesFromPaths')->willReturn([
      'web/modules/custom/foo' => 'web/modules/custom/foo',
      'web/modules/custom/bar' => 'web/modules/custom/bar',
    ]);

    $modulesWithTests = ['web/modules/custom/foo', 'web/modules/custom/bar'];
    $modulesWithoutTests = [];
    $task->method('splitModulesByTests')->willReturn([$modulesWithTests, $modulesWithoutTests]);

    // Expect buildArguments to be called once per module with a single-element
    // array.
    $task->expects($this->exactly(2))
      ->method('buildArguments')
      ->withConsecutive(
        [['web/modules/custom/foo']],
        [['web/modules/custom/bar']]
      )
      ->willReturn($this->createMock(ProcessArgumentsCollection::class));

    $processBuilder->method('buildProcess')
      ->willReturn($this->createMock(Process::class));

    // Simulate first module passing, second failing.
    $passingResult = $this->createConfiguredMock(TaskResultInterface::class, ['isPassed' => TRUE]);
    $failingResult = $this->createConfiguredMock(TaskResultInterface::class, ['isPassed' => FALSE]);

    $task->expects($this->exactly(2))
      ->method('getTaskResult')
      ->willReturnOnConsecutiveCalls($passingResult, $failingResult);

    $result = $task->run($context);
    $this->assertSame($failingResult, $result);
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
