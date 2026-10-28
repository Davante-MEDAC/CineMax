FROM php:8.4-cli-bookworm

ARG UID=1000
ARG GID=1000

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip curl ca-certificates libicu-dev libzip-dev default-mysql-client \
    && docker-php-ext-install -j"$(nproc)" intl pdo_mysql zip pcntl \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN groupadd -g "$GID" dev 2>/dev/null || true \
    && useradd -m -u "$UID" -g "$GID" -s /bin/bash dev 2>/dev/null || true \
    && mkdir -p /app/vendor /app/node_modules \
    && chown -R "$UID:$GID" /app

COPY docker/entrypoint.sh /usr/local/bin/dev-entrypoint
RUN chmod +x /usr/local/bin/dev-entrypoint

USER $UID:$GID
WORKDIR /app
ENTRYPOINT ["dev-entrypoint"]
