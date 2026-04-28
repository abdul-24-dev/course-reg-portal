FROM php:8.2-alpine

WORKDIR /app

# Install required PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy application files
COPY . .

# Set environment variables
ENV PORT=8080

EXPOSE 8080

# Start PHP built-in server
CMD ["php", "-S", "0.0.0.0:8080"]
