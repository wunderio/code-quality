<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Wunderio\CodingStandards\Sniffs\Debug\DevelAndKintFunctionCallSniff;

return static function (ContainerConfigurator $containerConfigurator): void {
  $parameters = $containerConfigurator->parameters();

  // Register services.
  $services = $containerConfigurator->services();
  $services->set(DevelAndKintFunctionCallSniff::class);

  // Ignore patterns.
  $parameters->set(Option::EXCLUDE_PATHS, [
    '/vendor',
    '/node_modules/',
    '/core/',
    '/libraries/',
  ]);

  // Scan file extensions; [default: [php]]
  $parameters->set(Option::FILE_EXTENSIONS, ['php', 'inc', 'module', 'install', 'theme']);
};
