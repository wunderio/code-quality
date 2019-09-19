<?php

/**
 * @file
 * Tests covering PhpCheckSyntaxTask.
 */

declare(strict_types = 1);

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use PHPUnit\Framework\TestCase;
use Wunderio\GrumPHP\Task\PhpCheckSyntax\PhpCheckSyntaxTask;

/**
 * Class PhpCompatibilityTaskTest.
 */
final class PhpCheckSyntaxTaskTest extends TestCase {

  /**
   * Test building arguments.
   *
   * @covers \Wunderio\GrumPHP\Task\PhpCheckSyntax\PhpCheckSyntaxTask::buildArgumentsFromPath
   */
  public function testBuildsProcessArgumentsFromPath(): void {
    $processBuilder = $this->createMock(ProcessBuilder::class);
    $stub = $this->getMockBuilder(PhpCheckSyntaxTask::class)
      ->setConstructorArgs([
        $this->createMock(GrumPHP::class),
        $processBuilder,
        $this->createMock(ProcessFormatterInterface::class),
      ])
      ->setMethodsExcept(['buildArgumentsFromPath'])
      ->getMock();
    $arguments = $this->createMock(ProcessArgumentsCollection::class);

    $path = 'test.php';
    $processBuilder->expects($this->once())
      ->method('createArgumentsForCommand')
      ->willReturn($arguments);
    $arguments->expects($this->exactly(2))
      ->method('add');
    $stub->method('getConfiguration')->willReturn($this->getConfigDefaults());

    $actual = $stub->buildArgumentsFromPath($path);
    $this->assertInstanceOf(ProcessArgumentsCollection::class, $actual);
  }

  /**
   * Default configuration.
   *
   * @return array
   *   Configuration.
   */
  protected function getConfigDefaults(): array {
    return [
      'ignore_patterns' => ['/vendor/'],
      'extensions' => ['php'],
      'run_on' => ['.'],
    ];
  }

}
