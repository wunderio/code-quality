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
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Wunderio\GrumPHP\Task\ContextFileExternalTaskBase;
use Symfony\Component\Finder\Finder;

/**
 * Class ContextFileExternalTaskBaseTest.
 *
 * @covers Wunderio\GrumPHP\Task\ContextFileExternalTaskBase
 */
final class ContextFileExternalTaskBaseTest extends TestCase {

  /**
   * Test name.
   */
  public function testGetName(): void {
    $stub = $this->getMockBuilder(ContextFileExternalTaskBase::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['getName'])
      ->getMockForAbstractClass();
    $stub->name = 'test_name';
    $this->assertEquals($stub->getName(), $stub->name);
  }

  /**
   * Test run contexts.
   */
  public function testCanRunInContext(): void {
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
   */
  public function testDefaultOptionsConfiguration(): void {
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
   */
  public function testGetFilesInGitContext(): void {
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
   */
  public function testGetFilesInRunContextWithoutSeparateFiles(): void {
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
   */
  public function testRunWithoutFiles(): void {
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

    $stub->expects($this->once())
      ->method('getFilesOrResult')
      ->willReturn($this->getMockBuilder(TaskResultInterface::class)->getMock());

    $processBuilder->expects($this->never())
      ->method('buildProcess');

    $context = $this->getMockBuilder(RunContext::class)
      ->disableOriginalConstructor()
      ->getMock();

    $actual = $stub->run($context);
    $this->assertInstanceOf(TaskResultInterface::class, $actual);
  }

  /**
   * Test run in scenario where no files or directories found.
   */
  public function testRunWithFiles(): void {
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
    $stub->expects($this->once())
      ->method('getTaskResult')
      ->willReturn(
        TaskResult::createPassed($stub, $this->getMockBuilder(ContextInterface::class)->getMock())
      );

    $context = $this->getMockBuilder(RunContext::class)
      ->disableOriginalConstructor()
      ->getMock();

    $actual = $stub->run($context);
    $this->assertInstanceOf(TaskResultInterface::class, $actual);
    $this->assertTrue($actual->isPassed());
  }

  /**
   * Test get Files in run context with file separation.
   */
  public function testGetFilesInRunContextWithSeparateFiles(): void {
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
   */
  public function testGetContextFiles(): void {
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
   */
  public function testGetFileFinder(): void {
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
   */
  public function testGetFilesFromConfig(): void {
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
   */
  public function testGetTaskResultUnsuccessful(): void {
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
   */
  public function testGetTaskResultSuccessful(): void {
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
   */
  public function testGetFilesOrResult(): void {
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
   */
  public function testGetFilesOrResultInGitWithEmptyArray(): void {
    $stub = $this->getMockBuilder(ContextFileExternalTaskBase::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['getFilesOrResult'])
      ->getMockForAbstractClass();

    $context = $this->getMockBuilder(GitPreCommitContext::class)->disableOriginalConstructor()->getMock();
    $stub->expects($this->once())->method('getFiles')->willReturn([]);

    $actual = $stub->getFilesOrResult($context);
    $this->assertInstanceOf(TaskResultInterface::class, $actual);
  }

  /**
   * Test get Files Or Result in git context with empty Files collection.
   */
  public function testGetFilesOrResultInGitWithEmptyFilesCollection(): void {
    $stub = $this->getMockBuilder(ContextFileExternalTaskBase::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['getFilesOrResult'])
      ->getMockForAbstractClass();

    $context = $this->getMockBuilder(GitPreCommitContext::class)->disableOriginalConstructor()->getMock();
    $stub->expects($this->once())->method('getFiles')->willReturn(new FilesCollection());

    $actual = $stub->getFilesOrResult($context);
    $this->assertInstanceOf(TaskResultInterface::class, $actual);
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
