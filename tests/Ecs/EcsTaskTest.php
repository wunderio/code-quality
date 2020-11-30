<?php

/**
 * @file
 * Tests covering EcsTask.
 */

declare(strict_types = 1);

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Task\Config\TaskConfigInterface;
use PHPUnit\Framework\TestCase;
use Wunderio\GrumPHP\Task\Ecs\EcsTask;

/**
 * Class EcsTaskTest.
 *
 * Tests covering Ecs task.
 */
final class EcsTaskTest extends TestCase {

  /**
   * Test building arguments.
   *
   * @covers \Wunderio\GrumPHP\Task\Ecs\EcsTask::buildArguments
   */
  public function testBuildsProcessArguments(): void {
    $processBuilder = $this->createMock(ProcessBuilder::class);
    $stub = $this->getMockBuilder(EcsTask::class)
      ->setConstructorArgs([
        $processBuilder,
        $this->createMock(ProcessFormatterInterface::class),
      ])
      ->setMethodsExcept(['buildArguments'])
      ->getMock();
    $arguments = $this->createMock(ProcessArgumentsCollection::class);
    $taskConfig = $this->createMock(TaskConfigInterface::class);

    $files = new FilesCollection(['file.php']);
    $processBuilder->expects($this->once())->method('createArgumentsForCommand')->willReturn($arguments);
    $arguments->expects($this->exactly(2))->method('add');
    $config = [
      'extensions' => ['php'],
      'run_on' => ['.'],
      'clear-cache' => FALSE,
      'config' => 'ecs.yml',
      'no-progress-bar' => TRUE,
      'level' => NULL,
    ];
    $stub->method('getConfig')->willReturn($taskConfig);
    $taskConfig->method('getOptions')->willReturn($config);

    $actual = $stub->buildArguments($files);
    $this->assertInstanceOf(ProcessArgumentsCollection::class, $actual);
  }

}
