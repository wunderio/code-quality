# grumphp-advanced-ecs

Easy coding standards implementation to better react on task context.

### grumphp.yml:
````yml
parameters:
    tasks:
        ecs:
          config: ecs.yml
          whitelist_patterns: ['web/modules/custom', 'web/themes/custom']
          triggered_by: [php, inc, module, install]
          clear-cache: false
          no-progress-bar: true
    extensions:
        - Wunderio\GrumPHP\Task\Ecs\EcsExtensionLoader
````
