# psalm

Psalm is a static analysis tool that attempts to dig into your program and
find as many type-related bugs as possible..

### grumphp.yml (with current defaults):
````yml
grumphp:
    tasks:
        psalm:
            config: 'psalm.xml'
            ignore_patterns:
                - '/vendor/'
                - '/node_modules/'
                - '/core/'
                - '/libraries/'
            extensions: ['php', 'inc', 'module', 'install', 'theme']
            run_on: ['web/modules/custom', 'web/themes/custom']
            no_cache: false
            report: ~
            output_format: null
            threads: 1
            show_info: false
    extensions:
        - Wunderio\GrumPHP\Task\Psalm\PsalmExtensionLoader
````
