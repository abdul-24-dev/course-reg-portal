#!/bin/bash
set -e

PORT=${PORT:-80}

# Only modify port if not the default
if [ "$PORT" != "80" ]; then
    # Clear any existing Listen directives and add new one
    sed -i '/^Listen/d' /etc/apache2/ports.conf
    echo "Listen 0.0.0.0:$PORT" >> /etc/apache2/ports.conf
fi

# Start Apache
exec apache2-foreground
