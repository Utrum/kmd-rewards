#!/bin/bash

# Warning: "--name" must be set to "kmd-rewards",
# underline not allowed by Apache.

docker run \
  -d \
  --rm \
  --name kmd-rewards \
  -p 127.0.0.1:8080:8080 \
  kmdplatform/kmd-rewards

