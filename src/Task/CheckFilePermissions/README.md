# php_check_syntax

Check if the file permissions match that you've required.


### grumphp.yml (with current defaults):
````yml
parameters:
    tasks:
        check_file_permissions:
            ignore_patterns:
                - '/vendor/'
                - '/node_modules/'
                - '/core/'
                - '/libraries/'
            extensions:
              defaults: ['sh']
            run_on:
              defaults: ['.']
    extensions:
        - Wunderio\GrumPHP\Task\CheckFilePermissions\CheckFilePermissionsExtensionLoader
````
