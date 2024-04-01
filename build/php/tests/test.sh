#!/bin/bash

# Check if PHP binaries directory argument is provided
if [ -z "$2" ]; then
    echo "Error: PHP binaries directory argument is missing."
    exit 1
fi

# Set PHP binaries directory
export PHP_BINARIES="$2"
chmod +x "$PHP_BINARIES"

# Get the directory of the script
DIR="$(cd -P "$( dirname "${BASH_SOURCE[0]}" )" && pwd)"
export TEST_DIR="$DIR/$1/"

# Count the number of tests
TEST_NUMBER=("$TEST_DIR"*)
TEST_NUMBER=${#TEST_NUMBER[@]}

set +e

INCREMENT=0
FAILED=0

# Change directory to the test directory
cd "$TEST_DIR"
echo "Doing tests on $TEST_DIR"

# Iterate over test files
for f in *; do
    INCREMENT=$((INCREMENT+1))
    echo -n "[$INCREMENT/$TEST_NUMBER] $f ... "
    chmod +x "$f"
    "./$f"
    STATUS=$?
    if [ $STATUS != 0 ]; then
        echo "FAILED!"
        FAILED=$((FAILED+1))
    else
        echo "OK"
    fi
done

echo "Ran $INCREMENT tests, $FAILED failed."

exit 0
