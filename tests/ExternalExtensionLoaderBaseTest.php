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
 *
 * @covers Wunderio\GrumPHP\Task\ExternalExtensionLoaderBase
 */
final class ExternalExtensionLoaderBaseTest extends TestCase {

  /**
   * Test load.
   */
  public function testLoad(): void {
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
    $container = $this->getMockBuilder(ContainerBuilder::class)
      ->disableOriginalConstructor()
      ->getMock();

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
   */
  public function testLoadWithoutArguments(): void {
    $class = TestLoader::class;
    $name = 'testname';
    $stub = $this->getMockBuilder(ExternalExtensionLoaderBase::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['load'])
      ->getMockForAbstractClass();
    $container = $this->getMockBuilder(ContainerBuilder::class)
      ->disableOriginalConstructor()
      ->getMock();

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
