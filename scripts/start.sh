#!/bin/bash

trap "killall ngrok" EXIT

/ngrok http --domain=reasonably-coherent-weasel.ngrok-free.app 8080 &

composer serve






