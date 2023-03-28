#!/bin/bash

#
# Helper script to run drupal tests.
#

set -euo pipefail
export PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/app/vendor/bin
install -d /app/public/simpletest/browser_output
cd /app/
XDEBUG_MODE=coverage php \
  /app/vendor/bin/phpunit \
  --configuration /app/phpunit.xml \
  "$@"
