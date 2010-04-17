#!/bin/zsh
################################################################################
# Jerity Unit Test Launcher
# Author: Nick Pope <nick@nickpope.me.uk>
################################################################################

# Default test script:
default='AllTests.php'

################################################################################
# Helper Functions {{{

function zlocate {
  if [[ -x '/usr/bin/which' ]]; then
    /usr/bin/which ${1} 2> /dev/null
  else
    builtin which ${1}
  fi
}

function is_readable {
  [[ -r $( zlocate ${1} ) ]]
}

function is_writable {
  [[ -w $( zlocate ${1} ) ]]
}

function is_executable {
  [[ -x $( zlocate ${1} ) ]]
}

function is_enabled {
  [[ ${1} -eq 1 ]]
}

function einfo {
  echo -e " \e[1;32m*\e[1;39m ${*}\e[0m"
}

function ewarn {
  echo -e " \e[1;33m*\e[1;39m ${*}\e[0m"
}

function eerror {
  echo -e " \e[1;31m*\e[1;39m ${*}\e[0m" > /dev/stderr
  exit 1
}

function extract_version {
  exec ${*} | grep -m 1 -o '[0-9]\+\.[0-9]\+\.[0-9]\+' | tr . ' '
}

# }}} Helper Functions
################################################################################

if ! is_executable phpunit; then
  eerror 'You need PHPUnit to run tests.'
fi

version=($(extract_version phpunit --version))
if [[ ${version[1]} < 3 || ${version[2]} < 3 ]]; then
  eerror 'You need PHPUnit >= 3.3.0'
fi

opts=(
--colors
--stderr # Workaround for header() errors
--syntax-check
)

einfo "Running Jerity Unit Tests ... [${1:-${default}}]"
echo
phpunit ${opts} "${1:-${default}}" 2>&1 | sed 's/^/   /'
echo

# vim:fdl=0
