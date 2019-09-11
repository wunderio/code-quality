<?php

namespace Wunderio\GrumPHP\Task\PhpCheckSyntax;

use GrumPHP\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class PhpCheckSyntaxExtensionLoader.
 *
 * @package Wunderio\GrumPHP\Task\PhpCheckSyntaxTask
 */
class PhpCheckSyntaxExtensionLoader implements ExtensionInterface
{
  /**
   * {@inheritdoc}
   */
  public function load(ContainerBuilder $container)
  {
    return $container->register('task.php_check_syntax', PhpCheckSyntaxTask::class)
      ->addArgument(new Reference('config'))
      ->addArgument(new Reference('process_builder'))
      ->addArgument(new Reference('formatter.raw_process'))
      ->addTag('grumphp.task', ['config' => 'php_check_syntax']);
  }
}
