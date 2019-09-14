<?php

/**
 * @file
 * Tests covering EcsTask.
 */

declare(strict_types = 1);

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use PHPUnit\Framework\TestCase;
use Wunderio\GrumPHP\Task\Ecs\EcsTask;

/**
 * Class EcsTaskTest.
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
        $this->createMock(GrumPHP::class),
        $processBuilder,
        $this->createMock(ProcessFormatterInterface::class),
      ])
      ->setMethodsExcept(['buildArguments'])
      ->getMock();
    $arguments = $this->createMock(ProcessArgumentsCollection::class);

    $files = new FilesCollection(['file.php']);
    $processBuilder->expects($this->once())
      ->method('createArgumentsForCommand')
      ->willReturn($arguments);
    $arguments->expects($this->exactly(2))
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
