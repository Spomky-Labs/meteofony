# AGENTS.md

Météofony is a Symfony security playground: a small weather application on French geography, used as the
material of hands-on workshops about auditing and hardening Symfony authentication. Some authentication
methods are wired only partially on purpose (see [Security](#security)). Ask before enabling, completing or
removing one of them.

Check `composer.json` for exact versions and `symfony.lock` for the recipes that ran. At the time of
writing: PHP 8.4+ (8.5 in the Docker image), Symfony 8.1, API Platform 5, Doctrine ORM, PostgreSQL 16.

## Running the application

Everything goes through [Castor](https://castor.jolicode.com/). `castor.php` imports the tasks of
`.castor/stack/`: the stack itself (`stack.php`), how it is chosen (`runtime.php`), what fills it
(`provision.php`), and the quality tools (`qa.php`).

| Task                                    | What it does                                                                              |
|-----------------------------------------|-------------------------------------------------------------------------------------------|
| `castor start`                          | Start the stack, wait for the `php` container, compile the frontend (`--pull` refreshes images) |
| `castor stop` / `restart`               | Stop / restart the containers                                                             |
| `castor destroy`                        | Remove containers, volumes and networks (`--force` skips the confirmation)                |
| `castor init`                           | Seed regions, then departments, then cities, then users                                   |
| `castor build`                          | Build the image locally (`--prod`, `--no-cache`, `--pull`)                                |
| `castor console <command>`              | Run `bin/console` in the container                                                        |
| `castor php <command>`                  | Run any command in the container (`castor php composer require ...`)                      |
| `castor frontend --watch`               | Rebuild Tailwind on change                                                                |
| `castor install` / `update`             | Composer install / update (update also migrates and updates the importmap)               |
| `castor runtime`                        | Show which runtime Castor picked, and why                                                 |

**Runtime.** `castor start` uses Docker when its daemon answers, else the Symfony CLI, else the PHP built-in
server. The two fallbacks run on a SQLite file (`var/app.db`) built with `doctrine:schema:create`, because
the migrations are PostgreSQL SQL, and they have no X509 client-certificate authentication, which Caddy
provides. `--runtime=docker|symfony|php` or `METEOFONY_RUNTIME` forces one. Inside a container or in CI,
commands run directly.

**Image.** Compose names the image `${IMAGES_PREFIX}meteofony:${TAG}`. Castor sets `TAG=dev`, and `.env` sets
`IMAGES_PREFIX=ghcr.io/spomky-labs/`, so a fresh clone pulls the multi-arch image that
`.github/workflows/dev-image.yml` publishes, and Compose builds it locally when the pull fails. After
changing `Dockerfile` or `frankenphp/`, run `castor build`.

**First start.** The entrypoint (`frankenphp/docker-entrypoint.sh`) installs the Composer dependencies into
the bind-mounted `vendor/` when it is empty, waits for the database, runs the migrations, then starts
FrankenPHP. `castor start` waits for that before running `bin/console`.

**Access.**
- Application: <https://localhost>. Ports come from `HTTP_PORT`, `HTTPS_PORT` and `HTTP3_PORT`, which
  `.env.local` can override when 80/443 are taken. Castor passes `.env.local` to Compose through
  `COMPOSE_ENV_FILES`.
- PostgreSQL and Mailpit are published on random host ports: `docker compose port database 5432`,
  `docker compose port mailer 8025`.
- Seed users (`data/users.json`), all with the password `password`: `user1` (`ROLE_USER`), `moderator`
  (`ROLE_MODERATOR`), `admin` (`ROLE_ADMIN`).

## Architecture

- **Data model.** `Region` → `Department` → `City` (Doctrine entities in `src/Entity/`, seeded from
  `data/*.json` by the `app:init:*` commands). Weather measures are **fake**: `src/State/MeasureStateProvider.php`
  generates them through `FakeMeasureTrait`, and nothing persists them.
- **Web.** Thin controllers in `src/Controller/` rendering Twig templates from `templates/`.
- **API.** API Platform resources are DTOs in `src/ApiResource/` (`Measure`, `User`), separate from the
  entities and served by state providers in `src/State/`. `ApiController` also exposes hand-written JSON
  routes under `/api/regions`, `/api/departments` and `/api/cities`.
- **Frontend.** Server-rendered, no Node.js: AssetMapper with `importmap.php`, Tailwind v4 and daisyUI through
  `symfonycasts/tailwind-bundle`, Turbo, Stimulus controllers in `assets/controllers/`, Symfony UX (Live
  and Twig Components, Map with Leaflet, Chart.js, Icons). The one Live Component is
  `src/Twig/SearchCityComponent.php` (`search_cities`), with its template in `templates/components/`.
- **Console commands** (`src/Command/`): `app:init:{regions,departments,cities,users}`,
  `app:user:{create,promote,demote}`, `app:access_token:create`.
- **Messenger** uses `sync://`. The mailer is `null://null`, except under Docker, where it is Mailpit.

## Security

The state of `main`, in `config/packages/security.yaml`:

- One firewall, `main`, with a custom authenticator (`src/Security/UsernamePasswordAuthenticator.php`:
  username, password, CSRF) and the `App\Security\UserProvider` provider. Logout is on `/logout`.
- Access control: `/admin` needs `ROLE_ADMIN`, `/dashboard` and `/profile` need `ROLE_USER`, everything
  else is public, `/api` included (its rule is commented out). The role hierarchy is
  `ROLE_ADMIN` → `ROLE_USER`. `ROLE_MODERATOR` exists only in the seed data.
- CSRF tokens are stateless (`config/packages/csrf.yaml`, `assets/controllers/csrf_protection_controller.js`).
- Password reset uses `symfonycasts/reset-password-bundle`.
- Partially wired, as workshop material:
  - **WebAuthn**: the `WebauthnCredential` entity and repositories exist, the config is commented out,
    and no WebAuthn bundle is installed.
  - **X509** for `/admin`: `AdminProvider` and the `Admin` user (not a Doctrine entity) exist. The
    firewall and Caddy's `client_auth` (`frankenphp/Caddyfile`) are commented out, and the certificates
    are in `frankenphp/certs/`.
  - **Access tokens**: the `AccessToken` entity and `app:access_token:create` exist, but no firewall
    reads them on `main`.
- Coraza WAF: off in the development Caddyfile (the comment there says why), on in production through
  `compose.prod.yaml`.
- `.env` is committed and holds defaults only. Real secrets go in `.env.local` (ignored) or in the
  secrets vault (`castor console secrets:set`).

## Quality and tests

The tools are not in `vendor/`. They run in the `ghcr.io/spomky-labs/phpqa:8.4` image, which Castor pulls on
each run, and they don't need the stack to be up. Their configuration lives in `.ci-tools/`.

| Task                     | Tool                                                         |
|--------------------------|--------------------------------------------------------------|
| `castor phpunit`         | Whole PHPUnit suite, with coverage                           |
| `castor phpstan`         | PHPStan at level max (`--baseline` regenerates the baseline) |
| `castor ecs`             | Easy Coding Standard, PSR-12 plus extra rules (`--fix`)      |
| `castor rector`          | Rector, dry run by default (`--fix` applies)                 |
| `castor deptrac`         | Layer dependencies                                           |
| `castor lint`            | Syntax check of `src/`, `tests/`, `castor.php`, `.castor/`   |
| `castor infect`          | Infection mutation testing                                   |
| `castor check-licenses`  | Licenses of the installed dependencies                       |
| `castor prepare-pr`      | `ecs --fix`, `rector --fix`, then PHPStan, Deptrac and lint  |

`.github/workflows/ci.yml` calls these tasks by name, so renaming a task function renames a CI step.

Tests live in `tests/Functional/` (`WebTestCase`). `tests/Benchmark/` holds PHPBench benchmarks of the
password hashers (`.ci-tools/phpbench.json`). A feature isn't done until a test exercises it the way a
caller would: an HTTP request for a controller, a service call for a service, and not just "it didn't
throw".

## Conventions

Follow <https://symfony.com/doc/current/best_practices.html>, with what this repository adds:

- `declare(strict_types=1)` in every PHP file. PHPStan stays at level max: fix the error rather than
  growing the baseline.
- Constructor property promotion, and `readonly` for DTOs and value objects. Don't make a service
  `readonly` if it might become `lazy: true`, because a lazy proxy can't extend a `readonly` class.
- PHP attributes for framework metadata: `#[Route]`, `#[IsGranted]`, `#[MapRequestPayload]`,
  `#[Assert\...]`, `#[AsCommand]`, `#[AsEventListener]`, `#[AsLiveComponent]`, `#[ApiResource]`. No YAML or
  XML routing: `config/routes.yaml` only imports the attributes.
- Autowiring and autoconfiguration. Where a type hint can't express it, use `#[Autowire]` or `#[Target]`;
  a YAML service definition is the last resort.
- Controllers extend `AbstractController`, stay thin, and delegate to services.
- Use what the framework already does: Form, Validator, Serializer, Messenger, Security voters and
  authenticators, Twig `path()`/`url()` rather than hardcoded URLs. Bind request data with
  `#[MapRequestPayload]` / `#[MapQueryString]` rather than `json_decode()`. Use `symfony/lock` for mutual
  exclusion (install it first: it isn't required yet).
- New API endpoint: a DTO in `src/ApiResource/` with `#[ApiResource]` and its operations, plus a state
  provider in `src/State/` when the data doesn't come from a Doctrine entity. `Measure` and
  `MeasureStateProvider` are the example.
- New capability: `castor php composer require <package>`, and let the Flex recipe register the bundle and
  write its configuration. Don't hand-edit `config/bundles.php`.
- Schema changes go through migrations: `castor console make:migration`, then
  `castor console doctrine:migrations:migrate`. Never use `doctrine:schema:update` or hand-written SQL. The
  SQLite fallback in `.castor/stack/runtime.php` is the one exception, for the reason given there.
- Frontend: server-side rendering first, Stimulus for small behaviours, and
  `castor console importmap:require <package>` for JavaScript dependencies.
- Makers prompt on a terminal: pass every argument and `--no-interaction`, or write the code by hand.
- If a task leaves a choice open and you can't ask, state the assumption you make and take the smallest
  option.

## Discover, don't guess

Framework APIs change between versions and training data goes stale. Look things up in the project:

- `castor console about`, `debug:router`, `debug:container`, `debug:autowiring <name>`,
  `debug:config <bundle>`, `config:dump-reference <bundle>`.
- `castor console lint:container`, `lint:twig templates/`, `lint:yaml config/`.
- When something fails: `docker compose logs php`, `var/log/dev.log`, and the profiler at `/_profiler`.
- The installed sources and docblocks under `vendor/`, and <https://symfony.com/doc/current/> for the
  version in `composer.json`.
