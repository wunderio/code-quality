# phpstan_check_deprecation

Check Drupal code against deprecation rules.

### grumphp.yml (with current defaults):
````yml
parameters:
    tasks:
        phpstan_check_deprecation:
            ignore_patterns: 
                - '/vendor/'
                - '/node_modules/'
                - '/core/'
                - '/libraries/'
            extensions: ['php', 'inc', 'module', 'install', 'theme']
            run_on: ['.']
            autoload_file:
              defaults: ~
              allowed_types: ['string', 'null']
            configuration:
              defaults: 'phpstan.neon'
              allowed_types: ['string', 'null']
            memory_limit:
              defaults: ~
              allowed_types: ['string', 'null']
            level:
              defaults: ~
              allowed_types: ['string', 'null']
    extensions:
        - Wunderio\GrumPHP\Task\PhpstanCheckDeprecation\PhpstanCheckDeprecationExtensionLoader
````
