<?php

/**
 * @file
 * Tests covering IndividualContextFileExternalTaskBase.
 */

declare(strict_types = 1);

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use PHPUnit\Framework\TestCase;
use Wunderio\GrumPHP\Task\CheckFilePermissions\CheckFilePermissionsTask;

/**
 * Class IndividualContextFileExternalTaskBaseTest.
 *
 * @covers \Wunderio\GrumPHP\Task\CheckFilePermissions\CheckFilePermissionsTask
 */
final class CheckFilePermissionsTaskTest extends TestCase {

  /**
   * Test run in scenario where no files or directories found.
   */
  public function testBuildArguments(): void {
    $grumPHP = $this->getMockBuilder(GrumPHP::class)->disableOriginalConstructor()->getMock();
    $processBuilder = $this->getMockBuilder(ProcessBuilder::class)
      ->disableOriginalConstructor()
      ->getMock();
    $processFormatterInterface = $this->getMockBuilder(ProcessFormatterInterface::class)->getMock();
    $stub = $this->getMockBuilder(CheckFilePermissionsTask::class)
      ->setConstructorArgs([
        $grumPHP,
        $processBuilder,
        $processFormatterInterface,
      ])
      ->setMethodsExcept(['buildArguments'])
      ->getMockForAbstractClass();
    $arguments = $this->getMockBuilder(ProcessArgumentsCollection::class)->getMock();

    $files = new FilesCollection(['file.php']);
    $processBuilder->expects($this->once())
      ->method('createArgumentsForCommand')
      ->willReturn($arguments);
    $arguments->expects($this->once())
      ->method('addFiles')
      ->with($files);

    $actual = $stub->buildArguments($files);
    $this->assertInstanceOf(ProcessArgumentsCollection::class, $actual);
  }

}