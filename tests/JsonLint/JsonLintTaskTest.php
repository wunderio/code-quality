<?php

/**
 * @file
 * Tests covering JsonLintTask.
 */

declare(strict_types = 1);

use GrumPHP\Linter\Json\JsonLinter;
use GrumPHP\Task\Config\TaskConfigInterface;
use PHPUnit\Framework\TestCase;
use Wunderio\GrumPHP\Task\JsonLint\JsonLintTask;

/**
 * Class JsonLintTaskTest.
 *
 * Tests covering JsonLint task.
 */
final class JsonLintTaskTest extends TestCase {

  /**
   * Test building arguments.
   *
   * @covers \Wunderio\GrumPHP\Task\JsonLint\JsonLintTask::configureLint
   */
  public function testBuildsProcessArgumentsFromPath(): void {
    $stub = $this->getMockBuilder(JsonLintTask::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['configureLint'])
      ->getMock();
    $lint = $this->createMock(JsonLinter::class);
    $taskConfig = $this->createMock(TaskConfigInterface::class);

    $stub->method('getConfig')->willReturn($taskConfig);
    $taskConfig->method('getOptions')->willReturn($this->getConfigDefaults());
    $lint->expects($this->once())->method('setDetectKeyConflicts');

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
      'detect_key_conflicts' => FALSE,
    ];
  }

}
