<?php

/**
 * @file
 * Tests covering PhpstanCheckDeprecationTask.
 */

declare(strict_types=1);

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Task\Config\TaskConfigInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Wunderio\GrumPHP\Task\PhpStan\PhpStanTask;

/**
 * Class PhpStanTaskTest.
 *
 * Tests covering PhpStan task.
 */
final class PhpStanTaskTest extends TestCase {

  /**
   * Test building arguments.
   *
   * @covers \Wunderio\GrumPHP\Task\PhpStan\PhpStanTask::buildArguments
   */
  public function testBuildsProcessArguments(): void {
    $processBuilder = $this->createMock(ProcessBuilder::class);
    $stub = $this->getMockBuilder(PhpStanTask::class)->setConstructorArgs([
      $processBuilder,
      $this->createMock(ProcessFormatterInterface::class),
    ])
      ->setMethodsExcept(['buildArguments'])->getMock();
    $arguments = $this->createMock(ProcessArgumentsCollection::class);
    $taskConfig = $this->createMock(TaskConfigInterface::class);

    $files = ['file1.php', 'file2.php', 'dir1/'];
    $processBuilder->expects($this->once())
      ->method('createArgumentsForCommand')
      ->willReturn($arguments);

    $arguments->expects($this->exactly(7))->method('add');
    $config = [];
    foreach ($this->getConfigurations() as $name => $option) {
      $config[$name] = $option['defaults'];
    }
    $stub->expects($this->once())->method('getConfig')->willReturn($taskConfig);
    $taskConfig->method('getOptions')->willReturn($config);

    $actual = $stub->buildArguments($files);
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
    return $tasks[PhpStanTask::class]['options'];
  }

}
