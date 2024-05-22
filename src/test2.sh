#!/bin/bash

set -e
set -u
export PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin

#
# Filter files by mime.
#
# Usage: acquire_files_mime 'text/x-shellscript' file1 file2 fileN
#
function acquire_files_mime {
  local files="${@:2}"
  local mime="$1"
  local res=""

  for file in $files
  do
    local valid_file=$(file --mime-type "$file" \
    | grep -E ':\s*'"$mime"'$' \
    | sed -e 's/:.*//')
    if [ ! -z "$valid_file" ]
    then
      res="$res$valid_file
"
    fi
  done
  echo "$res"
}

#
# Functiont to check file permissions.
#
# Usage: check_perms 755 file_name
#
function check_perms {
  local perms_expect="$1"
  local file="$2"
  local result=""

  case "$(uname -s)" in
    Darwin*) # mac
      stat_flags='-f %p'
      perms_expect="100$perms_expect"
      ;;
    Linux*|*) # linux or the rest
      stat_flags='-c %a'
      ;;
  esac

  perms_current="$(stat $stat_flags "$file")"
  if [ "$perms_current" != "$perms_expect" ]; then
    result="$result
File: $file
Current permissions:  $perms_current
Expected permissions: $perms_expect
-"
  fi

  printf "$result"
}

#
# Check for shell script permissions.
#
function check_sh_perms {
  local files="$@"
  local res=""
  local files_sh=$(acquire_files_mime 'text/x-shellscript' "$files")


  for file in $files_sh
  do
    res="$res$(check_perms 755 "$file")"
  done

  if [ ! -z "$res" ]
  then
    echo "Fix file permissions for the following shell scripts:"
    echo "$res"
    exit 1
  else
    exit 0
  fi
}

check_sh_perms "$@"
