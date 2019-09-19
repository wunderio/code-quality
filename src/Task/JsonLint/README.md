# json_lint

Json linter.

### grumphp.yml:
````yml
parameters:
    tasks:
        json_lint:
            run_on: ['.']
            extensions: ['yaml', 'yml']
            ignore_patterns: ['*/vendor/*','*/node_modules/*']
            detect_key_conflicts: false
    extensions:
        - Wunderio\GrumPHP\Task\JsonLint\JsonLintExtensionLoader
````
