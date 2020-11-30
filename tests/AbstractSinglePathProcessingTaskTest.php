<?php

/**
 * @file
 * Tests covering AbstractSinglePathProcessingTask.
 */

declare(strict_types = 1);

use GrumPHP\Collection\FilesCollection;
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
use Wunderio\GrumPHP\Task\AbstractSinglePathProcessingTask;

/**
 * Class AbstractSinglePathProcessingTaskTest.
 */
final class AbstractSinglePathProcessingTaskTest extends TestCase {

  /**
   * GrumPHP object mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject
   */
  protected $grumPHP;

  /**
   * ProcessBuilder object mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject
   */
  protected $processBuilder;

  /**
   * ProcessFormatterInterface object mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject
   */
  protected $processFormatterInterface;

  /**
   * AbstractPerPathExternalTask object mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject
   */
  protected $stub;

  /**
   * Process object mock.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject
   */
  protected $process;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->processBuilder = $this->createMock(ProcessBuilder::class);
    $this->processFormatterInterface = $this->createMock(ProcessFormatterInterface::class);
    $this->stub = $this->getMockBuilder(AbstractSinglePathProcessingTask::class)
      ->setConstructorArgs([
        $this->processBuilder,
        $this->processFormatterInterface,
      ])
      ->setMethodsExcept(['run'])
      ->getMockForAbstractClass();
    $this->process = $this->createMock(Process::class);
  }

  /**
   * Test run in scenario where no paths or directories found.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractSinglePathProcessingTask::run
   */
  public function testSkipsTaskIfNoFilesFound(): void {
    $this->stub->expects($this->once())
      ->method('getPathsOrResult')
      ->willReturn(TaskResult::createSkipped(
        $this->createMock(TaskInterface::class),
        $this->createMock(ContextInterface::class)
      ));

    $this->stub->expects($this->never())->method('runInParallel');

    $actual = $this->stub->run($this->createMock(RunContext::class));
    $this->assertInstanceOf(TaskResultInterface::class, $actual);
    $this->assertEquals(TaskResult::SKIPPED, $actual->getResultCode());
  }

  /**
   * Test run in scenario with one path found and process successful.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractSinglePathProcessingTask::run
   */
  public function testPassesTaskIfFileFoundAndProcessSuccessful(): void {
    $this->stub->expects($this->once())
      ->method('getPathsOrResult')
      ->willReturn([__DIR__]);
    $this->stub->expects($this->once())
      ->method('runInParallel')
      ->willReturn('');

    $actual = $this->stub->run($this->createMock(RunContext::class));
    $this->assertInstanceOf(TaskResultInterface::class, $actual);
    $this->assertTrue($actual->isPassed());
  }

  /**
   * Test run in scenario with one file found and process successful.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractSinglePathProcessingTask::run
   */
  public function testFailsTaskIfMultipleFilesFoundButProcessUnsuccessful(): void {
    $this->stub->expects($this->once())
      ->method('getPathsOrResult')
      ->willReturn(new FilesCollection([__FILE__]));
    $this->stub->expects($this->once())
      ->method('runInParallel')
      ->willReturn('Error');

    $actual = $this->stub->run($this->createMock(RunContext::class));
    $this->assertInstanceOf(TaskResultInterface::class, $actual);
    $this->assertFalse($actual->isPassed());
  }

  /**
   * Test running 3 processes in parallel.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractSinglePathProcessingTask::runInParallel
   */
  public function testRunsPathListInParallel(): void {
    $task = $this->getMockBuilder(AbstractSinglePathProcessingTask::class)
      ->setConstructorArgs([
        $this->processBuilder,
        $this->processFormatterInterface,
      ])
      ->setMethodsExcept(['runInParallel'])
      ->getMockForAbstractClass();
    $output_text = 'Test error output';
    $task->expects($this->exactly(3))
      ->method('buildArgumentsFromPath')
      ->willReturn(new ProcessArgumentsCollection());
    $this->processBuilder->expects($this->exactly(3))
      ->method('buildProcess')
      ->willReturn($this->process);
    $this->process->expects($this->exactly(3))->method('start');
    $task->expects($this->once())->method('handleProcesses')->willReturnCallback(
      static function (&$runningProcesses, &$output) use ($output_text) {
        $runningProcesses = [];
        $output = $output_text;
      }
    );

    $actual = $task->runInParallel(['.', 'src/', 'test/']);
    $this->assertEquals($output_text, $actual);
  }

  /**
   * Test parallel process handling.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractSinglePathProcessingTask::handleProcesses
   */
  public function testHandlesParallelProcess(): void {
    $task = $this->getMockBuilder(AbstractSinglePathProcessingTask::class)
      ->setConstructorArgs([
        $this->processBuilder,
        $this->processFormatterInterface,
      ])
      ->setMethodsExcept(['handleProcesses'])
      ->getMockForAbstractClass();
    $message = 'Test error';
    $output = '';
    $processes = [
      $this->process,
      $this->process,
    ];

    $this->process
      ->expects($this->exactly(2))
      ->method('isRunning')
      ->willReturnOnConsecutiveCalls([TRUE, FALSE]);
    $this->process
      ->expects($this->once())
      ->method('isSuccessful')
      ->willReturn(FALSE);
    $this->processFormatterInterface
      ->expects($this->once())
      ->method('format')
      ->willReturn($message);

    $task->handleProcesses($processes, $output);
    $this->assertEquals($message . PHP_EOL, $output);
  }

}
