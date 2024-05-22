<?php

/**
 * @file
 * Tests covering PhpCompatibilityTask.
 */

declare(strict_types=1);

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Task\Config\TaskConfigInterface;
use PHPUnit\Framework\TestCase;
use Wunderio\GrumPHP\Task\PhpCompatibility\PhpCompatibilityTask;

/**
 * Class PhpCompatibilityTaskTest.
 *
 * Tests covering PhpCompatibility task.
 */
final class PhpCompatibilityTaskTest extends TestCase {

  /**
   * Test building arguments.
   *
   * @covers \Wunderio\GrumPHP\Task\PhpCompatibility\PhpCompatibilityTask::buildArguments
   */
  public function testBuildsProcessArguments(): void {
    $processBuilder = $this->createMock(ProcessBuilder::class);
    $stub = $this->getMockBuilder(PhpCompatibilityTask::class)
      ->setConstructorArgs([
        $processBuilder,
        $this->createMock(ProcessFormatterInterface::class),
      ])
      ->setMethodsExcept(['buildArguments'])
      ->getMock();
    $arguments = $this->createMock(ProcessArgumentsCollection::class);
    $taskConfig = $this->createMock(TaskConfigInterface::class);

    $files = new FilesCollection(['test.php', 'file.php']);
    $processBuilder->method('createArgumentsForCommand')
      ->willReturn($arguments);
    $arguments->expects($this->exactly(5))
      ->method('add');
    $stub->method('getConfig')->willReturn($taskConfig);
    $taskConfig->method('getOptions')->willReturn([
      'standard' => 'php-compatibility.xm',
      'testVersion' => '8.1',
      'extensions' => ['php'],
      'run_on' => ['.'],
      'ignore_patterns' => ['/vendor/'],
      'parallel' => 10,
    ]);

    $actual = $stub->buildArguments($files);
    $this->assertInstanceOf(ProcessArgumentsCollection::class, $actual);
  }

}
