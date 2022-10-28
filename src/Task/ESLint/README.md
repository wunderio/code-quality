# ESLint

Check js code against coding standards and security standards. By default Drupal rules are loaded.

### grumphp.yml (with current defaults):
````yml
parameters:
    tasks:
        eslint:
          ignore_patterns:
            - '**/vendor/**'
            - '**/node_modules/**'
            - '**/core/**'
            - '**/libraries/**'
            - '**/contrib/**'
          extensions: ['js', 'jsx', 'ts', 'tsx', 'vue']
          run_on: [ 'web/modules/custom', 'web/themes/custom' ]
          bin: 'node_modules/.bin/eslint'
          config: 'web/core/.eslintrc.passing.json'
          debug: false
          format: ~
          max_warnings: ~
          no_eslintrc: false
          quiet: ~
    extensions:
        - Wunderio\GrumPHP\Task\PhpCompatibilityTask\PhpCompatibilityExtensionLoader
````


