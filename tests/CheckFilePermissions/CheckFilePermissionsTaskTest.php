<?php

/**
 * @file
 * Tests covering CheckFilePermissionsTask.
 */

declare(strict_types = 1);

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use PHPUnit\Framework\TestCase;
use Wunderio\GrumPHP\Task\CheckFilePermissions\CheckFilePermissionsTask;

/**
 * Class CheckFilePermissionsTaskTest.
 *
 * Tests covering CheckFilePermissions task.
 */
final class CheckFilePermissionsTaskTest extends TestCase {

  /**
   * Test building arguments.
   *
   * @covers \Wunderio\GrumPHP\Task\CheckFilePermissions\CheckFilePermissionsTask::buildArguments
   */
  public function testBuildsProcessArguments(): void {
    $processBuilder = $this->createMock(ProcessBuilder::class);
    $stub = $this->getMockBuilder(CheckFilePermissionsTask::class)
      ->setConstructorArgs([
        $processBuilder,
        $this->createMock(ProcessFormatterInterface::class),
      ])
      ->setMethodsExcept(['buildArguments'])
      ->getMock();
    $arguments = $this->createMock(ProcessArgumentsCollection::class);

    $files = new FilesCollection(['file.php']);
    $processBuilder->expects($this->once())->method('createArgumentsForCommand')->willReturn($arguments);
    $arguments->expects($this->once())->method('addFiles')->with($files);

    $actual = $stub->buildArguments($files);
    $this->assertInstanceOf(ProcessArgumentsCollection::class, $actual);
  }

}
