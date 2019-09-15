<?php

/**
 * @file
 * Tests covering AbstractConfigurableContextFileExternalTask.
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
use Wunderio\GrumPHP\Task\AbstractConfigurableContextFileExternalTask;
use Symfony\Component\Finder\Finder;

/**
 * Class AbstractConfigurableContextFileExternalTaskTest.
 */
final class AbstractConfigurableContextFileExternalTaskTest extends TestCase {

  /**
   * Test class constructor.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractConfigurableContextFileExternalTask::__construct
   */
  public function testSetsConfigurationFromYaml(): void {
    $customTask = new CustomTestTask(
        $this->createMock(GrumPHP::class),
        $this->createMock(ProcessBuilder::class),
        $this->createMock(ProcessFormatterInterface::class)
    );
    $this->assertEquals('custom_test', $customTask->name);
    $this->assertEquals(['config', 'process_builder', 'formatter.raw_process'], $customTask->arguments);
    $this->assertCount(3, $customTask->configurableOptions);
  }

  /**
   * Test name.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractConfigurableContextFileExternalTask::getName
   */
  public function testGetsTaskName(): void {
    $stub = $this->getMockBuilder(AbstractConfigurableContextFileExternalTask::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['getName'])
      ->getMockForAbstractClass();
    $stub->name = 'test_name';
    $this->assertEquals($stub->getName(), $stub->name);
  }

  /**
   * Test run contexts.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractConfigurableContextFileExternalTask::canRunInContext
   */
  public function testRunsInGitAndRunContexts(): void {
    $stub = $this->getMockBuilder(AbstractConfigurableContextFileExternalTask::class)
      ->disableOriginalConstructor()
      ->onlyMethods([
        'canRunInContext',
      ])
      ->setMethodsExcept(['canRunInContext'])
      ->getMockForAbstractClass();
    $this->assertTrue($stub->canRunInContext(new RunContext(new FilesCollection())));
    $this->assertTrue($stub->canRunInContext(new GitPreCommitContext(new FilesCollection())));

    $commitMessageContext = $this->createMock(GitCommitMsgContext::class);
    $this->assertFalse($stub->canRunInContext($commitMessageContext));
  }

