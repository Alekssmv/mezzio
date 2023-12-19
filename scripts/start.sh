#!/bin/bash

# Start ngrok
./ngrok http 82 &

# Start your server
php -S 0.0.0.0:82 -t public