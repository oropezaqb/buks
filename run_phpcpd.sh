#!/bin/bash

# Path to the .phpcpdignore file
IGNORE_FILE=".phpcpdignore"

# Initialize the exclude parameters
EXCLUDES=""

# Read the ignore file line by line
while IFS= read -r line; do
    # Skip empty lines and comments
    if [[ -n "$line" && ! "$line" =~ ^# ]]; then
        EXCLUDES+="--exclude=$line "
    fi
done < "$IGNORE_FILE"

# Run phpcpd with the excludes
phpcpd app bootstrap config database routes tests $EXCLUDES src/
