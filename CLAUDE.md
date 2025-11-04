# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Météofony is a Symfony 7.2 security playground/demonstration application simulating a weather monitoring system with French geographic data. The project emphasizes modern security practices, code quality, and developer experience.

**Stack:** PHP 8.3, Symfony 7.2, API Platform 4.0, Doctrine ORM, FrankenPHP, PostgreSQL 16, Tailwind CSS, Symfony UX (Live Components, Turbo, Stimulus)

## Development Commands

All commands use **Castor** (PHP task runner). Run from project root:

### Essential Commands
- `castor start` - Start Docker containers and run migrations
- `castor stop` - Stop containers
- `castor init` - Initialize database with seed data (users, regions, departments, cities)
- `castor frontend --watch` - Compile frontend assets with live reload
- `castor console [command]` - Run Symfony console commands
- `castor php [command]` - Execute PHP in Docker container

### Testing & Quality
- `castor test` - Run PHPUnit tests (use `--coverage` for coverage report)
- `castor cs` - Check coding standards (use `--fix` to auto-fix)
- `castor stan` - Run PHPStan static analysis (level: max)
- `castor rector` - Run Rector (use `--fix` to apply refactorings)
- `castor audit` - Security vulnerability scan

### Running Individual Tests
```bash
castor php vendor/bin/phpunit tests/Benchmark/SecurityBenchmarkTest.php
castor console app:init:users  # Re-initialize test data if needed
```

## Architecture

### Layered Structure
1. **Web Layer** - Traditional Symfony MVC controllers returning Twig templates
2. **API Layer** - API Platform RESTful endpoints at `/api/*`
3. **Security Layer** - Multiple authentication methods (password, access tokens, WebAuthn support)
4. **Data Layer** - Doctrine entities with PostgreSQL persistence

### Key Separation Pattern
- **Entities** (`src/Entity/`) - Database persistence models
- **ApiResource** (`src/ApiResource/`) - API DTOs, separate from entities
- **State Providers** (`src/State/`) - Custom data fetching for API Platform
- **Live Components** (`src/Twig/Components/`) - Interactive UI without JavaScript frameworks

### Data Model Hierarchy
```
Region (e.g., Île-de-France)
  └─ Department (e.g., Paris - 75)
      └─ City (e.g., Paris)
          └─ Measure (weather data - fake, not persisted)
```

### Frontend Architecture
- **No Node.js build required** - Uses AssetMapper with ImportMap
- Server-rendered Twig templates with progressive enhancement
- Symfony UX Live Components for interactivity (see `src/Twig/Components/`)
- Minimal JavaScript via Stimulus controllers (`assets/controllers/`)
- Tailwind CSS for styling

### Security Features
- Role-based access: `ROLE_USER`, `ROLE_ADMIN`
- Custom authenticator: `src/Security/UsernamePasswordAuthenticator.php`
- Access tokens for API: `src/Entity/AccessToken.php`
- WebAuthn infrastructure present (commented out in `security.yaml`)
- Coraza WAF integrated in Caddyfile

## Code Standards

### Strict Type Safety
- All files use `declare(strict_types=1)`
- PHPStan at maximum level - maintain this standard
- Use constructor property promotion extensively

### Attributes Over Annotations
```php
#[Route('/path', name: 'route_name')]
#[ORM\Entity(repositoryClass: FooRepository::class)]
#[ApiResource(operations: [new Get()])]
```

### API Development Pattern
1. Create ApiResource DTO in `src/ApiResource/`
2. Define operations with `#[ApiResource]` attribute
3. Implement State Provider in `src/State/` if custom data fetching needed
4. Use `#[ApiProperty]` for field-level configuration

Example: See `src/ApiResource/Measure.php` + `src/State/MeasureProvider.php`

## Docker Environment

- **FrankenPHP** replaces traditional PHP-FPM (worker mode in production)
- **Caddy** provides web server + Mercure hub for real-time features
- **PostgreSQL 16** for database
- All services defined in `compose.yaml` and `compose.override.yaml`

Access:
- Web: https://localhost
- API: https://localhost/api
- Database: localhost:5432 (from host)

## Common Patterns

### Creating Console Commands
Extend `Symfony\Component\Console\Command\Command`, place in `src/Command/`. See examples in `src/Command/Init/` for database seeding patterns.

### Database Migrations
```bash
castor console make:migration
castor console d:m:m  # doctrine:migrations:migrate
```

### Adding API Endpoints
1. Create ApiResource class with `#[ApiResource]`
2. Define operations: `#[Get]`, `#[GetCollection]`, `#[Post]`, etc.
3. Create State Provider if data doesn't come from Doctrine entity
4. Configure security in `config/packages/security.yaml` if needed

### Live Components
Create in `src/Twig/Components/`, extend `AbstractController` or use `#[AsLiveComponent]`. Template goes in `templates/components/`. See `CityComponent.php` for reference.

## Important Files

- `castor.php` - All task definitions
- `config/packages/security.yaml` - Authentication & authorization
- `config/routes.yaml` - Additional route configuration
- `compose.yaml` - Docker services configuration
- `frankenphp/Caddyfile` - Web server + Mercure configuration
- `importmap.php` - Frontend dependencies (no package.json needed)

## Notes

- This is a **security learning/demonstration project** - code intentionally explores various auth methods
- Measure data is **fake** (generated via State Provider, not from database)
- Quality tools are strict: PHPStan max level, PSR-12 compliance required
- Frontend uses server-side rendering philosophy - avoid heavy client-side JavaScript
