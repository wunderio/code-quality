<?php

namespace Wunderio\GrumPHP\Task\PhpCompatibility;

use GrumPHP\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class PhpCompatibilityExtensionLoader.
 *
 * @package Wunderio\GrumPHP\Task\PhpCompatibilityTask
 */
class PhpCompatibilityExtensionLoader implements ExtensionInterface {

  /**
   * {@inheritdoc}
   */
  public function load(ContainerBuilder $container) {
    return $container->register('task.php_compatibility', PhpCompatibilityTask::class)
      ->addArgument(new Reference('config'))
      ->addArgument(new Reference('process_builder'))
      ->addArgument(new Reference('formatter.raw_process'))
      ->addTag('grumphp.task', ['config' => 'php_compatibility']);
  }

}
