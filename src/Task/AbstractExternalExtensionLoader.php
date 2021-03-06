<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task;

use GrumPHP\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Yaml\Yaml;

/**
 * Class AbstractExternalExtensionLoader.
 *
 * Provides a base implementation for \GrumPHP\Extension\ExtensionInterface.
 *
 * @package Wunderio\GrumPHP\Task
 */
abstract class AbstractExternalExtensionLoader implements ExtensionInterface {

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
   * AbstractExternalExtensionLoader constructor.
   */
  public function __construct() {
    $tasks = Yaml::parseFile(__DIR__ . '/tasks.yml');
    $class_name = str_replace('ExtensionLoader', '', static::class);
    $this->class = $class_name . 'Task';
    $default_configuration = $tasks['default'];
    unset($default_configuration['name']);
    $configurations = $tasks[$this->class] ?? $default_configuration;
    $class_name = explode('\\', $class_name);
    $default_name = strtolower(preg_replace('/\B([A-Z])/', '_$1', end($class_name)));
    $this->name = $configurations['name'] ?? $default_name;
    $this->arguments = $configurations['arguments'] ?? $default_configuration['arguments'];
  }

  /**
   * {@inheritdoc}
   */
  public function load(ContainerBuilder $container): void {
    $task = $container->register('task.' . $this->name, $this->class);
    if (!empty($this->arguments)) {
      foreach ($this->arguments as $argument) {
        $task->addArgument(new Reference($argument));
      }
    }
    $task->addTag('grumphp.task', ['task' => $this->name]);
  }

}
