<p align="center">
  <img src="assets/logo.svg" alt="CineMáx" width="400">
</p>

# CineMáx

Plantilla base para iniciar proyectos con **Laravel 13**, lista para desarrollo local o con Docker.

## Stack

- PHP 8.3+ (la imagen Docker usa PHP 8.4)
- Laravel 13
- Vite 8 + Tailwind CSS 4
- MySQL 8.4
- PHPUnit 12 y Laravel Pint

## Requisitos

Para desarrollo local:

- [PHP 8.3+](https://www.php.net/downloads) con las extensiones `intl`, `pdo_mysql` y `zip`
- [Composer 2](https://getcomposer.org/download/)
- [Node.js 22+](https://nodejs.org/) y npm
- [MySQL 8](https://dev.mysql.com/downloads/) (o cambia `DB_CONNECTION=sqlite` en `.env`)

Para desarrollo con Docker solo necesitas [Docker](https://docs.docker.com/get-docker/) con Docker Compose.

## Uso local

### Linux

```bash
# Instalar dependencias (Debian/Ubuntu)
sudo apt install php php-cli php-intl php-mysql php-zip php-xml php-mbstring php-curl unzip composer nodejs npm

# Clonar e iniciar el proyecto
git clone <url-del-repositorio> mi-proyecto
cd mi-proyecto
cp .env.example .env    # edita DB_* con tus credenciales de MySQL
composer setup
composer dev
```

### macOS

```bash
# Instalar dependencias con Homebrew
brew install php composer node mysql
brew services start mysql

# Clonar e iniciar el proyecto
git clone <url-del-repositorio> mi-proyecto
cd mi-proyecto
cp .env.example .env    # edita DB_* con tus credenciales de MySQL
composer setup
composer dev
```

### Windows

Instala PHP, Composer, Node.js y MySQL. La forma más sencilla es con [Laravel Herd](https://herd.laravel.com/windows) o con `winget` desde PowerShell:

```powershell
winget install PHP.PHP.8.4 Composer.Composer OpenJS.NodeJS.LTS Oracle.MySQL
```

Luego, en PowerShell:

```powershell
git clone <url-del-repositorio> mi-proyecto
cd mi-proyecto
Copy-Item .env.example .env    # edita DB_* con tus credenciales de MySQL
composer setup
composer dev
```

> En Windows, asegúrate de habilitar las extensiones `intl`, `pdo_mysql` y `zip` en tu `php.ini`.

### ¿Qué hacen estos comandos?

- `composer setup`: instala dependencias PHP y npm, crea `.env` si no existe, genera `APP_KEY`, ejecuta las migraciones y compila los assets.
- `composer dev`: levanta el servidor de desarrollo con recarga en vivo.

La aplicación queda disponible en <http://localhost:8000>.

### Otros comandos útiles

```bash
composer test           # Ejecuta las pruebas
./vendor/bin/pint       # Formatea el código (en Windows: vendor\bin\pint)
php artisan migrate     # Ejecuta migraciones
```

## Uso con Docker

El entorno Docker está en `docker/` y levanta tres servicios:

| Servicio | Descripción                         | Puerto |
| -------- | ----------------------------------- | ------ |
| `app`    | Servidor Laravel (`artisan serve`)  | 8000   |
| `vite`   | Servidor de Vite con recarga en vivo | 5173   |
| `db`     | MySQL 8.4                           | 3306   |

Al iniciar, el contenedor `app` instala las dependencias de Composer, espera a la base de datos y ejecuta las migraciones. El contenedor `vite` instala las dependencias de npm. El código se monta como volumen, así que los cambios se reflejan al instante.

### 1. Configurar `.env`

Copia el archivo de ejemplo:

```bash
cp .env.example .env              # Linux / macOS
Copy-Item .env.example .env       # Windows (PowerShell)
```

Ajusta las variables de base de datos para que apunten al contenedor `db`:

```dotenv
DB_HOST=db
DB_PORT=3306
DB_DATABASE=cinemax
DB_USERNAME=cinemax
DB_PASSWORD=cinemax
```

Genera una `APP_KEY` y pégala en `.env`:

```bash
docker compose -f docker/compose.yaml run --rm --no-deps app php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

### 2. Levantar el entorno

**Linux / macOS** (con [just](https://github.com/casey/just)):

```bash
just dev          # Construye y levanta app, vite y db
just dev-down     # Detiene el entorno
just dev-exec php artisan test    # Ejecuta un comando dentro del contenedor app
```

**Linux / macOS** (sin just):

```bash
UID=$(id -u) GID=$(id -g) docker compose -f docker/compose.yaml up --build
```

**Windows** (PowerShell):

```powershell
docker compose -f docker/compose.yaml up --build
```

Para detener el entorno en cualquier sistema:

```bash
docker compose -f docker/compose.yaml down
```

### 3. Ejecutar comandos dentro del contenedor

```bash
docker compose -f docker/compose.yaml exec app php artisan migrate
docker compose -f docker/compose.yaml exec app composer test
docker compose -f docker/compose.yaml exec app php artisan tinker
```

La aplicación queda disponible en <http://localhost:8000>.

> Si cambias valores en `.env`, recrea el contenedor para que los tome:
> `docker compose -f docker/compose.yaml up -d --force-recreate app`

### Development Kit Container (opcional)

El `Justfile` incluye `just dkc`, que abre una shell interactiva en la imagen `ghcr.io/leoborai/dkc:latest` con el proyecto montado en `/app`.

## Licencia

Proyecto de código abierto bajo la [licencia MIT](LICENSE).
