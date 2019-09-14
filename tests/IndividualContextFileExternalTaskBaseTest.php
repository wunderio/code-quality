<?php

/**
 * @file
 * Tests covering IndividualContextFileExternalTaskBase.
 */

declare(strict_types = 1);

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Wunderio\GrumPHP\Task\IndividualContextFileExternalTaskBase;

/**
 * Class IndividualContextFileExternalTaskBaseTest.
 */
final class IndividualContextFileExternalTaskBaseTest extends TestCase {

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
   * IndividualContextFileExternalTaskBase object mock.
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
    $this->grumPHP = $this->getMockBuilder(GrumPHP::class)->disableOriginalConstructor()->getMock();
    $this->processBuilder = $this->getMockBuilder(ProcessBuilder::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['buildProcess'])
      ->getMock();
    $this->processFormatterInterface = $this->getMockBuilder(ProcessFormatterInterface::class)->getMock();
    $this->stub = $this->getMockBuilder(IndividualContextFileExternalTaskBase::class)
      ->setConstructorArgs([
        $this->grumPHP,
        $this->processBuilder,
        $this->processFormatterInterface,
      ])
      ->onlyMethods([
        'getFilesOrResult',
        'buildArguments',
        'run',
        'getTaskResult',
      ])
      ->setMethodsExcept(['run'])
      ->getMockForAbstractClass();
    $this->process = $this->getMockBuilder(Process::class)
      ->disableOriginalConstructor()
      ->getMock();
  }

  /**
   * Test run in scenario where no files or directories found.
   *
   * @covers \Wunderio\GrumPHP\Task\IndividualContextFileExternalTaskBase::run
   */
  public function testSkipsTaskIfNoFilesFound(): void {
    $this->stub->expects($this->once())
      ->method('getFilesOrResult')
      ->willReturn(TaskResult::createSkipped(
        $this->createMock(TaskInterface::class),
        $this->createMock(ContextInterface::class)
      ));

    $this->processBuilder->expects($this->never())
      ->method('buildProcess');

    $context = $this->getMockBuilder(RunContext::class)
      ->disableOriginalConstructor()
      ->getMock();

    $actual = $this->stub->run($context);
    $this->assertInstanceOf(TaskResultInterface::class, $actual);
    $this->assertEquals(TaskResult::SKIPPED, $actual->getResultCode());
  }

  /**
   * Test run in scenario with one files found and process successful.
   *
   * @covers \Wunderio\GrumPHP\Task\IndividualContextFileExternalTaskBase::run
   */
  public function testPassesTaskIfFileFoundAndProcessSuccessful(): void {
    $this->stub->expects($this->once())
      ->method('getFilesOrResult')
      ->willReturn(['file.php']);
    $this->stub->expects($this->once())
      ->method('buildArguments')
      ->willReturn($this->getMockBuilder(ProcessArgumentsCollection::class)->getMock());
    $this->processBuilder->expects($this->once())
      ->method('buildProcess')
      ->willReturn($this->process);
    $this->process->expects($this->once())
      ->method('run');
    $this->process->expects($this->once())
      ->method('isSuccessful')
      ->willReturn(TRUE);

    $context = $this->getMockBuilder(RunContext::class)
      ->disableOriginalConstructor()
      ->getMock();

    $actual = $this->stub->run($context);
    $this->assertInstanceOf(TaskResultInterface::class, $actual);
    $this->assertTrue($actual->isPassed());
  }

  /**
   * Test run in scenario with multiple found and process unsuccessful.
   *
   * @covers \Wunderio\GrumPHP\Task\IndividualContextFileExternalTaskBase::run
   */
  public function testFailsTaskIfMultipleFilesFoundButProcessUnsuccessful(): void {
    $this->stub->expects($this->once())
      ->method('getFilesOrResult')
      ->willReturn(['file.php', 'directory/']);
    $this->stub->expects($this->exactly(2))
      ->method('buildArguments')
      ->willReturn($this->getMockBuilder(ProcessArgumentsCollection::class)->getMock());
    $this->processBuilder->expects($this->exactly(2))
      ->method('buildProcess')
      ->willReturn($this->process);
    $this->process->expects($this->exactly(2))
      ->method('run');
    $this->process->expects($this->exactly(2))
      ->method('isSuccessful')
      ->willReturn(FALSE);
    $this->processFormatterInterface->expects($this->exactly(2))
      ->method('format')
      ->willReturn('Error');

    $context = $this->getMockBuilder(RunContext::class)
      ->disableOriginalConstructor()
      ->getMock();

    $actual = $this->stub->run($context);
    $this->assertInstanceOf(TaskResultInterface::class, $actual);
    $this->assertFalse($actual->isPassed());
  }

}
