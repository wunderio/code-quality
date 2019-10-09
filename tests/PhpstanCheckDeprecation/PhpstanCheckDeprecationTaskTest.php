<?php

/**
 * @file
 * Tests covering PhpstanCheckDeprecationTask.
 */

declare(strict_types = 1);

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Wunderio\GrumPHP\Task\PhpstanCheckDeprecation\PhpstanCheckDeprecationTask;

/**
 * Class PhpstanTaskTest.
 */
final class PhpstanCheckDeprecationTaskTest extends TestCase {

  /**
   * Test building arguments.
   *
   * @covers \Wunderio\GrumPHP\Task\Phpstan\PhpstanDrupalCheckTask::buildArguments
   */
  public function testBuildsProcessArguments(): void {
    $processBuilder = $this->createMock(ProcessBuilder::class);
    $stub = $this->getMockBuilder(PhpstanCheckDeprecationTask::class)->setConstructorArgs([
      $this->createMock(GrumPHP::class),
      $processBuilder,
      $this->createMock(ProcessFormatterInterface::class),
    ])
      ->setMethodsExcept(['buildArguments'])->getMock();
    $arguments = $this->createMock(ProcessArgumentsCollection::class);

    $files = ['file1.php', 'file2.php', 'dir1/'];
    $processBuilder->expects($this->once())
      ->method('createArgumentsForCommand')
      ->willReturn($arguments);

    $arguments->expects($this->exactly(7))->method('add');
    $config = [];
    foreach ($this->getConfigurations() as $name => $option) {
      $config[$name] = $option['defaults'];
    }
    $stub->expects($this->once())->method('getConfiguration')->willReturn($config);

    $actual = $stub->buildArguments($files);
    $this->assertInstanceOf(ProcessArgumentsCollection::class, $actual);
  }

  /**
   * Gets task configurations.
   *
   * @return array
   *   Array of options.
   */
  protected function getConfigurations(): array {
    $tasks = Yaml::parseFile(__DIR__ . '/../../src/Task/tasks.yml');
    return $tasks[PhpstanCheckDeprecationTask::class]['options'];
  }

}
