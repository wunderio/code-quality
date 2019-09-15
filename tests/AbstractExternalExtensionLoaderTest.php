<?php

/**
 * @file
 * Tests covering AbstractExternalExtensionLoader.
 */

declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Wunderio\GrumPHP\Task\AbstractExternalExtensionLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class AbstractExternalExtensionLoaderTest.
 */
final class AbstractExternalExtensionLoaderTest extends TestCase {

  /**
   * Test load.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractExternalExtensionLoader::load
   */
  public function testLoadsDefinitionWithArguments(): void {
    $class = AbstractConfigurableContextFileExternalTaskTest::class;
    $stub = $this->getMockBuilder(AbstractExternalExtensionLoader::class)
      ->setMethodsExcept(['load'])
      ->getMockForAbstractClass();
    $container = $this->createMock(ContainerBuilder::class);

    $container->expects($this->once())
      ->method('register')
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
   * @covers \Wunderio\GrumPHP\Task\AbstractExternalExtensionLoader::load
   */
  public function testLoadsDefinitionWithoutArguments(): void {
    $class = AbstractConfigurableContextFileExternalTaskTest::class;
    $stub = $this->getMockBuilder(AbstractExternalExtensionLoader::class)
      ->setMethodsExcept(['load'])
      ->getMockForAbstractClass();
    $container = $this->createMock(ContainerBuilder::class);

    $container->expects($this->once())
      ->method('register')
      ->willReturn(new Definition($class));
    $stub->arguments = [];

    /** @var \Symfony\Component\DependencyInjection\Definition $task */
    $task = $stub->load($container);
    $this->assertInstanceOf(Definition::class, $task);
    $this->assertEquals([], $task->getArguments());
    $this->assertEquals($class, $task->getClass());
  }

}
