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
   * Test imports method.
   *
   * @covers \Wunderio\GrumPHP\Task\AbstractExternalExtensionLoader::import()
   */
  public function testImports(): void {
    $customLoader = new CustomTestExtensionLoader();
    $imports = $customLoader->imports();

    $expected_path = dirname(__DIR__) . '/src/Task/CustomTest/services.yaml';
    $this->assertEquals([$expected_path], iterator_to_array($imports));
  }

}

/**
 * Class CustomExtensionLoader.
 *
 * Extender class for test cases.
 */
class CustomTestExtensionLoader extends AbstractExternalExtensionLoader {}
