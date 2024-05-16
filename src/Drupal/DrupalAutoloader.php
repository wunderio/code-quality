<?php

declare(strict_types=1);

namespace Wunderio\GrumPHP\Drupal;

use Drupal\Core\DependencyInjection\ContainerNotInitializedException;
use DrupalFinder\DrupalFinderComposerRuntime;
use Drush\Drush;
use mglaman\PHPStanDrupal\Drupal\Extension;
use mglaman\PHPStanDrupal\Drupal\ExtensionDiscovery;
use Nette\Utils\Finder;
use PHPUnit\Framework\Test;
use Symfony\Component\Yaml\Yaml;

/**
 * Drupal autoloader for allowing Psalm to scan code.
 *
 * Code re-used from https://github.com/mglaman/phpstan-drupal/blob/main/src/Drupal/DrupalAutoloader.php.
 */
class DrupalAutoloader {

  /**
   * Drupal autoloader.
   *
   * @var \Composer\Autoload\ClassLoader
   */
  private $autoloader;

  /**
   * Path to Drupal root.
   *
   * @var string
   */
  private $drupalRoot;

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
  private $serviceMap = [];

  /**
   * Array of Drupal service yml files.
   *
   * @var array
   */
  private $serviceYamls = [];

  /**
   * Array of service class providers.
   *
   * @var array
   */
  private $serviceClassProviders = [];

  /**
   * ExtensionDiscovery object.
   *
   * @var \mglaman\PHPStanDrupal\Drupal\ExtensionDiscovery
   */
  private $extensionDiscovery;

  /**
   * Array of Drupal namespaces.
   *
   * @var array
   */
  private $namespaces = [];

