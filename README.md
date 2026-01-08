# User Analytics API

A Symfony 7.4 LTS backend API for user management and analytics.

## Technical Stack

- **Framework**: Symfony 7.4.* (LTS)
- **PHP Version**: 8.2+
- **Database**: SQLite
- **Containerization**: Docker with docker-compose
- **API Format**: JSON only (no frontend, no authentication)

## Architecture Overview

The application follows Symfony best practices with a clean architecture:

```
src/
├── Controller/
│   └── UserController.php      # API endpoints
├── Entity/
│   └── User.php                # user entity with validation
├── Repository/
│   └── UserRepository.php      # custom database queries
└── DataFixtures/
    └── UserFixtures.php        # seed data (25 users)
```

### User Entity

The User entity includes:
- `id`: Auto-generated integer (primary key)
- `name`: String with minimum 2 characters validation
- `email`: String with email format validation and unique constraint
- `status`: String (values: "active" or "inactive")
- `created_at`: DateTime immutable, auto-generated on creation

All validations use PHP 8 attributes.

## Setup Instructions

### 1. Build and Start Docker Container

```bash
docker-compose build
docker-compose up -d
```

### 2. Install Dependencies

```bash
docker exec symfony_api composer install
```

### 3. Create Database and Run Migrations

```bash
docker exec symfony_api php bin/console doctrine:database:create
docker exec symfony_api php bin/console doctrine:migrations:migrate --no-interaction
```

### 4. Load Fixtures (25 Users)

```bash
docker exec symfony_api php bin/console doctrine:fixtures:load --no-interaction
```

The fixtures create 25 users with the following distribution:
- 8 users created within the last 7 days
- 5 users created between 8-15 days ago
- 12 users created older than 15 days

### 5. Start the PHP Development Server

```bash
docker exec symfony_api php -S 0.0.0.0:8080 -t public
```

The API will be available at: `http://localhost:8080`

## API Documentation

All responses follow this structure:

```json
{
    "success": true/false,
    "data": {...} or [...],
    "message": "..."
}
```

### Endpoints

#### 1. Create User

**POST** `/users`

Creates a new user with validation.

**Request Body:**
```json
{
    "name": "Jane Doe",
    "email": "jane.doe@example.com",
    "status": "active"
}
```

**Success Response (201 Created):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Jane Doe",
        "email": "jane.doe@example.com",
        "status": "active",
        "created_at": "2026-01-08T12:00:00Z"
    },
    "message": "User created successfully."
}
```

**Error Response - Validation Failed (400 Bad Request):**
```json
{
    "success": false,
    "data": null,
    "message": "Validation failed.",
    "errors": {
        "name": "Name must be at least 2 characters long.",
        "email": "The email \"invalid\" is not a valid email."
    }
}
```

**Error Response - Email Exists (409 Conflict):**
```json
{
    "success": false,
    "data": null,
    "message": "Email already exists."
}
```

**Example cURL:**
```bash
curl -X POST http://localhost:8080/users \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Doe",
    "email": "jane.doe@example.com",
    "status": "active"
  }'
```

---

#### 2. List Users

**GET** `/users`

Returns all users sorted by `created_at` DESC (newest first).

**Optional Query Parameters:**
- `status`: Filter by status ("active" or "inactive")

**Success Response (200 OK):**
```json
{
    "success": true,
    "data": [
        {
            "id": 2,
            "name": "John Smith",
            "email": "john.smith@example.com",
            "status": "active",
            "created_at": "2026-01-07T10:30:00Z"
        },
        {
            "id": 1,
            "name": "Jane Doe",
            "email": "jane.doe@example.com",
            "status": "inactive",
            "created_at": "2026-01-06T08:15:00Z"
        }
    ],
    "message": "Users retrieved successfully."
}
```

**Example cURL - Get All Users:**
```bash
curl http://localhost:8080/users
```

**Example cURL - Filter by Status:**
```bash
curl http://localhost:8080/users?status=active
```

---

#### 3. User Analytics

**GET** `/users/analytics`

Returns analytics data about users.

**Success Response (200 OK):**
```json
{
    "success": true,
    "data": {
        "total_users": 25,
        "users_last_15_days": 13,
        "average_users_per_day_last_7_days": 1.14
    },
    "message": "Analytics retrieved successfully."
}
```

**Example cURL:**
```bash
curl http://localhost:8080/users/analytics
```

## Testing All Endpoints

### Complete Test Sequence

```bash
# 1. test analytics with fixtures data
curl http://localhost:8080/users/analytics

# expected: 25 total users, 13 users in last 15 days, ~1.14 average per day

# 2. list all users
curl http://localhost:8080/users

# expected: Array of 25 users sorted by created_at DESC

# 3. list only active users
curl http://localhost:8080/users?status=active

# expected: only users with status "active"

# 4. create a new user
curl -X POST http://localhost:8080/users \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test.user@example.com",
    "status": "active"
  }'

# expected: 201 Created with user data

# 5. Try to create duplicate email (should fail)
curl -X POST http://localhost:8080/users \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Another User",
    "email": "test.user@example.com",
    "status": "active"
  }'

# expected: 409 Conflict

# 6. try invalid data
curl -X POST http://localhost:8080/users \
  -H "Content-Type: application/json" \
  -d '{
    "name": "A",
    "email": "invalid-email",
    "status": "pending"
  }'

# expected: 400 Bad Request with validation errors

# 7. check analytics again
curl http://localhost:8080/users/analytics

# expected: 26 total users (25 + 1 new), 14 users in last 15 days
```

## Useful Commands

### Database Operations

```bash
# create migration
docker exec symfony_api php bin/console make:migration

# run migrations
docker exec symfony_api php bin/console doctrine:migrations:migrate

# drop and recreate database
docker exec symfony_api php bin/console doctrine:database:drop --force
docker exec symfony_api php bin/console doctrine:database:create
docker exec symfony_api php bin/console doctrine:migrations:migrate --no-interaction
docker exec symfony_api php bin/console doctrine:fixtures:load --no-interaction
```

### Debugging

```bash
# view container logs
docker logs symfony_api

# access container shell
docker exec -it symfony_api bash

# check Symfony routes
docker exec symfony_api php bin/console debug:router

# clear cache
docker exec symfony_api php bin/console cache:clear
```

### Stop/Restart

```bash
# stop containers
docker-compose down

# restart containers
docker-compose restart

# rebuild and restart
docker-compose down
docker-compose build
docker-compose up -d
```

## Project Structure

```
user-analytics-api/
├── bin/
│   └── console                 
├── config/
│   ├── bundles.php            
│   ├── packages/              
│   ├── routes.yaml            
│   └── services.yaml          
├── migrations/                
├── public/
│   └── index.php             
├── src/
│   ├── Controller/           
│   ├── Entity/              
│   ├── Repository/        
│   ├── DataFixtures/        
│   └── Kernel.php           
├── var/                      
├── vendor/                 
├── .env                     
├── .gitignore
├── composer.json            
├── docker-compose.yml        
├── Dockerfile               
└── README.md                
```


