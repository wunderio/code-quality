<?php

/**
 * @file
 * Tests covering ContextFileExternalTaskBase.
 */

declare(strict_types = 1);

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitCommitMsgContext;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Wunderio\GrumPHP\Task\ContextFileExternalTaskBase;
use Symfony\Component\Finder\Finder;

/**
 * Class ContextFileExternalTaskBaseTest.
 */
final class ContextFileExternalTaskBaseTest extends TestCase {

  /**
   * Test name.
   *
   * @covers \Wunderio\GrumPHP\Task\ContextFileExternalTaskBase::getName
   */
  public function testGetsTaskName(): void {
    $stub = $this->getMockBuilder(ContextFileExternalTaskBase::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['getName'])
      ->getMockForAbstractClass();
    $stub->name = 'test_name';
    $this->assertEquals($stub->getName(), $stub->name);
  }

  /**
   * Test run contexts.
   *
   * @covers \Wunderio\GrumPHP\Task\ContextFileExternalTaskBase::canRunInContext
   */
  public function testRunsInGitAndRunContexts(): void {
    $stub = $this->getMockBuilder(ContextFileExternalTaskBase::class)
      ->disableOriginalConstructor()
      ->onlyMethods([
        'canRunInContext',
      ])
      ->setMethodsExcept(['canRunInContext'])
      ->getMockForAbstractClass();
    $this->assertTrue($stub->canRunInContext(new RunContext(new FilesCollection())));
    $this->assertTrue($stub->canRunInContext(new GitPreCommitContext(new FilesCollection())));

    $commitMessageContext = $this->getMockBuilder(GitCommitMsgContext::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->assertFalse($stub->canRunInContext($commitMessageContext));
  }

  /**
   * Test Default Options Configuration.
   *
   * @covers \Wunderio\GrumPHP\Task\ContextFileExternalTaskBase::getConfigurableOptions
   */
  public function testGetsConfigurableOptions(): void {
    $stub = $this->getMockBuilder(ContextFileExternalTaskBase::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['getConfigurableOptions'])
      ->getMockForAbstractClass();
    $resolver = $stub->getConfigurableOptions();
    $options = array_flip($resolver->getDefinedOptions());
    $this->assertArrayHasKey('ignore_patterns', $options);
    $this->assertArrayHasKey('extensions', $options);
    $this->assertArrayHasKey('run_on', $options);
  }

  /**
   * Test get Files in git context.
   *
   * @covers \Wunderio\GrumPHP\Task\ContextFileExternalTaskBase::getFiles
   */
  public function testGetsFilesInGitContext(): void {
    $stub = $this->getMockBuilder(ContextFileExternalTaskBase::class)
      ->disableOriginalConstructor()
      ->onlyMethods([
        'getConfiguration',
        'getContextFiles',
        'getFiles',
        'getFilesFromConfig',
      ])
      ->setMethodsExcept(['getFiles'])
      ->getMockForAbstractClass();

    $stub->expects($this->once())
      ->method('getConfiguration')
      ->willReturn($this->getConfigDefaults());
    $stub->expects($this->once())
      ->method('getContextFiles')
      ->willReturn(new FilesCollection([]));

    $stub->expects($this->never())
      ->method('getFilesFromConfig');

    $context = $this->getMockBuilder(GitPreCommitContext::class)
      ->disableOriginalConstructor()
      ->getMock();

    $files = $stub->getFiles($context);
    $this->assertInstanceOf(FilesCollection::class, $files);
  }

  /**
   * Test get Files in run context without file separation.
   *
   * @covers \Wunderio\GrumPHP\Task\ContextFileExternalTaskBase::getFiles
   */
  public function testGetsRunOnPathsInRunContextIfFileSeparationNotEnabled(): void {
    $stub = $this->getMockBuilder(ContextFileExternalTaskBase::class)
      ->disableOriginalConstructor()
      ->onlyMethods([
        'getConfiguration',
        'getContextFiles',
        'getFiles',
        'getFilesFromConfig',
      ])
      ->setMethodsExcept(['getFiles'])
      ->getMockForAbstractClass();

    $stub->expects($this->once())
      ->method('getConfiguration')
      ->willReturn($this->getConfigDefaults());

    $stub->expects($this->never())
      ->method('getFilesFromConfig');
    $stub->expects($this->never())
      ->method('getContextFiles');

    $context = $this->getMockBuilder(RunContext::class)
      ->disableOriginalConstructor()
      ->getMock();

    $files = $stub->getFiles($context);
    $this->assertEquals($files, $this->getConfigDefaults()['run_on']);
  }

  /**
   * Test run in scenario where no files or directories found.
   *
   * @covers \Wunderio\GrumPHP\Task\ContextFileExternalTaskBase::run
   */
  public function testSkipsTaskIfNoFilesFound(): void {
    $grumPHP = $this->getMockBuilder(GrumPHP::class)->disableOriginalConstructor()->getMock();
    $processBuilder = $this->getMockBuilder(ProcessBuilder::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['buildProcess'])
      ->getMock();
    $processFormatterInterface = $this->getMockBuilder(ProcessFormatterInterface::class)->getMock();
    $stub = $this->getMockBuilder(ContextFileExternalTaskBase::class)
      ->setConstructorArgs([
        $grumPHP,
        $processBuilder,
        $processFormatterInterface,
      ])
      ->onlyMethods(['getFilesOrResult', 'run'])
      ->setMethodsExcept(['run'])
      ->getMockForAbstractClass();
    $context = $this->getMockBuilder(RunContext::class)
      ->disableOriginalConstructor()
      ->getMock();

    $stub->expects($this->once())
      ->method('getFilesOrResult')
      ->willReturn(TaskResult::createSkipped($this->createMock(TaskInterface::class), $context));

    $processBuilder->expects($this->never())
      ->method('buildProcess');

    $actual = $stub->run($context);
    $this->assertInstanceOf(TaskResultInterface::class, $actual);
    $this->assertEquals(TaskResult::SKIPPED, $actual->getResultCode());
  }

  /**
   * Test run in scenario with files or directories found.
   *
   * @covers \Wunderio\GrumPHP\Task\ContextFileExternalTaskBase::run
   */
  public function testReturnsTaskResultIfFileFoundAndProcessUnsuccessful(): void {
    $grumPHP = $this->getMockBuilder(GrumPHP::class)->disableOriginalConstructor()->getMock();
    $processBuilder = $this->getMockBuilder(ProcessBuilder::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['buildProcess'])
      ->getMock();
    $processFormatterInterface = $this->getMockBuilder(ProcessFormatterInterface::class)->getMock();
    $stub = $this->getMockBuilder(ContextFileExternalTaskBase::class)
      ->setConstructorArgs([
        $grumPHP,
        $processBuilder,
        $processFormatterInterface,
      ])
      ->onlyMethods([
        'getFilesOrResult',
        'buildArguments',
        'run',
        'getTaskResult',
      ])
      ->setMethodsExcept(['run'])
      ->getMockForAbstractClass();

    $stub->expects($this->once())
      ->method('getFilesOrResult')
      ->willReturn(['file.php']);
    $process = $this->getMockBuilder(Process::class)
      ->disableOriginalConstructor()
      ->getMock();
    $process->expects($this->once())->method('run');
    $stub->expects($this->once())
      ->method('buildArguments')
      ->willReturn($this->getMockBuilder(ProcessArgumentsCollection::class)->getMock());
    $processBuilder->expects($this->once())
      ->method('buildProcess')
      ->willReturn($process);
    $message = 'Test message...';
    $stub->expects($this->once())
      ->method('getTaskResult')
      ->willReturn(
        TaskResult::createFailed($stub, $this->createMock(ContextInterface::class), $message)
      );

    $context = $this->getMockBuilder(RunContext::class)
      ->disableOriginalConstructor()
      ->getMock();

    $actual = $stub->run($context);
    $this->assertInstanceOf(TaskResultInterface::class, $actual);
    $this->assertFalse($actual->isPassed());
    $this->assertEquals($message, $actual->getMessage());
  }

  /**
   * Test get Files in run context with file separation.
   *
   * @covers \Wunderio\GrumPHP\Task\ContextFileExternalTaskBase::getFiles
   */
  public function testGetsFilesFromConfigurationInRunContextIfFileSeparationEnabled(): void {
    $stub = $this->getMockBuilder(ContextFileExternalTaskBase::class)
      ->disableOriginalConstructor()
      ->onlyMethods([
        'getConfiguration',
        'getContextFiles',
        'getFiles',
        'getFilesFromConfig',
      ])
      ->setMethodsExcept(['getFiles'])
      ->getMockForAbstractClass();

    $stub->isFileSpecific = TRUE;

    $stub->expects($this->once())
      ->method('getConfiguration')
      ->willReturn($this->getConfigDefaults());
    $stub->expects($this->once())
      ->method('getFilesFromConfig')
      ->willReturn(new FilesCollection([]));

    $stub->expects($this->never())
      ->method('getContextFiles');

    $context = $this->getMockBuilder(RunContext::class)
      ->disableOriginalConstructor()
      ->getMock();

    $files = $stub->getFiles($context);
    $this->assertInstanceOf(FilesCollection::class, $files);
  }

  /**
   * Test get Files in run context with file separation.
   *
   * @covers \Wunderio\GrumPHP\Task\ContextFileExternalTaskBase::getContextFiles
   */
  public function testFiltersContextFilesWithConfigurationSets(): void {
    $stub = $this->getMockBuilder(ContextFileExternalTaskBase::class)
      ->disableOriginalConstructor()
      ->onlyMethods([
        'getConfiguration',
        'getContextFiles',
      ])
      ->setMethodsExcept(['getContextFiles'])
      ->getMockForAbstractClass();

    $context = $this->getMockBuilder(ContextInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $filesCollection = $this->getMockBuilder(FilesCollection::class)
      ->disableOriginalConstructor()
      ->getMock();

    $context->expects($this->once())
      ->method('getFiles')
      ->willReturn($filesCollection);
    $filesCollection->expects($this->once())
      ->method('extensions')
      ->willReturn($filesCollection);
    $filesCollection->expects($this->once())
      ->method('paths')
      ->willReturn($filesCollection);
    $filesCollection->expects($this->once())
      ->method('notPaths')
      ->willReturn($filesCollection);

    $files = $stub->getContextFiles($context, $this->getConfigDefaults());
    $this->assertInstanceOf(FilesCollection::class, $files);
  }

  /**
   * Test get File finder.
   *
   * @covers \Wunderio\GrumPHP\Task\ContextFileExternalTaskBase::getFileFinder
   */
  public function testGetsFileFinder(): void {
    $stub = $this->getMockBuilder(ContextFileExternalTaskBase::class)
      ->disableOriginalConstructor()
      ->onlyMethods([
        'getFileFinder',
      ])
      ->setMethodsExcept(['getFileFinder'])
      ->getMockForAbstractClass();
    $this->assertInstanceOf(Finder::class, $stub->getFileFinder());
  }

  /**
   * Test get files from config.
   *
   * @covers \Wunderio\GrumPHP\Task\ContextFileExternalTaskBase::getFilesFromConfig
   */
  public function testFindsFilesUsingConfiguration(): void {
    $stub = $this->getMockBuilder(ContextFileExternalTaskBase::class)
      ->disableOriginalConstructor()
      ->onlyMethods([
        'getFileFinder',
        'getFilesFromConfig',
      ])
      ->setMethodsExcept(['getFilesFromConfig'])
      ->getMockForAbstractClass();

    $files = $this->getMockBuilder(Finder::class)
      ->onlyMethods([
        'name',
        'in',
        'notPath',
        'getIterator',
      ])->getMock();

    $stub->expects($this->once())->method('getFileFinder')->willReturn($files);

    $files->expects($this->once())->method('name');
    $files->expects($this->once())->method('in');
    $files->expects($this->once())->method('notPath');
    $files->expects($this->once())->method('getIterator')->willReturn(new \AppendIterator());

    $this->assertInstanceOf(FilesCollection::class, $stub->getFilesFromConfig([
      'ignore_patterns' => ['/vendor/'],
      'extensions' => ['php'],
      'run_on' => ['.'],
    ]));
  }

  /**
   * Test get unsuccessful task result.
   *
   * @covers \Wunderio\GrumPHP\Task\ContextFileExternalTaskBase::getTaskResult
   */
  public function testFailsTaskIfProcessUnsuccessful(): void {
    $grumPHP = $this->getMockBuilder(GrumPHP::class)
      ->disableOriginalConstructor()
      ->getMock();
    $processBuilder = $this->getMockBuilder(ProcessBuilder::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['buildProcess'])
      ->getMock();
    $processFormatterInterface = $this->getMockBuilder(ProcessFormatterInterface::class)->getMock();
    $processFormatterInterface->expects($this->once())->method('format')->willReturn('Formatted Output.');
    $stub = $this->getMockBuilder(ContextFileExternalTaskBase::class)
      ->setConstructorArgs([
        $grumPHP,
        $processBuilder,
        $processFormatterInterface,
      ])
      ->setMethodsExcept(['getTaskResult'])
      ->getMockForAbstractClass();

    $context = $this->getMockBuilder(ContextInterface::class)->getMock();
    $process = $this->getMockBuilder(Process::class)->disableOriginalConstructor()->getMock();
    $process->expects($this->once())->method('isSuccessful')->willReturn(FALSE);

    $result = $stub->getTaskResult($context, $process);
    $this->assertFalse($result->isPassed());
  }

  /**
   * Test get successful task result.
   *
   * @covers \Wunderio\GrumPHP\Task\ContextFileExternalTaskBase::getTaskResult
   */
  public function testPassesTaskIfProcessSuccessful(): void {
    $stub = $this->getMockBuilder(ContextFileExternalTaskBase::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['getTaskResult'])
      ->getMockForAbstractClass();

    $context = $this->getMockBuilder(ContextInterface::class)->getMock();
    $process = $this->getMockBuilder(Process::class)->disableOriginalConstructor()->getMock();
    $process->expects($this->once())->method('isSuccessful')->willReturn(TRUE);

    $result = $stub->getTaskResult($context, $process);
    $this->assertTrue($result->isPassed());
  }

  /**
   * Test get Files Or Result in run context.
   *
   * @covers \Wunderio\GrumPHP\Task\ContextFileExternalTaskBase::getFilesOrResult
   */
  public function testReturnsArrayOfFilesIfFoundAfterFiltering(): void {
    $stub = $this->getMockBuilder(ContextFileExternalTaskBase::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['getFilesOrResult'])
      ->getMockForAbstractClass();

    $file = ['file.php'];
    $context = $this->getMockBuilder(RunContext::class)->disableOriginalConstructor()->getMock();
    $stub->expects($this->once())->method('getFiles')->willReturn($file);

    $actual = $stub->getFilesOrResult($context);
    $this->assertEquals($file, $actual);
  }

  /**
   * Test get Files Or Result in git context with empty array of files.
   *
   * @covers \Wunderio\GrumPHP\Task\ContextFileExternalTaskBase::getFilesOrResult
   */
  public function testSkipsTaskIfEmptyArrayOfFilesProvided(): void {
    $stub = $this->getMockBuilder(ContextFileExternalTaskBase::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['getFilesOrResult'])
      ->getMockForAbstractClass();

    $context = $this->getMockBuilder(GitPreCommitContext::class)->disableOriginalConstructor()->getMock();
    $stub->expects($this->once())->method('getFiles')->willReturn([]);

    $actual = $stub->getFilesOrResult($context);
    $this->assertInstanceOf(TaskResultInterface::class, $actual);
    $this->assertEquals(TaskResult::SKIPPED, $actual->getResultCode());
  }

  /**
   * Test get Files Or Result in git context with empty Files collection.
   *
   * @covers \Wunderio\GrumPHP\Task\ContextFileExternalTaskBase::getFilesOrResult
   */
  public function testSkipsTaskIfEmptyFilesCollectionOfFilesProvided(): void {
    $stub = $this->getMockBuilder(ContextFileExternalTaskBase::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['getFilesOrResult'])
      ->getMockForAbstractClass();

    $context = $this->getMockBuilder(GitPreCommitContext::class)->disableOriginalConstructor()->getMock();
    $stub->expects($this->once())->method('getFiles')->willReturn(new FilesCollection());

    $actual = $stub->getFilesOrResult($context);
    $this->assertInstanceOf(TaskResultInterface::class, $actual);
    $this->assertEquals(TaskResult::SKIPPED, $actual->getResultCode());
  }

  /**
   * Default configuration.
   *
   * @return array
   *   Configuration.
   */
  protected function getConfigDefaults(): array {
    return [
      'ignore_patterns' => ContextFileExternalTaskBase::IGNORE_PATTERNS,
      'extensions' => ContextFileExternalTaskBase::EXTENSIONS,
      'run_on' => ContextFileExternalTaskBase::RUN_ON,
    ];
  }

}
