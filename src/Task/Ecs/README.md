# ecs

Easy coding standards implementation to better react on task context.

### grumphp.yml:
````yml
parameters:
    tasks:
        ecs:
            run_on: ['.']
            extensions: [php, inc, module, phtml, php3, php4, php5]
            ignore_patterns: ['*/vendor/*','*/node_modules/*']
            config: ecs.yml
            clear-cache: false
            no-progress-bar: true
    extensions:
        - Wunderio\GrumPHP\Task\Ecs\EcsExtensionLoader
````
