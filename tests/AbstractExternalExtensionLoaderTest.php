<?php

/**
 * @file
 * Tests covering AbstractExternalExtensionLoader.
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Wunderio\GrumPHP\Task\AbstractExternalExtensionLoader;

/**
 * Class AbstractExternalExtensionLoaderTest.
 *
 * Tests covering AbstractExternalExtensionLoader class.
 */
final class AbstractExternalExtensionLoaderTest extends TestCase {

  /**
   * Test class constructor.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractExternalExtensionLoader::__construct
   */
  public function testSetsConfigurationFromYaml(): void {
    $customLoader = new CustomTestExtensionLoader();
    $this->assertEquals('custom_test', $customLoader->name);
    $this->assertEquals(['process_builder', 'formatter.raw_process'], $customLoader->arguments);
    $this->assertEquals('CustomTestTask', $customLoader->class);
  }

}

/**
 * Class CustomExtensionLoader.
 *
 * Extender class for test cases.
 */
class CustomTestExtensionLoader extends AbstractExternalExtensionLoader {}