  /**
   * Test Default Options Configuration.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractConfigurableContextFileExternalTask::getConfigurableOptions
   */
  public function testGetsConfigurableOptions(): void {
    $stub = $this->getMockBuilder(AbstractConfigurableContextFileExternalTask::class)
      ->setConstructorArgs([
        $this->createMock(GrumPHP::class),
        $this->createMock(ProcessBuilder::class),
        $this->createMock(ProcessFormatterInterface::class),
      ])
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
   * @covers \Wunderio\GrumPHP\Task\AbstractConfigurableContextFileExternalTask::getFiles
   */
  public function testGetsFilesInGitContext(): void {
    $stub = $this->getMockBuilder(AbstractConfigurableContextFileExternalTask::class)
      ->disableOriginalConstructor()
      ->onlyMethods([
        'getConfiguration',
        'getContextFiles',
        'getFiles',
        'getFilesFromConfig',
      ])
      ->setMethodsExcept(['getFiles'])
      ->getMockForAbstractClass();
    $context = $this->createMock(GitPreCommitContext::class);

    $stub->expects($this->once())->method('getConfiguration')->willReturn($this->getConfigDefaults());
    $stub->expects($this->once())->method('getContextFiles')->willReturn(new FilesCollection([]));
    $stub->expects($this->never())->method('getFilesFromConfig');

    $files = $stub->getFiles($context);
    $this->assertInstanceOf(FilesCollection::class, $files);
  }

  /**
   * Test get Files in run context without file separation.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractConfigurableContextFileExternalTask::getFiles
   */
  public function testGetsRunOnPathsInRunContextIfFileSeparationNotEnabled(): void {
    $stub = $this->getMockBuilder(AbstractConfigurableContextFileExternalTask::class)
      ->disableOriginalConstructor()
      ->onlyMethods([
        'getConfiguration',
        'getContextFiles',
        'getFiles',
        'getFilesFromConfig',
      ])
      ->setMethodsExcept(['getFiles'])
      ->getMockForAbstractClass();
    $context = $this->createMock(RunContext::class);

    $stub->expects($this->once())->method('getConfiguration')->willReturn($this->getConfigDefaults());
    $stub->expects($this->never())->method('getFilesFromConfig');
    $stub->expects($this->never())->method('getContextFiles');

    $files = $stub->getFiles($context);
    $this->assertEquals($files, $this->getConfigDefaults()['run_on']);
  }

  /**
   * Test run in scenario where no files or directories found.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractConfigurableContextFileExternalTask::run
   */
  public function testSkipsTaskIfNoFilesFound(): void {
    $grumPHP = $this->createMock(GrumPHP::class);
    $processBuilder = $this->getMockBuilder(ProcessBuilder::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['buildProcess'])
      ->getMock();
    $processFormatterInterface = $this->createMock(ProcessFormatterInterface::class);
    $stub = $this->getMockBuilder(AbstractConfigurableContextFileExternalTask::class)
      ->setConstructorArgs([
        $grumPHP,
        $processBuilder,
        $processFormatterInterface,
      ])
      ->onlyMethods(['getFilesOrResult', 'run'])
      ->setMethodsExcept(['run'])
      ->getMockForAbstractClass();
    $context = $this->createMock(RunContext::class);

    $stub->expects($this->once())
      ->method('getFilesOrResult')
      ->willReturn(TaskResult::createSkipped($this->createMock(TaskInterface::class), $context));
    $processBuilder->expects($this->never())->method('buildProcess');

    $actual = $stub->run($context);
    $this->assertInstanceOf(TaskResultInterface::class, $actual);
    $this->assertEquals(TaskResult::SKIPPED, $actual->getResultCode());
  }

  /**
   * Test run in scenario with files or directories found.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractConfigurableContextFileExternalTask::run
   */
  public function testReturnsTaskResultIfFileFoundAndProcessUnsuccessful(): void {
    $processBuilder = $this->getMockBuilder(ProcessBuilder::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['buildProcess'])
      ->getMock();
    $stub = $this->getMockBuilder(AbstractConfigurableContextFileExternalTask::class)
      ->setConstructorArgs([
        $this->createMock(GrumPHP::class),
        $processBuilder,
        $this->createMock(ProcessFormatterInterface::class),
      ])
      ->onlyMethods([
        'getFilesOrResult',
        'buildArguments',
        'run',
        'getTaskResult',
      ])
      ->setMethodsExcept(['run'])
      ->getMockForAbstractClass();
    $message = 'Test message...';

    $stub->expects($this->once())->method('getFilesOrResult')->willReturn(['file.php']);
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

  /**
   * Test get Files in run context with file separation.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractConfigurableContextFileExternalTask::getFiles
   */
  public function testGetsFilesFromConfigurationInRunContextIfFileSeparationEnabled(): void {
    $stub = $this->getMockBuilder(AbstractConfigurableContextFileExternalTask::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['getFiles'])
      ->getMockForAbstractClass();

    $stub->isFileSpecific = TRUE;

    $stub->expects($this->once())->method('getConfiguration')->willReturn($this->getConfigDefaults());
    $stub->expects($this->once())->method('getFilesFromConfig')->willReturn(new FilesCollection([]));
    $stub->expects($this->never())->method('getContextFiles');

    $files = $stub->getFiles($this->createMock(RunContext::class));
    $this->assertInstanceOf(FilesCollection::class, $files);
  }

  /**
   * Test get Files in run context with file separation.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractConfigurableContextFileExternalTask::getContextFiles
   */
  public function testFiltersContextFilesWithConfigurationSets(): void {
    $stub = $this->getMockBuilder(AbstractConfigurableContextFileExternalTask::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['getContextFiles'])
      ->getMockForAbstractClass();
    $context = $this->createMock(ContextInterface::class);
    $filesCollection = $this->createMock(FilesCollection::class);

    $context->expects($this->once())->method('getFiles')->willReturn($filesCollection);
    $filesCollection->expects($this->once())->method('extensions')->willReturn($filesCollection);
    $filesCollection->expects($this->once())->method('paths')->willReturn($filesCollection);
    $filesCollection->expects($this->once())->method('notPaths')->willReturn($filesCollection);

    $files = $stub->getContextFiles($context, $this->getConfigDefaults());
    $this->assertInstanceOf(FilesCollection::class, $files);
  }

  /**
   * Test get File finder.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractConfigurableContextFileExternalTask::getFileFinder
   */
  public function testGetsFileFinder(): void {
    $stub = $this->getMockBuilder(AbstractConfigurableContextFileExternalTask::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['getFileFinder'])
      ->getMockForAbstractClass();
    $this->assertInstanceOf(Finder::class, $stub->getFileFinder());
  }

  /**
   * Test get files from config.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractConfigurableContextFileExternalTask::getFilesFromConfig
   */
  public function testFindsFilesUsingConfiguration(): void {
    $stub = $this->getMockBuilder(AbstractConfigurableContextFileExternalTask::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['getFilesFromConfig'])
      ->getMockForAbstractClass();
    $files = $this->createMock(Finder::class);

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
   * @covers \Wunderio\GrumPHP\Task\AbstractConfigurableContextFileExternalTask::getTaskResult
   */
  public function testFailsTaskIfProcessUnsuccessful(): void {
    $processFormatterInterface = $this->createMock(ProcessFormatterInterface::class);
    $processFormatterInterface->expects($this->once())->method('format')->willReturn('Formatted Output.');
    $stub = $this->getMockBuilder(AbstractConfigurableContextFileExternalTask::class)
      ->setConstructorArgs([
        $this->createMock(GrumPHP::class),
        $this->createMock(ProcessBuilder::class),
        $processFormatterInterface,
      ])
      ->setMethodsExcept(['getTaskResult'])
      ->getMockForAbstractClass();
    $process = $this->createMock(Process::class);

    $process->expects($this->once())->method('isSuccessful')->willReturn(FALSE);

    $result = $stub->getTaskResult($this->createMock(ContextInterface::class), $process);
    $this->assertFalse($result->isPassed());
  }

  /**
   * Test get successful task result.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractConfigurableContextFileExternalTask::getTaskResult
   */
  public function testPassesTaskIfProcessSuccessful(): void {
    $stub = $this->getMockBuilder(AbstractConfigurableContextFileExternalTask::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['getTaskResult'])
      ->getMockForAbstractClass();

    $process = $this->createMock(Process::class);
    $process->expects($this->once())->method('isSuccessful')->willReturn(TRUE);

    $result = $stub->getTaskResult($this->createMock(ContextInterface::class), $process);
    $this->assertTrue($result->isPassed());
  }

  /**
   * Test get Files Or Result in run context.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractConfigurableContextFileExternalTask::getFilesOrResult
   */
  public function testReturnsArrayOfFilesIfFoundAfterFiltering(): void {
    $stub = $this->getMockBuilder(AbstractConfigurableContextFileExternalTask::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['getFilesOrResult'])
      ->getMockForAbstractClass();

    $file = ['file.php'];
    $stub->expects($this->once())->method('getFiles')->willReturn($file);

    $actual = $stub->getFilesOrResult($this->createMock(RunContext::class));
    $this->assertEquals($file, $actual);
  }

  /**
   * Test get Files Or Result in git context with empty array of files.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractConfigurableContextFileExternalTask::getFilesOrResult
   */
  public function testSkipsTaskIfEmptyArrayOfFilesProvided(): void {
    $stub = $this->getMockBuilder(AbstractConfigurableContextFileExternalTask::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['getFilesOrResult'])
      ->getMockForAbstractClass();

    $stub->expects($this->once())->method('getFiles')->willReturn([]);

    $actual = $stub->getFilesOrResult($this->createMock(GitPreCommitContext::class));
    $this->assertInstanceOf(TaskResultInterface::class, $actual);
    $this->assertEquals(TaskResult::SKIPPED, $actual->getResultCode());
  }

  /**
   * Test get Files Or Result in git context with empty Files collection.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractConfigurableContextFileExternalTask::getFilesOrResult
   */
  public function testSkipsTaskIfEmptyFilesCollectionOfFilesProvided(): void {
    $stub = $this->getMockBuilder(AbstractConfigurableContextFileExternalTask::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['getFilesOrResult'])
      ->getMockForAbstractClass();

    $stub->expects($this->once())->method('getFiles')->willReturn(new FilesCollection());

    $actual = $stub->getFilesOrResult($this->createMock(GitPreCommitContext::class));
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
      'ignore_patterns' => ['/vendor/'],
      'extensions' => ['php'],
      'run_on' => ['.'],
    ];
  }

}

/**
 * Class CustomTestTask.
 *
 * Extender class for test cases.
 */
class CustomTestTask extends AbstractConfigurableContextFileExternalTask {

  /**
   * {@inheritdoc}
   */
  public function buildArguments(iterable $files): ProcessArgumentsCollection {}

}
