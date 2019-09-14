<?php

/**
 * @file
 * Tests covering ExternalExtensionLoaderBase.
 */

declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Definition;
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
    $class = TestLoader::class;
    $name = 'testname';
    $args = [
      'one',
      'two',
      'three',
    ];
    $stub = $this->getMockBuilder(ExternalExtensionLoaderBase::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['load'])
      ->getMockForAbstractClass();
    $container = $this->createMock(ContainerBuilder::class);

    $container->expects($this->once())
      ->method('register')
      ->with('task.' . $name, $class)
      ->willReturn(new Definition($class));
    $stub->taskInfo = [
      'name' => $name,
      'class' => $class,
      'arguments' => $args,
    ];

    /** @var \Symfony\Component\DependencyInjection\Definition $task */
    $task = $stub->load($container);
    $this->assertInstanceOf(Definition::class, $task);
    $this->assertEquals($args, $task->getArguments());
    $this->assertEquals($class, $task->getClass());
  }

  /**
   * Test load without arguments.
   *
   * @covers \Wunderio\GrumPHP\Task\ExternalExtensionLoaderBase::load
   */
  public function testLoadsDefinitionWithoutArguments(): void {
    $class = TestLoader::class;
    $name = 'testname';
    $stub = $this->getMockBuilder(ExternalExtensionLoaderBase::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['load'])
      ->getMockForAbstractClass();
    $container = $this->createMock(ContainerBuilder::class);

    $container->expects($this->once())
      ->method('register')
      ->with('task.' . $name, $class)
      ->willReturn(new Definition($class));
    $stub->taskInfo = [
      'name' => $name,
      'class' => $class,
    ];

    /** @var \Symfony\Component\DependencyInjection\Definition $task */
    $task = $stub->load($container);
    $this->assertInstanceOf(Definition::class, $task);
    $this->assertEquals([], $task->getArguments());
    $this->assertEquals($class, $task->getClass());
  }

}

/**
 * Class TestLoader.
 */
class TestLoader {}
