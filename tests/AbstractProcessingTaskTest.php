<?php

/**
 * @file
 * Tests covering AbstractProcessingTask.
 */

declare(strict_types = 1);

use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Wunderio\GrumPHP\Task\AbstractProcessingTask;

/**
 * Class AbstractProcessingTaskTest.
 *
 * Tests covering AbstractProcessingTask class.
 */
final class AbstractProcessingTaskTest extends TestCase {

  /**
   * Test class constructor.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractProcessingTask::__construct
   */
  public function testSetsConfigurationFromYaml(): void {
    $customTask = new CustomTestTask(
        $this->createMock(ProcessBuilder::class),
        $this->createMock(ProcessFormatterInterface::class)
    );
    $this->assertEquals('custom_test', $customTask->name);
    $this->assertCount(3, $customTask->configurableOptions);
  }

  /**
   * Test get unsuccessful task result.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractProcessingTask::getTaskResult
   */
  public function testFailsTaskIfProcessUnsuccessful(): void {
    $processFormatterInterface = $this->createMock(ProcessFormatterInterface::class);
    $processFormatterInterface->expects($this->once())->method('format')->willReturn('Formatted Output.');
    $stub = $this->getMockBuilder(AbstractProcessingTask::class)
      ->setConstructorArgs([
        $this->createMock(ProcessBuilder::class),
        $processFormatterInterface,
      ])
      ->setMethodsExcept(['getTaskResult'])
      ->getMockForAbstractClass();
    $process = $this->createMock(Process::class);

    $process->expects($this->once())->method('isSuccessful')->willReturn(FALSE);

    $result = $stub->getTaskResult($process, $this->createMock(ContextInterface::class));
    $this->assertFalse($result->isPassed());
  }

  /**
   * Test get successful task result.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractProcessingTask::getTaskResult
   */
  public function testPassesTaskIfProcessSuccessful(): void {
    $stub = $this->getMockBuilder(AbstractProcessingTask::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['getTaskResult'])
      ->getMockForAbstractClass();

    $process = $this->createMock(Process::class);
    $process->expects($this->once())->method('isSuccessful')->willReturn(TRUE);

    $result = $stub->getTaskResult($process, $this->createMock(ContextInterface::class));
    $this->assertTrue($result->isPassed());
  }

}

/**
 * Class CustomTestTask.
 *
 * Extender class for test cases.
 */
class CustomTestTask extends AbstractProcessingTask {

  /**
   * {@inheritdoc}
   */
  public function run(ContextInterface $context): TaskResultInterface {}

}
