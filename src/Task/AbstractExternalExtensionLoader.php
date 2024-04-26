<?php

declare(strict_types=1);

namespace Wunderio\GrumPHP\Task;

use GrumPHP\Extension\ExtensionInterface;

/**
 * Class AbstractExternalExtensionLoader.
 *
 * Provides a base implementation for \GrumPHP\Extension\ExtensionInterface.
 *
 * @package Wunderio\GrumPHP\Task
 */
abstract class AbstractExternalExtensionLoader implements ExtensionInterface {

  /**
   * {@inheritdoc}
   */
  public function imports(): iterable {
    $class_name = str_replace('ExtensionLoader', '', static::class);
    $class_exploded = explode('\\', $class_name);
    yield dirname(__DIR__) . '/Task/' . end($class_exploded) . '/services.yaml';
  }

}
