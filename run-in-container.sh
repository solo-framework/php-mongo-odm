#!/bin/bash
# run as ./run-in-container.sh bash -c 'command'
docker run --rm -it -v $(pwd):/app php8.0-afi "$@"
