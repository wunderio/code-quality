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
    $grumPHP = $this->getMockBuilder(GrumPHP::class)->disableOriginalConstructor()->getMock();
    $processBuilder = $this->getMockBuilder(ProcessBuilder::class)
      ->disableOriginalConstructor()
      ->getMock();
    $processFormatterInterface = $this->getMockBuilder(ProcessFormatterInterface::class)->getMock();
    $stub = $this->getMockBuilder(PhpCompatibilityTask::class)
      ->setConstructorArgs([
        $grumPHP,
        $processBuilder,
        $processFormatterInterface,
      ])
      ->setMethodsExcept(['buildArguments'])
      ->getMock();
    $arguments = $this->getMockBuilder(ProcessArgumentsCollection::class)->getMock();

    $files = new FilesCollection(['test.php', 'file.php']);
    $processBuilder->method('createArgumentsForCommand')
      ->willReturn($arguments);
    $stub->expects($this->once())
      ->method('addArgumentsFromConfig')
      ->willReturn($arguments);
    $arguments->expects($this->exactly(3))
      ->method('add');
    $config = [];
    foreach ($stub->configurableOptions as $name => $option) {
      $config[$name] = $option['defaults'];
    }
    $stub->method('getConfiguration')->willReturn($config);

    $actual = $stub->buildArguments($files);
    $this->assertInstanceOf(ProcessArgumentsCollection::class, $actual);
  }

  /**
   * Test adding arguments from configuration.
   *
   * @covers \Wunderio\GrumPHP\Task\PhpCompatibility\PhpCompatibilityTask::addArgumentsFromConfig
   */
  public function testAddsArgumentsFromConfiguration(): void {
    $grumPHP = $this->getMockBuilder(GrumPHP::class)->disableOriginalConstructor()->getMock();
    $processBuilder = $this->getMockBuilder(ProcessBuilder::class)
      ->disableOriginalConstructor()
      ->getMock();
    $processFormatterInterface = $this->getMockBuilder(ProcessFormatterInterface::class)->getMock();
    $stub = $this->getMockBuilder(PhpCompatibilityTask::class)
      ->setConstructorArgs([
        $grumPHP,
        $processBuilder,
        $processFormatterInterface,
      ])
      ->setMethodsExcept(['addArgumentsFromConfig'])
      ->getMock();
    $arguments = $this->getMockBuilder(ProcessArgumentsCollection::class)->getMock();
    $config = [];
    foreach ($stub->configurableOptions as $name => $option) {
      $config[$name] = $option['defaults'];
    }

    $arguments->expects($this->once())
      ->method('addOptionalCommaSeparatedArgument');
    $arguments->expects($this->once())
      ->method('addSeparatedArgumentArray');

    $actual = $stub->addArgumentsFromConfig($arguments, $config);
    $this->assertInstanceOf(ProcessArgumentsCollection::class, $actual);
  }

}
