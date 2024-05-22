<?php

/**
 * @file
 * Tests covering AbstractMultiPathProcessingTask.
 */

declare(strict_types=1);

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Wunderio\GrumPHP\Task\AbstractMultiPathProcessingTask;

/**
 * Class AbstractMultiPathProcessingTaskTest.
 *
 * Tests covering AbstractMultiPathProcessingTask class.
 */
final class AbstractMultiPathProcessingTaskTest extends TestCase {

  /**
   * Test run in scenario where no files or directories found.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractMultiPathProcessingTask::run
   */
  public function testSkipsTaskIfNoFilesFound(): void {
    $processBuilder = $this->getMockBuilder(ProcessBuilder::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['buildProcess'])
      ->getMock();
    $processFormatterInterface = $this->createMock(ProcessFormatterInterface::class);
    $stub = $this->getMockBuilder(AbstractMultiPathProcessingTask::class)
      ->setConstructorArgs([
        $processBuilder,
        $processFormatterInterface,
      ])
      ->setMethodsExcept(['run'])
      ->getMockForAbstractClass();
    $context = $this->createMock(RunContext::class);

    $stub->expects($this->once())
      ->method('getPathsOrResult')
      ->willReturn(TaskResult::createSkipped($this->createMock(TaskInterface::class), $context));
    $processBuilder->expects($this->never())->method('buildProcess');

    $actual = $stub->run($context);
    $this->assertInstanceOf(TaskResultInterface::class, $actual);
    $this->assertEquals(TaskResult::SKIPPED, $actual->getResultCode());
  }

  /**
   * Test run in scenario with files or directories found.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractMultiPathProcessingTask::run
   */
  public function testReturnsTaskResultIfFileFoundAndProcessUnsuccessful(): void {
    $processBuilder = $this->getMockBuilder(ProcessBuilder::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['buildProcess'])
      ->getMock();
    $stub = $this->getMockBuilder(AbstractMultiPathProcessingTask::class)
      ->setConstructorArgs([
        $processBuilder,
        $this->createMock(ProcessFormatterInterface::class),
      ])
      ->setMethodsExcept(['run'])
      ->getMockForAbstractClass();
    $message = 'Test message...';

    $stub->expects($this->once())->method('getPathsOrResult')->willReturn(['file.php']);
    $process = $this->createMock(Process::class);
    $process->expects($this->once())->method('run');
    $stub->expects($this->once())
      ->method('buildArguments')
      ->willReturn($this->createMock(ProcessArgumentsCollection::class));
    $processBuilder->expects($this->once())->method('buildProcess')->willReturn($process);
    $stub->expects($this->once())
      ->method('getTaskResult')
      ->willReturn(TaskResult::createFailed($stub, $this->createMock(ContextInterface::class), $message));

    $actual = $stub->run($this->createMock(RunContext::class));
    $this->assertInstanceOf(TaskResultInterface::class, $actual);
    $this->assertFalse($actual->isPassed());
    $this->assertEquals($message, $actual->getMessage());
  }

}
