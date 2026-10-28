default:
    @echo "No default task specified."
    just --list

# Runs the Development Kit Container
dkc:
    docker pull ghcr.io/leoborai/dkc:latest
    docker run -it --rm \
        -v $(pwd):/app \
        -w /app \
        ghcr.io/leoborai/dkc:latest

# Starts the Docker development environment (app, vite, mysql) with live reload
dev:
    UID=$(id -u) GID=$(id -g) docker compose -f docker/compose.yaml up --build

# Stops the Docker development environment
dev-down:
    docker compose -f docker/compose.yaml down

# Runs a command inside the app container (e.g. just dev-exec php artisan test)
dev-exec *args:
    UID=$(id -u) GID=$(id -g) docker compose -f docker/compose.yaml exec app {{args}}
