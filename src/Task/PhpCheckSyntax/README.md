# php_check_syntax

Run `php -l` on the code.

### grumphp.yml (with current defaults):
````yml
parameters:
    tasks:
        php_check_syntax:
            ignore_patterns:
                - '/vendor/'
                - '/node_modules/'
                - '/core/'
                - '/libraries/'
            extensions: ['php', 'inc', 'module', 'install']
            run_on: '.']
            parallelism: 100
    extensions:
        - Wunderio\GrumPHP\Task\PhpCheckSyntaxTask\PhpCheckSyntaxExtensionLoader
````
