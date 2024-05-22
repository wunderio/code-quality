<?php

/**
 * @file
 * Tests covering ContextRunTrait.
 */

declare(strict_types=1);

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Task\Context\GitCommitMsgContext;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use PHPUnit\Framework\TestCase;
use Wunderio\GrumPHP\Task\ContextRunTrait;

/**
 * Class ContextRunTraitTest.
 *
 * Tests covering ContextRunTrait trait.
 */
final class ContextRunTraitTest extends TestCase {

  /**
   * Test run contexts.
   *
   * @covers \Wunderio\GrumPHP\Task\ContextRunTrait::canRunInContext
   */
  public function testRunsInGitAndRunContexts(): void {
    $stub = $this->getMockBuilder(ContextRunTrait::class)
      ->setMethodsExcept(['canRunInContext'])
      ->getMockForTrait();
    $this->assertTrue($stub->canRunInContext(new RunContext(new FilesCollection())));
    $this->assertTrue($stub->canRunInContext(new GitPreCommitContext(new FilesCollection())));

    $commitMessageContext = $this->createMock(GitCommitMsgContext::class);
    $this->assertFalse($stub->canRunInContext($commitMessageContext));
  }

}
