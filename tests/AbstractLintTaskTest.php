<?php

/**
 * @file
 * Tests covering AbstractLintTask.
 */

declare(strict_types=1);

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\LintErrorsCollection;
use GrumPHP\Linter\LinterInterface;
use GrumPHP\Linter\LintError;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Config\TaskConfigInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use PHPUnit\Framework\TestCase;
use Wunderio\GrumPHP\Task\AbstractLintTask;

/**
 * Class AbstractLintTaskTest.
 *
 * Tests covering AbstractLintTask class.
 */
final class AbstractLintTaskTest extends TestCase {

  /**
   * Test class constructor.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractLintTask::__construct
   */
  public function testSetsConfigurationFromYaml(): void {
    $customTask = new CustomLintTestTask(
        $this->createMock(LinterInterface::class)
    );
    $this->assertEquals('custom_lint_test', $customTask->name);
    $this->assertCount(3, $customTask->configurableOptions);
  }

  /**
   * Test get successful task result.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractLintTask::run
   */
  public function testPassesTaskIfProcessSuccessful(): void {
    $stub = $this->getMockBuilder(AbstractLintTask::class)
      ->setConstructorArgs([
        $this->createMock(LinterInterface::class),
      ])
      ->setMethodsExcept(['run'])
      ->getMockForAbstractClass();
    $context = $this->createMock(ContextInterface::class);
    $files = new FilesCollection(['file.php']);
    $stub->expects($this->once())->method('getPathsOrResult')->willReturn($files);
    $stub->expects($this->once())->method('configureLint');
    $stub->expects($this->once())
      ->method('runLint')
      ->with($context, $files)
      ->willReturn(TaskResult::createPassed($this->createMock(TaskInterface::class), $context));

    $result = $stub->run($context);
    $this->assertTrue($result->isPassed());
  }

  /**
   * Test get unsuccessful task result if no files found.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractLintTask::run
   */
  public function testSkipsIfNoFilesFound(): void {
    $stub = $this->getMockBuilder(AbstractLintTask::class)
      ->setConstructorArgs([
        $this->createMock(LinterInterface::class),
      ])
      ->setMethodsExcept(['run'])
      ->getMockForAbstractClass();
    $context = $this->createMock(ContextInterface::class);
    $stub->expects($this->once())
      ->method('getPathsOrResult')
      ->willReturn(TaskResult::createSkipped($this->createMock(TaskInterface::class), $context)
      );
    $stub->expects($this->never())->method('configureLint');
    $stub->expects($this->never())->method('runLint');

    $result = $stub->run($context);
    $this->assertEquals(TaskResult::SKIPPED, $result->getResultCode());
  }

  /**
   * Test get failed task result on Exception.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractLintTask::runLint
   */
  public function testReturnsFailedResultOnException(): void {
    $lint = $this->createMock(LinterInterface::class);
    $stub = $this->getMockBuilder(AbstractLintTask::class)
      ->setConstructorArgs([
        $lint,
      ])
      ->setMethodsExcept(['runLint'])
      ->getMockForAbstractClass();
    $files = new FilesCollection([]);

    $lint->method('isInstalled')->willReturn(FALSE);

    $result = $stub->runLint($this->createMock(ContextInterface::class), $files);
    $this->assertEquals(TaskResult::FAILED, $result->getResultCode());
  }

  /**
   * Test get failed task result on lint error.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractLintTask::runLint
   */
  public function testReturnsFailedResultOnLintError(): void {
    $lint = $this->createMock(LinterInterface::class);
    $stub = $this->getMockBuilder(AbstractLintTask::class)
      ->setConstructorArgs([
        $lint,
      ])
      ->setMethodsExcept(['runLint'])
      ->getMockForAbstractClass();
    $taskConfig = $this->createMock(TaskConfigInterface::class);

    $files = new FilesCollection([new SplFileInfo(__FILE__)]);
    $lintError = new LintErrorsCollection([
      new LintError('Error', 'TestError', __FILE__, 1),
    ]);

    $stub->method('getConfig')->willReturn($taskConfig);
    $taskConfig->method('getOptions')->willReturn(['ignore_patterns' => []]);
    $lint->method('isInstalled')->willReturn(TRUE);
    $lint->expects($this->once())->method('lint')->willReturn($lintError);

    $result = $stub->runLint($this->createMock(ContextInterface::class), $files);
    $this->assertEquals(TaskResult::FAILED, $result->getResultCode());
  }

  /**
   * Test get success task result if linter has no errors.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractLintTask::runLint
   */
  public function testReturnsSuccessResultOnSuccess(): void {
    $lint = $this->createMock(LinterInterface::class);
    $stub = $this->getMockBuilder(AbstractLintTask::class)
      ->setConstructorArgs([
        $lint,
      ])
      ->setMethodsExcept(['runLint'])
      ->getMockForAbstractClass();
    $taskConfig = $this->createMock(TaskConfigInterface::class);

    $files = new FilesCollection([new SplFileInfo(__FILE__)]);
    $lintError = new LintErrorsCollection([]);

    $stub->method('getConfig')->willReturn($taskConfig);
    $taskConfig->method('getOptions')->willReturn(['ignore_patterns' => []]);
    $lint->method('isInstalled')->willReturn(TRUE);
    $lint->expects($this->once())->method('lint')->willReturn($lintError);

    $result = $stub->runLint($this->createMock(ContextInterface::class), $files);
    $this->assertEquals(TaskResult::PASSED, $result->getResultCode());
  }

}

/**
 * Class CustomLintTestTask.
 *
 * Extender class for test cases.
 */
class CustomLintTestTask extends AbstractLintTask {

  /**
   * {@inheritdoc}
   */
  public function configureLint(LinterInterface $linter): void {}

}
