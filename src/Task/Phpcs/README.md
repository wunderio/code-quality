# phpcs

Check Drupal code against coding standards and security standards.

### grumphp.yml (with current defaults):
````yml
parameters:
    tasks:
        phpcs:
            ignore_patterns: 
                - '/vendor/'
                - '/node_modules/'
                - '/core/'
                - '/libraries/'
            extensions: ['php', 'inc', 'module', 'install', 'theme']
            run_on: ['.']
            standard:
                - 'vendor/wunderio/code-quality/config/phpcs.xml'
                - 'vendor/wunderio/code-quality/config/phpcs-security.xml'
            tab_width: ~
            encoding: ~
            sniffs: []
            severity: ~
            error_severity:  ~
            warning_severity: ~
            report: 'full'
            report_width: 120
            exclude: []
            parallel: 20
    extensions:
        - Wunderio\GrumPHP\Task\PhpCompatibilityTask\PhpCompatibilityExtensionLoader
````
