FROM railway/nixpacks:latest as builder
WORKDIR /app
COPY . .
RUN nix-shell -p php php82Packages.composer --run "composer install --no-dev"

FROM railway/nixpacks:latest
WORKDIR /app
COPY --from=builder /app .
ENV PORT=8080
EXPOSE 8080
CMD ["php", "-S", "0.0.0.0:8080"]
