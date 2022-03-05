#!/bin/bash
# run as ./run-in-container.sh bash -c 'command'

# docker run --rm -it -v $(pwd):/app php8.0-afi-ubuntu  "$@"
# docker run --rm -it -v $(pwd):/app php8.1-afi-ubuntu  "$@"

if [[ "$1" = "" ]];then
	echo "Using $0 php8.0 | php8.1"
	exit 1
fi

IMAGE="${1}-afi-ubuntu"
docker run --rm -it -v $(pwd):/app "$IMAGE" "${@:2}"