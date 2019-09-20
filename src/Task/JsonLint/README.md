# json_lint

Json linter.

### grumphp.yml (with current defaults):
````yml
parameters:
    tasks:
        json_lint:
            ignore_patterns:
                - '/vendor/'
                - '/node_modules/'
                - '/core/'
                - '/libraries/'
            extensions: ['json', 'lock']
            run_on: ['.']
            detect_key_conflicts: false
    extensions:
        - Wunderio\GrumPHP\Task\JsonLint\JsonLintExtensionLoader
````
