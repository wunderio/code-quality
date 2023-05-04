#!/bin/bash

#
# Helper script to run GrumPHP.
#

set -exuo pipefail
export PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/app/vendor/bin

cd /app
./vendor/bin/grumphp "$@"
