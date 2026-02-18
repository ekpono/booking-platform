# Booking Platform

A simple booking management platform built with Laravel 12, Vue.js 3, and PostgreSQL. Users can create and manage bookings associated with clients, with automatic overlap prevention.

## Features

- **Booking Management**: Create, view, update, and delete bookings
- **Client Management**: Manage clients associated with bookings
- **Overlap Prevention**: Prevents users from creating overlapping bookings
- **Weekly View API**: Retrieve bookings for any calendar week (Monday-Sunday)
- **Modern UI**: Vue.js 3 frontend with Tailwind CSS styling
- **Repository Pattern**: Clean architecture with abstracted database layer

## Tech Stack

- **Backend**: Laravel 12 (PHP 8.4)
- **Frontend**: Vue.js 3 with Vite
- **Database**: PostgreSQL
- **Styling**: Tailwind CSS 4
- **Development**: Laravel Sail (Docker)
- **Testing**: PHPUnit

## Requirements

- Docker Desktop
- Git

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/ekpono/booking-platform.git
   cd booking-platform
   ```

2. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

3. **Start Docker containers**
   ```bash
   ./vendor/bin/sail up -d
   ```

4. **Install PHP dependencies** (if not already installed)
   ```bash
   ./vendor/bin/sail composer install
   ```

5. **Generate application key**
   ```bash
   ./vendor/bin/sail artisan key:generate
   ```

6. **Run database migrations**
   ```bash
   ./vendor/bin/sail artisan migrate
   ```

7. **Seed the database** (optional, for demo data)
   ```bash
   ./vendor/bin/sail artisan db:seed
   ```

8. **Install and build frontend assets**
   ```bash
   ./vendor/bin/sail npm install
   ./vendor/bin/sail npm run build
   ```

9. **Access the application**
   - Web UI: http://localhost
   - API: http://localhost/api

## Running Tests

```bash
./vendor/bin/sail artisan test
```

Or run specific test files:

```bash
# Test booking creation
./vendor/bin/sail artisan test --filter=BookingCreationTest

# Test overlap prevention
./vendor/bin/sail artisan test --filter=BookingOverlapTest

# Test weekly API
./vendor/bin/sail artisan test --filter=WeeklyBookingsApiTest
```

## API Endpoints

### Bookings

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/bookings` | List all bookings |
| GET | `/api/bookings?week=2025-08-05` | Get bookings for specific week |
| POST | `/api/bookings` | Create a new booking |
| GET | `/api/bookings/{id}` | Get a single booking |
| PUT | `/api/bookings/{id}` | Update a booking |
| DELETE | `/api/bookings/{id}` | Delete a booking |

### Clients

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/clients` | List all clients |
| POST | `/api/clients` | Create a new client |
| GET | `/api/clients/{id}` | Get a single client |
| DELETE | `/api/clients/{id}` | Delete a client |

## API Examples

### Create a Booking

```bash
curl -X POST http://localhost/api/bookings \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 1,
    "title": "Project Meeting",
    "description": "Discuss project requirements",
    "start_time": "2025-08-05 10:00:00",
    "end_time": "2025-08-05 11:00:00"
  }'
```

### Get Weekly Bookings

```bash
curl http://localhost/api/bookings?week=2025-08-05
```

### Response Format

```json
{
  "data": [
    {
      "id": 1,
      "title": "Project Meeting",
      "description": "Discuss project requirements",
      "start_time": "2025-08-05T10:00:00+00:00",
      "end_time": "2025-08-05T11:00:00+00:00",
      "user": {
        "id": 1,
        "name": "Demo User",
        "email": "demo@example.com"
      },
      "client": {
        "id": 1,
        "name": "Acme Corporation",
        "email": "contact@acme.com"
      },
      "created_at": "2025-02-18T20:00:00+00:00",
      "updated_at": "2025-02-18T20:00:00+00:00"
    }
  ]
}
```

## Business Rules

### Overlap Prevention

A user cannot have overlapping bookings. The system checks for overlaps when:

- Creating a new booking
- Updating an existing booking

If an overlap is detected, the API returns a 422 response:

```json
{
  "message": "Validation failed",
  "errors": {
    "overlap": ["The booking overlaps with an existing booking for this user."]
  }
}
```

### Weekly API

The weekly bookings endpoint uses Monday-Sunday week boundaries:

- Pass any date within the desired week
- Returns all bookings where `start_time` falls within that week
- Results are ordered by `start_time`

## Project Structure

```
app/
├── Exceptions/
│   └── BookingOverlapException.php
├── Http/
│   ├── Controllers/Api/
│   │   ├── BookingController.php
│   │   └── ClientController.php
│   ├── Requests/
│   │   ├── StoreBookingRequest.php
│   │   ├── UpdateBookingRequest.php
│   │   └── StoreClientRequest.php
│   └── Resources/
│       ├── BookingResource.php
│       └── ClientResource.php
├── Models/
│   ├── Booking.php
│   ├── Client.php
│   └── User.php
├── Providers/
│   └── RepositoryServiceProvider.php
└── Repositories/
    ├── Contracts/
    │   ├── RepositoryInterface.php
    │   ├── BookingRepositoryInterface.php
    │   └── ClientRepositoryInterface.php
    └── Eloquent/
        ├── BaseRepository.php
        ├── BookingRepository.php
        └── ClientRepository.php
```

## Demo Credentials

After running seeders:

- **Email**: demo@example.com
- **Password**: password

## Development

### Start development server

```bash
./vendor/bin/sail up -d
./vendor/bin/sail npm run dev
```

### Watch for changes

```bash
./vendor/bin/sail npm run dev
```

### Run code formatting

```bash
./vendor/bin/sail vendor/bin/pint
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
