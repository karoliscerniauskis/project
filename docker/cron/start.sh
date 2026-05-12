#!/bin/bash

# Start cron in background
cron

# Run outbox processor (keeps container running)
while true; do
    echo "Starting outbox processor..."
    cd /app && /usr/local/bin/php bin/console app:outbox:process
    echo "Outbox processor stopped. Restarting in 5 seconds..."
    sleep 5
done
