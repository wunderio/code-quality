# grumphp-php-compatibility

Check if files are compatible with X version of PHP.

### grumphp.yml:
````yml
parameters:
    tasks:
        php_compatibility:
            testVersion: "7.3"
            triggered_by:  [php, inc, module, install]
    extensions:
        - Wunderio\GrumPHP\Task\PhpCompatibilityTask\PhpCompatibilityExtensionLoader
````
