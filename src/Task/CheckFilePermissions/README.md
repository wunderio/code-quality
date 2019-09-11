# grumphp-php-check-syntax

Check if the file permissions match that you've required. Dont' use yet. Work in progress!!!

### grumphp.yml:
````yml
parameters:
    tasks:
        check_file_permissions:
            run_on: ['.']
            extensions: [php, inc, module, phtml, php3, php4, php5]
            ignore_patterns: ['*/vendor/*','*/node_modules/*']
    extensions:
        - Wunderio\GrumPHP\Task\CheckFilePermissions\CheckFilePermissionsExtensionLoader
````
