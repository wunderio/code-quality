<?php

/**
 * @file
 * Tests covering PhpcsTask.
 */

declare(strict_types=1);

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Task\Config\TaskConfigInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Wunderio\GrumPHP\Task\Phpcs\PhpcsTask;

/**
 * Class PhpcsTaskTest.
 *
 * Tests covering Phpcs task.
 */
final class PhpcsTaskTest extends TestCase {

  /**
   * Test building arguments.
   *
   * @covers \Wunderio\GrumPHP\Task\Phpcs\PhpcsTask::buildArguments
   */
  public function testBuildsProcessArguments(): void {
    $processBuilder = $this->createMock(ProcessBuilder::class);
    $stub = $this->getMockBuilder(PhpcsTask::class)->setConstructorArgs([
      $processBuilder,
      $this->createMock(ProcessFormatterInterface::class),
    ])
      ->setMethodsExcept(['buildArguments'])->getMock();
    $arguments = $this->createMock(ProcessArgumentsCollection::class);
    $taskConfig = $this->createMock(TaskConfigInterface::class);

    $files = ['file1.php', 'file2.php', 'file3.php', 'dir1/'];
    $processBuilder->expects($this->once())
      ->method('createArgumentsForCommand')
      ->willReturn($arguments);
    $arguments->expects($this->exactly(6))->method('add');
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
    return $tasks[PhpcsTask::class]['options'];
  }

}
