#!/bin/bash

# This script runs all the workers
# To run this script, you need to vendor/bin/ in your PATH - export "PATH=$PATH:/path/to/vendor/bin/"

# Ждем пока добавятся все токены и unisender-api-key, т.к следующие воркеры зависят от них
laminas worker:token &
laminas worker:enums & 
laminas worker:unisender-api-key & 
wait

laminas worker:webhooks &
laminas worker:contacts-sync &
wait
