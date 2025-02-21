# Academy 2025

## Getting Started (with Docker)

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --no-cache` to build fresh images
3. Run `docker compose up -d` to set up and start a fresh Symfony project
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.
6. Execute commands as you typically do on your local machine use this syntax e.g: `docker compose exec php php bin/console make:migration`

## Emails (Docker)

1. Access url via `http://localhost:32776/` to see your fake email server.

## Features

-   Production, development and CI ready
-   Just 1 service by default
-   Blazing-fast performance thanks to [the worker mode of FrankenPHP](https://github.com/dunglas/frankenphp/blob/main/docs/worker.md) (automatically enabled in prod mode)
-   [Installation of extra Docker Compose services](docs/extra-services.md) with Symfony Flex
-   Automatic HTTPS (in dev and prod)
-   HTTP/3 and [Early Hints](https://symfony.com/blog/new-in-symfony-6-3-early-hints) support
-   Real-time messaging thanks to a built-in [Mercure hub](https://symfony.com/doc/current/mercure.html)
-   [Vulcain](https://vulcain.rocks) support
-   Native [XDebug](docs/xdebug.md) integration
-   Super-readable configuration

**Enjoy!**

## Symfony local installation

1. Run application using command `symfony server:start`
2. Access application on url `http://localhost:8000`

### Database

Adjust .env file with your database credentials. You should have it locally installed.

3. Run `symfony console doctrine:migrations:migrate` to migrate database
4. Run `symfony console doctrine:fixtures:load` to load fixtures
5. Run `symfony console doctrine:fixtures:load --env=test` to load fixtures in test environment
