#!/bin/sh

echo "Starting cron service..."
cron

echo "Installed crontab:"
crontab -l

echo "Starting outbox processor loop..."
while true; do
    echo "Processing outbox..."
    cd /app && APP_ENV=prod APP_DEBUG=0 /usr/local/bin/php bin/console app:outbox:process || echo "Outbox processor failed. Retrying in 5 seconds..."
    echo "Outbox processor finished. Restarting in 5 seconds..."
    sleep 5
done
