# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Telegram habit-tracking bot (@HabitTrainingBot) built with Symfony 6.4, PHP 8.1+, Doctrine ORM, PostgreSQL, and Redis.

## Development Commands

All commands run inside Docker containers via `make`:

```bash
make up              # Start dev environment (PostgreSQL, PHP-FPM, Nginx, Redis)
make down            # Stop containers
make backend         # Install dependencies + run migrations (dev and test)
make backend_shell   # Shell into PHP container

make phpunit         # Run tests
make phpstan         # Static analysis (level 7)
make cs              # Code style check (ECS with PSR-12)
```

To run a single test inside the container:
```bash
docker-compose --file .docker/docker-compose.dev.yml exec php ./vendor/bin/phpunit --filter TestClassName
```

## Architecture

### Request Flow

Telegram webhook → `WebhookController` → `WebhookService` → `Router` → `CommandInterface` handler

### Command System (`src/Service/Command/`)

Each bot interaction is a `CommandInterface` with:
- `canRun()` — determines if this command handles the current update
- `run()` — executes the command logic
- `getPriority()` — `CommandPriority` enum for routing precedence

The `Router` iterates commands (sorted by priority) and dispatches to the first matching one.

**New commands must be registered** in the `app.service.command_locator` ServiceLocator in `config/services.yaml`. The key is the command's `COMMAND_NAME` constant and the value is the service class reference.

### Multi-Step Flows

`InputHandler` uses Redis to track user state across multiple Telegram messages (e.g., the habit creation flow: description → remind days → remind time → publish).

### Key Directories

- `src/Service/Command/` — Telegram command handlers (HabitCreation/, Settings/, etc.)
- `src/Service/Habit/` — Habit business logic (creation, completion, reminders)
- `src/Service/Keyboard/` — Telegram inline keyboard builders
- `src/Command/` — Symfony console commands (SendReminderCommand cron, news broadcasting)
- `src/Controller/Api/` — REST API endpoints (JWT-authenticated)
- `src/Entity/` — Doctrine entities: User, Habit (Draft/Published states), Metric (completions)

### State & Enums

- `HabitState`: Draft → Published (habit creation lifecycle)
- `UserStatus`: Active / Inactive
- `CommandPriority`: Controls which command handler gets dispatched first