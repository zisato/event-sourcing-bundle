#!/usr/bin/env bash

method="$1"
shift

exitIfInvalidExitCode() {
    if [[ $1 -ne 0 ]]
    then
        exit $1
    fi
}

generateCoverage() {
    php bin/phpcov merge build/coverage --html build/coverage/merged/html
    exitIfInvalidExitCode $?
}

unit() {
    php bin/phpunit --testsuite=unit --no-coverage $*
    exitIfInvalidExitCode $?
}

unitCoverage() {
    php bin/phpunit --testsuite=unit $*
    exitIfInvalidExitCode $?
}

testAll() {
    unit
}

testAllCoverage() {
    unitCoverage
    generateCoverage
}

case "$method" in
  all)
    testAll
    ;;
  unit)
    unit $*
    ;;
  all-coverage)
    testAllCoverage
    ;;
  unit-coverage)
    unitCoverage $*
    generateCoverage
    ;;
  *)
    testAll
esac

exit 0
