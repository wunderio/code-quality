<?php

declare(strict_types=1);

namespace Wunderio\GrumPHP\Drupal;

use Drupal\Core\DependencyInjection\ContainerNotInitializedException;
use mglaman\PHPStanDrupal\Drupal\Extension;
use Nette\Utils\Finder;

/**
 * Drupal autoloader base class for allowing Psalm to scan code.
 *
 * This was split into 2 classes to code complexity would not be too high.
 */
class DrupalAutoloaderBase {

  /**
   * Drupal autoloader.
   *
   * @var \Composer\Autoload\ClassLoader
   */
  protected $autoloader;

  /**
   * Path to Drupal root.
   *
   * @var string
   */
  protected $drupalRoot;

  /**
   * List of available modules.
   *
   * @var array
   */
  protected $moduleData = [];

  /**
   * List of available themes.
   *
   * @var array
   */
  protected $themeData = [];

  /**
   * Array of Drupal service mapping.
   *
   * @var array
   */
  protected $serviceMap = [];

  /**
   * Array of Drupal service yml files.
   *
   * @var array
   */
  protected $serviceYamls = [];

  /**
   * Array of service class providers.
   *
   * @var array
   */
  protected $serviceClassProviders = [];

  /**
   * ExtensionDiscovery object.
   *
   * @var \mglaman\PHPStanDrupal\Drupal\ExtensionDiscovery
   */
  protected $extensionDiscovery;

  /**
   * Array of Drupal namespaces.
   *
   * @var array
   */
  protected $namespaces = [];

  /**
   * Load legacy includes.
   */
  protected function loadLegacyIncludes(): void {
    /** @var \SplFileInfo $file */
    foreach (Finder::findFiles('*.inc')->in($this->drupalRoot . '/core/includes') as $file) {
      // phpcs:ignore PHPCS_SecurityAudit.Misc.IncludeMismatch.ErrMiscIncludeMismatchNoExt
      require_once $file->getPathname();
    }
  }

  /**
   * Add core test namespaces.
   */
  protected function addTestNamespaces(): void {
    $core_tests_dir = $this->drupalRoot . '/core/tests/Drupal';
    $this->namespaces['Drupal\\BuildTests'] = $core_tests_dir . '/BuildTests';
    $this->namespaces['Drupal\\FunctionalJavascriptTests'] = $core_tests_dir . '/FunctionalJavascriptTests';
    $this->namespaces['Drupal\\FunctionalTests'] = $core_tests_dir . '/FunctionalTests';
    $this->namespaces['Drupal\\KernelTests'] = $core_tests_dir . '/KernelTests';
    $this->namespaces['Drupal\\Tests'] = $core_tests_dir . '/Tests';
    $this->namespaces['Drupal\\TestSite'] = $core_tests_dir . '/TestSite';
    $this->namespaces['Drupal\\TestTools'] = $core_tests_dir . '/TestTools';
    $this->namespaces['Drupal\\Tests\\TestSuites'] = $this->drupalRoot . '/core/tests/TestSuites';
  }

  /**
   * Add Drupal module namespaces.
   */
  protected function addModuleNamespaces(): void {
    foreach ($this->moduleData as $module) {
      $module_name = $module->getName();
      $module_dir = $this->drupalRoot . '/' . $module->getPath();
      $this->namespaces["Drupal\\$module_name"] = $module_dir . '/src';

      // Extensions can have a \Drupal\Tests\extension namespace for test
      // cases, traits, and other classes such
      // as those that extend \Drupal\TestSite\TestSetupInterface.
      // @see drupal_phpunit_get_extension_namespaces()
      $module_test_dir = $module_dir . '/tests/src';
      if (is_dir($module_test_dir)) {
        $this->namespaces["Drupal\\Tests\\$module_name"] = $module_test_dir;
      }

      $servicesFileName = $module_dir . '/' . $module_name . '.services.yml';
      if (file_exists($servicesFileName)) {
        $this->serviceYamls[$module_name] = $servicesFileName;
      }
      $camelized = $this->camelize($module_name);
      $name = "{$camelized}ServiceProvider";
      $class = "Drupal\\{$module_name}\\{$name}";

      $this->serviceClassProviders[$module_name] = $class;
      $serviceId = "service_provider.$module_name.service_provider";
      $this->serviceMap[$serviceId] = ['class' => $class];
    }
  }

  /**
   * Add Drupal theme namespaces.
   */
  protected function addThemeNamespaces(): void {
    foreach ($this->themeData as $theme_name => $theme) {
      $theme_dir = $this->drupalRoot . '/' . $theme->getPath();
      $this->namespaces["Drupal\\$theme_name"] = $theme_dir . '/src';
    }
  }

  /**
   * Register PS4 namespaces.
   *
   * @param array $namespaces
   *   An array of namespaces to load.
   */
  protected function registerPs4Namespaces(array $namespaces): void {
    foreach ($namespaces as $prefix => $paths) {
      if (is_array($paths)) {
        foreach ($paths as $key => $value) {
          $paths[$key] = $value;
        }
      }
      $this->autoloader->addPsr4($prefix . '\\', $paths);
    }
  }

  /**
   * Loads in extensions (modules, themes, etc).
   *
   * Wrapper for \mglaman\PHPStanDrupal\Drupal\Extension->load().
   *
   * @param \mglaman\PHPStanDrupal\Drupal\Extension $extension
   *   Extension object.
   */
  protected function loadExtension(Extension $extension): void {
    try {
      $extension->load();
    }
    catch (\Throwable $e) {
      // Something prevented the extension file from loading.
      // This can happen when drupal_get_path or drupal_get_filename are used
      // outside the scope of a function.
    }
  }

  /**
   * Wrapper for require_once() that catches errors.
   *
   * @param string $path
   *   Path to file.
   */
  protected function loadAndCatchErrors(string $path): void {
    try {
      // phpcs:ignore PHPCS_SecurityAudit.Misc.IncludeMismatch.ErrMiscIncludeMismatchNoExt
      require_once $path;
    }
    catch (ContainerNotInitializedException $e) {
      $path = str_replace(dirname($this->drupalRoot) . '/', '', $path);
      // This can happen when drupal_get_path or drupal_get_filename are used
      // outside the scope of a function.
      @trigger_error("$path invoked the Drupal container outside of the scope of a function or class method. It was not loaded.", E_USER_WARNING);
    }
    catch (\Throwable $e) {
      $path = str_replace(dirname($this->drupalRoot) . '/', '', $path);
      // Something prevented the extension file from loading.
      @trigger_error("$path failed loading due to {$e->getMessage()}", E_USER_WARNING);
    }
  }

  /**
   * Camelizes a string.
   *
   * @todo Should we use method from Symfony\Component\DependencyInjection\Container instead?
   *
   * @param string $id
   *   A string to camelize.
   *
   * @return string
   *   The camelized string.
   */
  protected function camelize(string $id): string {
    return strtr(ucwords(strtr($id, ['_' => ' ', '.' => '_ ', '\\' => '_ '])), [' ' => '']);
  }

}
