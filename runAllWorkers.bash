#!/bin/bash

# This script runs all the workers

laminas worker:enums & 
laminas worker:token &
laminas worker:unisender-api-key & 
laminas worker:webhooks &
laminas worker:contacts-sync
wait
