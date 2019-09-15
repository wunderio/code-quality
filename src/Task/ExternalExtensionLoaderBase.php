<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task;

use GrumPHP\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Yaml\Yaml;

/**
 * Class PhpCompatibilityExtensionLoader.
 *
 * @package Wunderio\GrumPHP\Task\PhpCompatibilityTask
 */
abstract class ExternalExtensionLoaderBase implements ExtensionInterface {

  /**
   * Name.
   *
   * @var string
   */
  public $name;

  /**
   * Construction arguments.
   *
   * @var array
   */
  public $arguments;

  /**
   * Task class.
   *
   * @var string
   */
  public $class;

  /**
   * ExternalExtensionLoaderBase constructor.
   *
   * @codeCoverageIgnore
   */
  public function __construct() {
    $tasks = Yaml::parseFile(__DIR__ . '/tasks.yml');
    $this->class = str_replace('ExtensionLoader', 'Task', static::class);
    if (!class_exists($this->class)) {
      $this->class = ContextFileExternalTaskBase::class;
    }
    $configurations = $tasks[$this->class] ?? $tasks[$this->class];
    $this->name = $configurations['name'];
    $this->arguments = $configurations['arguments'] ?? $tasks[$this->class]['arguments'];
  }

  /**
   * {@inheritdoc}
   */
  public function load(ContainerBuilder $container): Definition {
    $task = $container->register('task.' . $this->name, $this->class);
    $task->addTag('grumphp.task', ['config' => $this->name]);
    if (empty($this->arguments)) {
      return $task;
    }
    foreach ($this->arguments as $argument) {
      $task->addArgument(new Reference($argument));
    }

    return $task;
  }

}
