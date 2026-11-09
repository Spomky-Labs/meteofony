# Météofony

Météofony is a small weather application built with Symfony: French regions, departments and cities, fake
weather measures, and a few users with different roles. It is the playground of hands-on workshops about
Symfony security, where we audit it, find the gaps and harden its authentication.

> [!IMPORTANT]
> **Coming to a workshop?** Please go through [Getting started](#getting-started) at home, before the
> day. The first start downloads close to 1 GB (Docker images, PHP dependencies, the Tailwind CLI), and
> the venue Wi-Fi will not cope with a whole room doing it at the same time. Once it works,
> `castor stop`: everything stays on your disk for the day of the workshop.

## Getting started

### 1. Install the prerequisites

| Tool                     | Version       | Check with               |
|--------------------------|---------------|--------------------------|
| Docker, with Compose v2  | 23.0 or later | `docker compose version` |
| Castor                   | 1.0 or later  | `castor --version`       |
| Git                      | any           | `git --version`          |

Plan for about 2 GB of free disk space.

- **Docker**: [Docker Desktop](https://docs.docker.com/get-started/get-docker/) on macOS and Windows,
  Docker Engine on Linux. It has to be running: `docker info` must answer without an error.
- **Castor**, the task runner that every command below goes through:

  ```bash
  curl "https://castor.jolicode.com/install" | bash -s -- --static
  ```

  `--static` installs a binary that carries its own PHP, so you do not need PHP on your machine. It goes
  to `~/.local/bin`, which must be in your `PATH`.
  [Other installation methods](https://castor.jolicode.com/installation/alternative-methods/).
- **Windows**: work inside WSL 2. Enable the WSL integration of Docker Desktop, install Castor inside WSL
  with the command above, and clone the project into the Linux file system (`~/...`), not under `/mnt/c/`:
  files shared from the Windows side make the application very slow.
- **Apple Silicon**: nothing special, the image is published for arm64 too.

Bring your favourite IDE. The workshop uses PhpStorm, but any editor will do.

### 2. Clone and start

```bash
git clone https://github.com/Spomky-Labs/meteofony.git
cd meteofony
castor start
castor init
```

`castor start` pulls the image and starts PHP (FrankenPHP), PostgreSQL and Mailpit. On the first run the
container also installs the Composer dependencies and runs the migrations, which takes a few minutes:
`docker compose logs -f php` shows where it is.

`castor init` loads the data: French regions, departments and cities, and three users.

### 3. Check that it works

1. Open <https://localhost>. Your browser warns about the certificate, because it was issued by the local
   authority of Caddy, the web server. Accept it.
2. Log in as `admin`, then open <https://localhost/admin>.

| Username    | Password   | Role             |
|-------------|------------|------------------|
| `user1`     | `password` | `ROLE_USER`      |
| `moderator` | `password` | `ROLE_MODERATOR` |
| `admin`     | `password` | `ROLE_ADMIN`     |

If you can see the admin page, you are ready. Run `castor stop` until the workshop, and `git pull` just
before it to get the latest changes.

## Troubleshooting

**Ports 80 or 443 are already in use** (another local stack, a web server). Create a `.env.local` file at
the root of the project:

```dotenv
HTTP_PORT=8080
HTTPS_PORT=8443
HTTP3_PORT=8443
```

Then run `castor stop` and `castor start`, and browse <https://localhost:8443>.

**`castor start` fails.** `docker compose logs php` usually says why, and `castor runtime` shows what
Castor detected on your machine.

**You cannot install Docker.** `castor start` then runs the application with the Symfony CLI or the PHP
built-in server, on a SQLite database it creates and fills itself. This needs PHP 8.4 or later with the
`pdo_sqlite` extension, and Composer. Some parts of the workshop need Docker, so please open an issue
before the day if this is your case.

**Still stuck?** [Open an issue](https://github.com/Spomky-Labs/meteofony/issues) with the output of the
command that failed. And if it still does not work on the day, bring your laptop anyway: we will get you
up and running.

## Everyday commands

| Command                    | What it does                                            |
|----------------------------|---------------------------------------------------------|
| `castor start` / `stop`    | Start or stop the containers                            |
| `castor init`              | Load the seed data                                      |
| `castor console <command>` | Run a Symfony console command in the container          |
| `castor frontend --watch`  | Rebuild the Tailwind CSS while you edit                 |
| `castor phpunit`           | Run the test suite                                      |
| `castor build`             | Build the image locally instead of pulling it           |
| `castor list`              | List every task                                         |

## Stack

PHP 8.5 on FrankenPHP and Caddy, Symfony 8.1, API Platform 5, Symfony UX with AssetMapper and Tailwind,
PostgreSQL 16, Mailpit.
