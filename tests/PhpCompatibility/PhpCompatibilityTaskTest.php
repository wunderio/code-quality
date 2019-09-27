<?php

/**
 * @file
 * Tests covering PhpCompatibilityTask.
 */

declare(strict_types = 1);

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use PHPUnit\Framework\TestCase;
use Wunderio\GrumPHP\Task\PhpCompatibility\PhpCompatibilityTask;

/**
 * Class PhpCompatibilityTaskTest.
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
        $this->createMock(GrumPHP::class),
        $processBuilder,
        $this->createMock(ProcessFormatterInterface::class),
      ])
      ->setMethodsExcept(['buildArguments'])
      ->getMock();
    $arguments = $this->createMock(ProcessArgumentsCollection::class);

    $files = new FilesCollection(['test.php', 'file.php']);
    $processBuilder->method('createArgumentsForCommand')
      ->willReturn($arguments);
    $arguments->expects($this->exactly(4))
      ->method('add');
    $stub->method('getConfiguration')->willReturn([
      'standard' => 'php-compatibility.xm',
      'testVersion' => '7.3',
      'extensions' => ['php'],
      'run_on' => ['.'],
      'ignore_patterns' => ['/vendor/'],
      'parallel' => 10,
    ]);

    $actual = $stub->buildArguments($files);
    $this->assertInstanceOf(ProcessArgumentsCollection::class, $actual);
  }

}