  /**
   * Load in Drupal code from defined path.
   *
   * @param string $drupalRoot
   *   Path to Drupal root.
   */
  public function register(string $drupalRoot): void {
    $finder = new DrupalFinderComposerRuntime();

    $drupalRoot = $finder->getDrupalRoot();
    $drupalVendorRoot = $finder->getVendorDir();
    if (!(bool) $drupalRoot || !(bool) $drupalVendorRoot) {
      throw new \RuntimeException("Unable to detect Drupal at $drupalRoot");
    }

    $this->drupalRoot = $drupalRoot;

    $this->autoloader = include $drupalVendorRoot . '/autoload.php';

    $this->serviceYamls['core'] = $drupalRoot . '/core/core.services.yml';
    $this->serviceClassProviders['core'] = '\Drupal\Core\CoreServiceProvider';
    $this->serviceMap['service_provider.core.service_provider'] = ['class' => $this->serviceClassProviders['core']];

    $this->extensionDiscovery = new ExtensionDiscovery($this->drupalRoot);
    $this->extensionDiscovery->setProfileDirectories([]);
    $profiles = $this->extensionDiscovery->scan('profile');
    // phpcs:ignore PHPCS_SecurityAudit.BadFunctions.CallbackFunctions.WarnCallbackFunctions
    $profile_directories = array_map(static function (Extension $profile) : string {
      return $profile->getPath();
    }, $profiles);
    $this->extensionDiscovery->setProfileDirectories($profile_directories);

    $this->moduleData = array_merge($this->extensionDiscovery->scan('module'), $profiles);
    // phpcs:ignore PHPCS_SecurityAudit.BadFunctions.CallbackFunctions.WarnCallbackFunctions
    usort($this->moduleData, static function (Extension $a, Extension $b) {
      return strpos($a->getName(), '_test') !== FALSE ? 10 : 0;
    });
    $this->themeData = $this->extensionDiscovery->scan('theme');
    $this->addTestNamespaces();
    $this->addModuleNamespaces();
    $this->addThemeNamespaces();
    $this->registerPs4Namespaces($this->namespaces);
    $this->loadLegacyIncludes();

    // @todo stop requiring the bootstrap.php and just copy what is needed.
    if (interface_exists(Test::class)) {
      require_once $this->drupalRoot . '/core/tests/bootstrap.php';

      // class_alias is not supported by OptimizedDirectorySourceLocator
      // or AutoloadSourceLocator, so we manually load this PHPUnit
      // compatibility trait that exists in Drupal 8.
      $phpunitCompatTraitFilepath = $this->drupalRoot . '/core/tests/Drupal/Tests/PhpunitCompatibilityTrait.php';
      if (file_exists($phpunitCompatTraitFilepath)) {
        // phpcs:ignore PHPCS_SecurityAudit.Misc.IncludeMismatch.ErrMiscIncludeMismatchNoExt
        require_once $phpunitCompatTraitFilepath;
        $this->autoloader->addClassMap(['Drupal\\Tests\\PhpunitCompatibilityTrait' => $phpunitCompatTraitFilepath]);
      }
    }

    foreach ($this->moduleData as $extension) {
      $this->loadExtension($extension);

      $module_name = $extension->getName();
      $module_dir = $this->drupalRoot . '/' . $extension->getPath();
      // Add .install.
      if (file_exists($module_dir . '/' . $module_name . '.install')) {
        $ignored_install_files = [
          'entity_test',
          'entity_test_update',
          'update_test_schema',
        ];
        if (!in_array($module_name, $ignored_install_files, TRUE)) {
          $this->loadAndCatchErrors($module_dir . '/' . $module_name . '.install');
        }
      }
      // Add .post_update.php.
      if (file_exists($module_dir . '/' . $module_name . '.post_update.php')) {
        $this->loadAndCatchErrors($module_dir . '/' . $module_name . '.post_update.php');
      }
      // Add misc .inc that are magically allowed via hook_hook_info.
      $magic_hook_info_includes = [
        'views',
        'views_execution',
        'tokens',
        'search_api',
        'pathauto',
      ];
      foreach ($magic_hook_info_includes as $hook_info_include) {
        if (file_exists($module_dir . "/$module_name.$hook_info_include.inc")) {
          $this->loadAndCatchErrors($module_dir . "/$module_name.$hook_info_include.inc");
        }
      }
    }
    foreach ($this->themeData as $extension) {
      $this->loadExtension($extension);
      $theme_dir = $this->drupalRoot . '/' . $extension->getPath();
      $theme_settings_file = $theme_dir . '/theme-settings.php';
      if (file_exists($theme_settings_file)) {
        $this->loadAndCatchErrors($theme_settings_file);
      }
    }

    if (class_exists(Drush::class)) {
      $reflect = new \ReflectionClass(Drush::class);
      if ($reflect->getFileName() !== FALSE) {
        $levels = 2;
        if (Drush::getMajorVersion() < 9) {
          $levels = 3;
        }
        $drushDir = dirname($reflect->getFileName(), $levels);
        /** @var \SplFileInfo $file */
        foreach (Finder::findFiles('*.inc')->in($drushDir . '/includes') as $file) {
          // phpcs:ignore PHPCS_SecurityAudit.Misc.IncludeMismatch.ErrMiscIncludeMismatchNoExt
          require_once $file->getPathname();
        }
      }
    }

    foreach ($this->serviceYamls as $extension => $serviceYaml) {
      $yaml = Yaml::parseFile($serviceYaml);
      // Weed out service files which only provide parameters.
      if (!isset($yaml['services']) || !is_array($yaml['services'])) {
        continue;
      }
      foreach ($yaml['services'] as $serviceId => $serviceDefinition) {
        // Check if this is an alias shortcut.
        // @link https://symfony.com/doc/4.4/service_container/alias_private.html#aliasing
        if (is_string($serviceDefinition)) {
          $serviceDefinition = [
            'alias' => str_replace('@', '', $serviceDefinition),
          ];
        }
        // Prevent \Nette\DI\ContainerBuilder::completeStatement from
        // array_walk_recursive into the arguments
        // and thinking these are real services for PHPStan's container.
        if (isset($serviceDefinition['arguments']) && is_array($serviceDefinition['arguments'])) {
          // phpcs:ignore PHPCS_SecurityAudit.BadFunctions.CallbackFunctions.WarnCallbackFunctions
          array_walk($serviceDefinition['arguments'], function (&$argument) : void {
            if (is_array($argument) || !is_string($argument)) {
              // @todo fix for @http_kernel.controller.argument_metadata_factory
              $argument = '';
            }
            else {
              $argument = str_replace('@', '', $argument);
            }
          });
        }
        // @todo sanitize "calls" and "configurator" and "factory"
        /*
         * jsonapi.params.enhancer:
         * class: Drupal\jsonapi\Routing\JsonApiParamEnhancer
         * calls:
         * - [setContainer, ['@service_container']]
         * tags:
         * - { name: route_enhancer }
         */
        unset($serviceDefinition['tags'], $serviceDefinition['calls'], $serviceDefinition['configurator'], $serviceDefinition['factory']);
        $this->serviceMap[$serviceId] = $serviceDefinition;
      }
    }
  }

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
