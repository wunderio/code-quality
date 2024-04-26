<?php

/**
 * @file
 * Tests covering ConfigurableTaskTrait.
 */

declare(strict_types=1);

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Wunderio\GrumPHP\Task\ConfigurableTaskInterface;
use Wunderio\GrumPHP\Task\ConfigurableTaskTrait;

/**
 * Class ConfigurableTaskTraitTest.
 *
 * Tests covering ConfigurableTaskTrait trait.
 */
final class ConfigurableTaskTraitTest extends TestCase {

  /**
   * Test name.
   *
   * @covers \Wunderio\GrumPHP\Task\ConfigurableTaskTrait::getName
   */
  public function testGetsTaskName(): void {
    $stub = $this->getMockBuilder(ConfigurableTaskTrait::class)
      ->setMethodsExcept(['getName'])
      ->getMockForTrait();
    $stub->name = 'test_name';
    $this->assertEquals($stub->getName(), $stub->name);
  }

  /**
   * Test Default Options Configuration.
   *
   * @covers \Wunderio\GrumPHP\Task\ConfigurableTaskTrait::getConfigurableOptions
   */
  public function testGetsConfigurableOptions(): void {
    $stub = $this->getMockBuilder(ConfigurableTaskTrait::class)
      ->setMethodsExcept(['getConfigurableOptions', 'configure'])
      ->getMockForTrait();
    $stub->configure();
    $resolver = $stub->getConfigurableOptions();
    $options = array_flip($resolver->getDefinedOptions());
    $this->assertArrayHasKey('ignore_patterns', $options);
    $this->assertArrayHasKey('extensions', $options);
    $this->assertArrayHasKey('run_on', $options);
  }

  /**
   * Test get Files in git context.
   *
   * @covers \Wunderio\GrumPHP\Task\ConfigurableTaskTrait::getPaths
   */
  public function testGetsFilesInGitContext(): void {
    $stub = $this->getMockBuilder(ConfigurableTaskTrait::class)
      ->getMockForTrait();
    $context = $this->createMock(GitPreCommitContext::class);
    $context->expects($this->once())
      ->method('getFiles')
      ->willReturn(new FilesCollection([]));

    $files = $stub::getPaths($context, $this->getConfigDefaults(), FALSE);
    $this->assertInstanceOf(FilesCollection::class, $files);
  }

  /**
   * Test get Files in run context without file separation.
   *
   * @covers \Wunderio\GrumPHP\Task\ConfigurableTaskTrait::getPaths
   */
  public function testGetsRunOnPathsInRunContextIfFileSeparationNotEnabled(): void {
    $stub = $this->getMockBuilder(ConfigurableTaskTrait::class)
      ->getMockForTrait();
    $context = $this->createMock(RunContext::class);

    $files = $stub::getPaths($context, $this->getConfigDefaults(), FALSE);
    $this->assertEquals($files, $this->getConfigDefaults()['run_on']);
  }

  /**
   * Test get Files in run context with file separation.
   *
   * @covers \Wunderio\GrumPHP\Task\ConfigurableTaskTrait::getPaths
   */
  public function testGetsFilesFromConfigurationInRunContextIfFileSeparationEnabled(): void {
    $stub = $this->getMockBuilder(ConfigurableTaskTrait::class)
      ->getMockForTrait();

    $files = $stub::getPaths($this->createMock(RunContext::class), $this->getConfigDefaults(), TRUE);
    $this->assertInstanceOf(FilesCollection::class, $files);
  }

  /**
   * Test cleaning directory list from non-existing paths.
   *
   * @covers \Wunderio\GrumPHP\Task\ConfigurableTaskTrait::cleanupDirs
   */
  public function testRemoveNonExistingDirectories(): void {
    $stub = $this->getMockBuilder(ConfigurableTaskTrait::class)
      ->getMockForTrait();

    $dirs = [__DIR__, 'non_existing_dir/path'];

    $stub::cleanupDirs($dirs);
    $this->assertEquals([__DIR__], $dirs);
  }

  /**
   * Test get File finder.
   *
   * @covers \Wunderio\GrumPHP\Task\ConfigurableTaskTrait::getFileFinder
   */
  public function testGetsFileFinder(): void {
    $stub = $this->getMockBuilder(ConfigurableTaskTrait::class)
      ->getMockForTrait();
    $this->assertInstanceOf(Finder::class, $stub::getFileFinder());
  }

