<?php

use Wunderio\GrumPHP\Drupal\DrupalAutoloader;

$vendor_dir = dirname(dirname(dirname(__FILE__)));
$base_dir = dirname($vendor_dir);

$o = new DrupalAutoloader();
$o->register($base_dir);
