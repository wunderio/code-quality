<?php

/**
 * @file
 * Tests covering PhpcsTask.
 */

declare(strict_types = 1);

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use PHPUnit\Framework\TestCase;
use Wunderio\GrumPHP\Task\Phpcs\PhpcsTask;

/**
 * Class PhpCompatibilityTaskTest.
 */
final class PhpcsTaskTest extends TestCase {

  /**
   * Test building arguments.
   *
   * @covers \Wunderio\GrumPHP\Task\Phpcs\PhpcsTask::buildArguments
   */
  public function testBuildsProcessArguments(): void {
    $grumPHP = $this->getMockBuilder(GrumPHP::class)->disableOriginalConstructor()->getMock();
    $processBuilder = $this->getMockBuilder(ProcessBuilder::class)
      ->disableOriginalConstructor()
      ->getMock();
    $processFormatterInterface = $this->getMockBuilder(ProcessFormatterInterface::class)->getMock();
    $stub = $this->getMockBuilder(PhpcsTask::class)->setConstructorArgs([
      $grumPHP,
      $processBuilder,
      $processFormatterInterface,
    ])
      ->setMethodsExcept(['buildArguments'])
      ->getMock();
    $arguments = $this->getMockBuilder(ProcessArgumentsCollection::class)->getMock();

    $files = ['file1.php', 'file2.php', 'file3.php', 'dir1/'];
    $processBuilder->expects($this->once())
      ->method('createArgumentsForCommand')
      ->willReturn($arguments);
    $stub->method('addArgumentsFromConfig')
      ->willReturn($arguments);
    $arguments->expects($this->exactly(5))
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
   * @covers \Wunderio\GrumPHP\Task\Phpcs\PhpcsTask::addArgumentsFromConfig
   */
  public function testAddsArgumentsFromConfiguration(): void {
    $grumPHP = $this->getMockBuilder(GrumPHP::class)->disableOriginalConstructor()->getMock();
    $processBuilder = $this->getMockBuilder(ProcessBuilder::class)
      ->disableOriginalConstructor()
      ->getMock();
    $processFormatterInterface = $this->getMockBuilder(ProcessFormatterInterface::class)->getMock();
    $stub = $this->getMockBuilder(PhpcsTask::class)
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

    $arguments->expects($this->atLeastOnce())
      ->method('addOptionalCommaSeparatedArgument');
    $arguments->expects($this->atLeastOnce())
      ->method('addOptionalArgument');
    $arguments->expects($this->atLeastOnce())
      ->method('addOptionalIntegerArgument');

    $actual = $stub->addArgumentsFromConfig($arguments, $config);
    $this->assertInstanceOf(ProcessArgumentsCollection::class, $actual);
  }

}