  /**
   * Test get files from config.
   *
   * @covers \Wunderio\GrumPHP\Task\ConfigurableTaskTrait::getFilesFromConfig
   */
  public function testFindsFilesUsingConfiguration(): void {
    $stub = $this->getMockBuilder(ConfigurableTaskTrait::class)
      ->getMockForTrait();

    $this->assertInstanceOf(FilesCollection::class, $stub::getFilesFromConfig([
      'ignore_patterns' => ['/vendor/'],
      'extensions' => ['php'],
      'run_on' => ['.'],
    ]));
  }

  /**
   * Test get empty files collection from config with no directories.
   *
   * @covers \Wunderio\GrumPHP\Task\ConfigurableTaskTrait::getFilesFromConfig
   */
  public function testReturnsEmptyFilesCollectionIfNoDirectoriesProvided(): void {
    $stub = $this->getMockBuilder(ConfigurableTaskTrait::class)
      ->getMockForTrait();

    $result = $stub::getFilesFromConfig([
      'ignore_patterns' => ['/vendor/'],
      'extensions' => ['php'],
      'run_on' => [],
    ]);
    $this->assertInstanceOf(FilesCollection::class, $result);
    $this->assertTrue($result->isEmpty());
  }

  /**
   * Test get Files Or Result in run context.
   *
   * @covers \Wunderio\GrumPHP\Task\ConfigurableTaskTrait::getPathsOrResult
   */
  public function testReturnsArrayOfFilesIfFoundAfterFiltering(): void {
    $stub = $this->getMockBuilder(ConfigurableTaskTrait::class)
      ->getMockForTrait();
    $stub->configure();

    $actual = $stub->getPathsOrResult(
      $this->createMock(RunContext::class),
      $this->getConfigDefaults(),
      $this->createMock(ConfigurableTaskInterface::class)
    );
    $this->assertEquals(['.'], $actual);
  }

  /**
   * Test get Files Or Result in git context with empty array of files.
   *
   * @covers \Wunderio\GrumPHP\Task\ConfigurableTaskTrait::getPathsOrResult
   */
  public function testSkipsTaskIfEmptyArrayOfFilesProvided(): void {
    $stub = $this->getMockBuilder(ConfigurableTaskTrait::class)
      ->getMockForTrait();

    $conf = $this->getConfigDefaults();
    $conf[ConfigurableTaskInterface::D_RUN] = [];

    $actual = $stub->getPathsOrResult(
      $this->createMock(GitPreCommitContext::class),
      $conf,
      $this->createMock(ConfigurableTaskInterface::class)
    );
    $this->assertInstanceOf(TaskResultInterface::class, $actual);
    $this->assertEquals(TaskResult::SKIPPED, $actual->getResultCode());
  }

  /**
   * Test get Files Or Result in git context with empty Files collection.
   *
   * @covers \Wunderio\GrumPHP\Task\ConfigurableTaskTrait::getPathsOrResult
   */
  public function testSkipsTaskIfEmptyFilesCollectionOfFilesProvided(): void {
    $stub = $this->getMockBuilder(ConfigurableTaskTrait::class)
      ->getMockForTrait();

    $context = $this->createMock(GitPreCommitContext::class);
    $context->expects($this->once())->method('getFiles')->willReturn(new FilesCollection([]));

    $actual = $stub->getPathsOrResult(
      $context,
      $this->getConfigDefaults(),
      $this->createMock(ConfigurableTaskInterface::class)
    );
    $this->assertInstanceOf(TaskResultInterface::class, $actual);
    $this->assertEquals(TaskResult::SKIPPED, $actual->getResultCode());
  }

  /**
   * Test is file path specific.
   *
   * @covers \Wunderio\GrumPHP\Task\ConfigurableTaskTrait::isFileSpecific
   */
  public function testReturnsFileSeparationFlag(): void {
    $stub = $this->getMockBuilder(ConfigurableTaskTrait::class)
      ->getMockForTrait();
    $stub->isFileSpecific = TRUE;
    $this->assertTrue($stub->isFileSpecific());
    $stub->isFileSpecific = FALSE;
    $this->assertFalse($stub->isFileSpecific());
  }

  /**
   * Test configuration from tasks.yml.
   *
   * @covers \Wunderio\GrumPHP\Task\ConfigurableTaskTrait::configure
   */
  public function testConfiguresDefaults(): void {
    $stub = $this->getMockBuilder(ConfigurableTaskTrait::class)
      ->getMockForTrait();
    $stub->configure();
    $this->assertEquals(FALSE, $stub->isFileSpecific);
    $this->assertIsArray($stub->configurableOptions);
    $this->assertIsArray($stub->configurableOptions);
    $this->assertStringContainsString('_trait_', $stub->name, 'Name should be configured from class name');
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
