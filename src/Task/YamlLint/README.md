# yaml_lint

YAML linter.

### grumphp.yml (with current defaults):
````yml
parameters:
    tasks:
        yaml_lint:
            ignore_patterns:
                - '/vendor/'
                - '/node_modules/'
                - '/core/'
                - '/libraries/'
            extensions: ['yaml', 'yml']
            run_on: ['.']
            object_support: false
            exception_on_invalid_type: false
            parse_constant: false
            parse_custom_tags: true
    extensions:
        - Wunderio\GrumPHP\Task\YamlLint\YamlLintExtensionLoader
````
