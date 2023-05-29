<?php

declare(strict_types=1);

namespace Wunderio\GrumPHP\Drupal;

use DrupalFinder\DrupalFinder;
use Drush\Drush;
use Nette\Utils\Finder;
use PHPUnit\Framework\Test;
use Symfony\Component\Yaml\Yaml;
use mglaman\PHPStanDrupal\Drupal\Extension;
use mglaman\PHPStanDrupal\Drupal\ExtensionDiscovery;

/**
 * Drupal autoloader for allowing Psalm to scan code.
 *
 * Code re-used from https://github.com/mglaman/phpstan-drupal/blob/main/src/Drupal/DrupalAutoloader.php.
 */
class DrupalAutoloader extends DrupalAutoloaderBase {

  /**
   * Load in Drupal code from defined path.
   *
   * @param string $drupalRoot
   *   Path to Drupal root.
   */
  public function register(string $drupalRoot): void {
    $finder = new DrupalFinder();
    $finder->locateRoot($drupalRoot);

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

    $this->registerIncludeModuleFiles();
    $this->registerIncludeThemeFiles();

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

    $this->registerIncludeServicesFromYamls();
  }

  /**
   * Helper for register() method to include module files.
   */
  private function registerIncludeModuleFiles() {
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
  }

  /**
   * Helper for register() method to include theme files.
   */
  private function registerIncludeThemeFiles() {
    foreach ($this->themeData as $extension) {
      $this->loadExtension($extension);
      $theme_dir = $this->drupalRoot . '/' . $extension->getPath();
      $theme_settings_file = $theme_dir . '/theme-settings.php';
      if (file_exists($theme_settings_file)) {
        $this->loadAndCatchErrors($theme_settings_file);
      }
    }
  }

  /**
   * Helper for register() method to include services.
   *
   * @todo Fix this method not being properly used - see https://github.com/wunderio/code-quality/issues/96 for more info.
   */
  private function registerIncludeServicesFromYamls() {
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

}
