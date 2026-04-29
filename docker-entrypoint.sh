#!/bin/bash
set -e

# Use Railway's PORT or default to 8080
PORT=${PORT:-8080}

# Configure Apache to listen on the correct port
sed -i "s/Listen 80/Listen 0.0.0.0:$PORT/" /etc/apache2/ports.conf
sed -i "s/:80/:$PORT/" /etc/apache2/sites-enabled/000-default.conf

# Start Apache
exec apache2-foreground
