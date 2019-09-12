<?php

declare(strict_types = 1);

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use PHPUnit\Framework\TestCase;
use Wunderio\GrumPHP\Task\ContextFileExternalTaskBase;
use Symfony\Component\Finder\Finder;

/**
 * Class ContextFileExternalTaskBaseTest.
 *
 * @covers Wunderio\GrumPHP\Task\ContextFileExternalTaskBase
 */
final class ContextFileExternalTaskBaseTest extends TestCase {

  /**
   * Test Default Options Configuration.
   */
  public function testDefaultOptionsConfiguration(): void {
    $stub = $this->getMockBuilder(ContextFileExternalTaskBase::class)
      ->disableOriginalConstructor()
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

    $files = $stub->getFiles($context, TRUE);
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

    $stub->getFilesFromConfig([
      'ignore_patterns' => ['/vendor/'],
      'extensions' => ['php'],
      'run_on' => ['.'],
    ]);

//    $this->assertInstanceOf(FilesCollection::class, $stub->getFilesFromConfig([
//      'ignore_patterns' => [],
//      'extensions' => ['php'],
//      'run_on' => ['.'],
//    ]));
  }

  /**
   * Default configuration.
   *
   * @return array
   *   Configuration.
   */
  protected function getConfigDefaults(): array {
    return [
      'ignore_patterns' => ContextFileExternalTaskBase::$ignorePatterns,
      'extensions' => ContextFileExternalTaskBase::$extensions,
      'run_on' => ContextFileExternalTaskBase::$extensions,
    ];
  }

}
