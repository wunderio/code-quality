# php_check_syntax

Run `php -l` on the code.

### grumphp.yml:
````yml
parameters:
    tasks:
        php_check_syntax:
            run_on: ['.']
            extensions: [php, inc, module, phtml, php3, php4, php5]
            ignore_patterns: ['*/vendor/*','*/node_modules/*']
    extensions:
        - Wunderio\GrumPHP\Task\PhpCheckSyntaxTask\PhpCheckSyntaxExtensionLoader
````
