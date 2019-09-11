<?php

namespace Wunderio\GrumPHP\Task;

use GrumPHP\Task\AbstractExternalTask;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Trait ContextFileTrait.
 *
 * @package Wunderio\GrumPHP\Task
 */
abstract class ContextFileExternalTaskBase extends AbstractExternalTask
{

  /**
   * {@inheritdoc}
   */
  public function getConfigurableOptions(): OptionsResolver
  {
    $resolver = new OptionsResolver();
    $resolver->setDefaults([
      'ignore_patterns' => ['*/vendor/*','*/node_modules/*', '*/core/*', '*/modules/contrib/*', '*/themes/contrib/*'],
      'extensions' => ['php', 'inc', 'module', 'install'],
      'run_on' => ['.']
    ]);
    $resolver->addAllowedTypes('ignore_patterns', ['array']);
    $resolver->setAllowedTypes('extensions', 'array');
    $resolver->setAllowedTypes('run_on', ['array']);
    return $resolver;
  }


  /**
   * Returns files.
   *
   * @param \GrumPHP\Task\Context\ContextInterface $context
   * @param array $config
   *
   * @return \GrumPHP\Collection\FilesCollection|null
   */
  protected function getFiles(ContextInterface $context, array $config)
  {
    $files = $context->getFiles()
      ->extensions($config['extensions'])
      ->paths($config['run_on'])
      ->notPaths($config['ignore_patterns']);
    if (\count($config['run_on'])) {
      $files = $files->paths($config['run_on']);
    }

    return $files;

  }
}
