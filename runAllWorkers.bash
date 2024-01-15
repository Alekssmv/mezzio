#!/bin/bash

# This script runs all the workers
# To run this script, you need to vendor/bin/ in your PATH - export "PATH=$PATH:/path/to/vendor/bin/"

laminas worker:enums & 
laminas worker:token &
laminas worker:unisender-api-key & 
laminas worker:webhooks &
laminas worker:contacts-sync
wait
