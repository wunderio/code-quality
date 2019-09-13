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
   * @covers \Wunderio\GrumPHP\Task\PhpCheckSyntax\PhpCheckSyntaxTask::buildArguments
   */
  public function testBuildArguments(): void {
    $grumPHP = $this->getMockBuilder(GrumPHP::class)->disableOriginalConstructor()->getMock();
    $processBuilder = $this->getMockBuilder(ProcessBuilder::class)
      ->disableOriginalConstructor()
      ->getMock();
    $processFormatterInterface = $this->getMockBuilder(ProcessFormatterInterface::class)->getMock();
    $stub = $this->getMockBuilder(PhpCheckSyntaxTask::class)
      ->setConstructorArgs([
        $grumPHP,
        $processBuilder,
        $processFormatterInterface,
      ])
      ->setMethodsExcept(['buildArguments'])
      ->getMockForAbstractClass();
    $arguments = $this->getMockBuilder(ProcessArgumentsCollection::class)->getMock();

    $files = ['test.php', 'file.php', 'file.php'];
    $processBuilder->expects($this->once())
      ->method('createArgumentsForCommand')
      ->willReturn($arguments);
    $arguments->expects($this->exactly(4))
      ->method('add');
    $config = [];
    foreach ($stub->configurableOptions as $name => $option) {
      $config[$name] = $option['defaults'];
    }
    $stub->method('getConfiguration')->willReturn($config);

    $actual = $stub->buildArguments($files);
    $this->assertInstanceOf(ProcessArgumentsCollection::class, $actual);
  }

}
