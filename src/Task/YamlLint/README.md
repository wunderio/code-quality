# yaml_lint

YAML linter.

### grumphp.yml:
````yml
parameters:
    tasks:
        yaml_lint:
            run_on: ['.']
            extensions: ['yaml', 'yml']
            ignore_patterns: ['*/vendor/*','*/node_modules/*']
            object_support: false
            exception_on_invalid_type: false
            parse_constant:  false
            parse_custom_tags: false
    extensions:
        - Wunderio\GrumPHP\Task\YamlLint\YamlLintExtensionLoader
````
