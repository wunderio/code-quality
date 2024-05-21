# php_compatibility

Check if files are compatible with X version of PHP.

### grumphp.yml (with current defaults):
````yml
parameters:
    tasks:
        php_compatibility:
            ignore_patterns:
                - '/vendor/'
                - '/node_modules/'
                - '/core/'
                - '/libraries/'
            extensions: ['php', 'inc', 'module', 'install', 'theme']
            run_on: ['.']
            testVersion: '8.1'
            standard: 'PHPCompatibility'
            parallel: 20
    extensions:
        - Wunderio\GrumPHP\Task\PhpCompatibilityTask\PhpCompatibilityExtensionLoader
````
