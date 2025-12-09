# BeautyBook

Online booking platform for beauty salons with a shared workspace model — masters rent workstations while the salon provides client management infrastructure: scheduling, online booking, notifications, and analytics.

## Business Model

Three-sided system with distinct roles:

- **Salon** — manages the space, oversees masters, tracks overall performance
- **Master** — independent specialist who rents a workstation, manages own services, schedule, and clients
- **Client** — browses the catalog, books appointments, receives reminders

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 7.4+ / Yii2 (Basic Template) |
| Database | MySQL 8.0 |
| Cache / Queue / Locks | Redis 7.x |
| Frontend | Vue 3 + Vite |
| HTTP Server | Nginx |
| Infrastructure | Docker Compose |

## Architecture

The application follows a decoupled architecture — Yii2 serves a versioned REST API, Vue 3 SPA consumes it as a standalone client.

```
beautybook/
│
├── docker/                         # Docker configuration
│   ├── nginx/default.conf          # Nginx reverse proxy config
│   ├── php/Dockerfile              # PHP-FPM with extensions
│   └── redis/redis.conf            # Redis configuration
│
├── api/                            # Yii2 Basic Template — REST API
│   ├── config/
│   │   ├── db.php                  # MySQL connection
│   │   ├── redis.php               # Redis connection
│   │   └── web.php                 # Application config, URL rules
│   ├── controllers/api/v1/         # Versioned API controllers
│   │   ├── BookingController.php
│   │   ├── MasterController.php
│   │   ├── ScheduleController.php
│   │   └── ServiceController.php
│   ├── models/                     # ActiveRecord models
│   │   ├── Booking.php
│   │   ├── Master.php
│   │   ├── Salon.php
│   │   ├── Service.php
│   │   └── TimeSlot.php
│   ├── components/                 # Redis-powered components
│   │   ├── RedisLock.php           # Distributed lock for booking
│   │   ├── RedisQueue.php          # Notification queue
│   │   └── RateLimiter.php         # API rate limiting
│   ├── workers/
│   │   └── NotificationWorker.php  # Queue consumer (console command)
│   └── migrations/                 # Database schema
│
├── frontend/                       # Vue 3 SPA (Vite)
│   └── src/
│       ├── views/                  # Page components
│       ├── components/             # Reusable UI components
│       ├── composables/            # Composition API hooks
│       ├── stores/                 # Pinia state management
│       ├── router/                 # Vue Router
│       └── api/                    # Axios HTTP layer
│
├── docker compose.yml
└── README.md
```

## Core Features

### Online Booking

Clients select a master, choose a service, pick an available time slot, and confirm the appointment. The booking flow uses a **Redis distributed lock** (`SETNX` with TTL) to prevent race conditions when two clients attempt to book the same slot simultaneously.

### Smart Scheduling

Masters manage their availability through a slot-based schedule. Slots are generated from working hours templates and can be individually blocked or opened. Schedule data is cached in Redis with automatic invalidation on changes.

### Notification Queue

Booking confirmations and appointment reminders are processed asynchronously via a **Redis-backed queue** (Lists with `LPUSH`/`BRPOP`). A dedicated console worker consumes the queue and dispatches notifications.

### API Rate Limiting

Public-facing endpoints are protected by a **sliding window rate limiter** built on Redis Sorted Sets. This prevents booking spam and API abuse without impacting legitimate traffic.

### Real-Time Updates

Schedule changes are broadcast via **Redis Pub/Sub**, enabling masters to see new bookings and cancellations in real time without page refresh.

### Role-Based Access

| Role | Access |
|------|--------|
| Client | Browse catalog, book appointments, view own history |
| Master | Manage schedule, services & pricing, view statistics |
| Admin | Manage masters, salon settings, view analytics |

## API

Base path: `/api/v1/`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/masters` | List salon masters |
| `GET` | `/masters/{id}` | Master profile with services |
| `GET` | `/masters/{id}/schedule?date=` | Available time slots |
| `POST` | `/bookings` | Create a booking |
| `GET` | `/bookings/{id}` | Booking status |
| `PATCH` | `/bookings/{id}/cancel` | Cancel a booking |
| `GET` | `/master/dashboard` | Master's dashboard |
| `PATCH` | `/master/schedule` | Update schedule |
| `GET` | `/admin/analytics` | Salon analytics |

## Redis Usage

Redis is **not just a cache** in this project — it serves six distinct roles:

| Role | Data Structure | Purpose |
|------|---------------|---------|
| Distributed Lock | `SETNX` + TTL | Prevent double-booking of time slots |
| Task Queue | Lists (`LPUSH`/`BRPOP`) | Async notification processing |
| Rate Limiter | Sorted Sets | Sliding window API protection |
| Pub/Sub | Channels | Real-time schedule broadcasting |
| Cache | Strings/Hashes + TTL | Schedule and catalog caching |
| Session Store | Yii2 Redis Session | Server-side session management |

## Getting Started

### Prerequisites

- Docker & Docker Compose v2
- Git

### Quick Start

```bash
# Clone the repository
git clone https://github.com/lmoroz/beautybook.git
cd beautybook

# Start all services
docker compose up -d

# Run database migrations
docker compose exec php-fpm php yii migrate --interactive=0

# The application is now available:
# - Frontend:  http://localhost:3000
# - API:       http://localhost:8080/api/v1/
# - MySQL:     localhost:3306
# - Redis:     localhost:6379
```

### Stop

```bash
docker compose down
```

### Reset Database

```bash
docker compose exec php-fpm php yii migrate/redo --interactive=0
```

### Run Notification Worker

```bash
docker compose exec php-fpm php yii queue/listen
```

## Database Schema

```
┌──────────┐     ┌──────────┐     ┌──────────┐
│  salons  │────<│ masters  │────<│ services │
└──────────┘     └────┬─────┘     └────┬─────┘
                      │                │
                 ┌────┴─────┐         │
                 │time_slots│         │
                 └────┬─────┘         │
                      │                │
                 ┌────┴─────┐         │
                 │ bookings │─────────┘
                 └──────────┘

salons     1 ──< N  masters
masters    1 ──< N  services
masters    1 ──< N  time_slots
time_slots 1 ──< 1  bookings
services   1 ──< N  bookings
```

## Development

### Backend (Yii2 API)

```bash
# Access PHP container shell
docker compose exec php-fpm bash

# Run migrations
php yii migrate

# Create new migration
php yii migrate/create create_bookings_table
```

### Frontend (Vue 3)

```bash
cd frontend
npm install
npm run dev
```

### Code Standards

- **PHP:** PSR-12
- **Vue:** Composition API (`<script setup>`), no Options API
- **Commits:** [Conventional Commits](https://www.conventionalcommits.org/)
- **Language:** English for all code, comments, and documentation

## Screenshots

> Coming soon

## License

MIT
