<?php

/**
 * @file
 * Tests covering ExternalExtensionLoaderBase.
 */

declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Wunderio\GrumPHP\Task\ContextFileExternalTaskBase;
use Wunderio\GrumPHP\Task\ExternalExtensionLoaderBase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class ExternalExtensionLoaderBaseTest.
 */
final class ExternalExtensionLoaderBaseTest extends TestCase {

  /**
   * Test load.
   *
   * @covers \Wunderio\GrumPHP\Task\ExternalExtensionLoaderBase::load
   */
  public function testLoadsDefinitionWithArguments(): void {
    $class = ContextFileExternalTaskBase::class;
    $name = 'default';
    $stub = $this->getMockBuilder(ExternalExtensionLoaderBase::class)
      ->setMethodsExcept(['load'])
      ->getMockForAbstractClass();
    $container = $this->createMock(ContainerBuilder::class);

    $container->expects($this->once())
      ->method('register')
      ->with('task.' . $name, $class)
      ->willReturn(new Definition($class));

    /** @var \Symfony\Component\DependencyInjection\Definition $task */
    $task = $stub->load($container);
    $this->assertInstanceOf(Definition::class, $task);
    $this->assertInstanceOf(Reference::class, $task->getArguments()[0]);
    $this->assertInstanceOf(Reference::class, $task->getArguments()[1]);
    $this->assertInstanceOf(Reference::class, $task->getArguments()[2]);
    $this->assertEquals($class, $task->getClass());
  }

  /**
   * Test load without arguments.
   *
   * @covers \Wunderio\GrumPHP\Task\ExternalExtensionLoaderBase::load
   */
  public function testLoadsDefinitionWithoutArguments(): void {
    $class = ContextFileExternalTaskBase::class;
    $name = 'default';
    $stub = $this->getMockBuilder(ExternalExtensionLoaderBase::class)
      ->setMethodsExcept(['load'])
      ->getMockForAbstractClass();
    $container = $this->createMock(ContainerBuilder::class);

    $container->expects($this->once())
      ->method('register')
      ->with('task.' . $name, $class)
      ->willReturn(new Definition($class));
    $stub->arguments = [];

    /** @var \Symfony\Component\DependencyInjection\Definition $task */
    $task = $stub->load($container);
    $this->assertInstanceOf(Definition::class, $task);
    $this->assertEquals([], $task->getArguments());
    $this->assertEquals($class, $task->getClass());
  }

}
