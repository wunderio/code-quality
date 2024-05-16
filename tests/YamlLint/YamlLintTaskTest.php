<?php

/**
 * @file
 * Tests covering YamlLintTask.
 */

declare(strict_types=1);

use GrumPHP\Linter\Yaml\YamlLinter;
use GrumPHP\Task\Config\TaskConfigInterface;
use PHPUnit\Framework\TestCase;
use Wunderio\GrumPHP\Task\YamlLint\YamlLintTask;

/**
 * Class YamlLintTaskTest.
 *
 * Tests covering YamlLint task.
 */
final class YamlLintTaskTest extends TestCase {

  /**
   * Test building arguments.
   *
   * @covers \Wunderio\GrumPHP\Task\YamlLint\YamlLintTask::configureLint
   */
  public function testBuildsProcessArgumentsFromPath(): void {
    $stub = $this->getMockBuilder(YamlLintTask::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['configureLint'])
      ->getMock();
    $lint = $this->createMock(YamlLinter::class);
    $taskConfig = $this->createMock(TaskConfigInterface::class);

    $stub->method('getConfig')->willReturn($taskConfig);
    $taskConfig->method('getOptions')->willReturn($this->getConfigDefaults());
    $lint->expects($this->once())->method('setObjectSupport');
    $lint->expects($this->once())->method('setExceptionOnInvalidType');
    $lint->expects($this->once())->method('setParseCustomTags');
    $lint->expects($this->once())->method('setParseConstants');

    $stub->configureLint($lint);
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
      'object_support' => FALSE,
      'exception_on_invalid_type' => FALSE,
      'parse_constant' => FALSE,
      'parse_custom_tags' => FALSE,
    ];
  }

}
