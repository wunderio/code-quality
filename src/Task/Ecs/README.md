# ecs

Easy coding standards implementation to better react on task context.

### grumphp.yml (with current defaults):
````yml
parameters:
    tasks:
        ecs:
            ignore_patterns:
                - '/vendor/'
                - '/node_modules/'
                - '/core/'
                - '/libraries/'
            extensions: ['php', 'inc', 'module', 'install', 'theme']
            run_on: ['.']
            clear-cache: false
            config: 'vendor/wunderio/code-quality/config/ecs.yml'
            no-progress-bar: true
            level: ~
    extensions:
        - Wunderio\GrumPHP\Task\Ecs\EcsExtensionLoader
````
