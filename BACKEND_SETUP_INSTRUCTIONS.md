# Backend Setup Instructions for Updates Module

## Files Created

All backend files have been created in your Laravel project:

### Migrations:
- `database/migrations/2024_01_15_000001_create_updates_table.php`
- `database/migrations/2024_01_15_000002_create_update_likes_table.php`
- `database/migrations/2024_01_15_000003_create_update_comments_table.php`

### Models:
- `app/Models/Update.php`
- `app/Models/UpdateLike.php`
- `app/Models/UpdateComment.php`

### Controller:
- `app/Http/Controllers/UpdateController.php`

### Routes:
- Updated `routes/api.php` with all necessary endpoints

## Setup Steps

### 1. Run Migrations

Open your terminal in the Laravel project directory and run:

```bash
cd c:\Users\sagan\Capstone\capstone_backend
php artisan migrate
```

This will create the three new tables: `updates`, `update_likes`, and `update_comments`.

### 2. Verify Tables Created

You can verify the tables were created by checking your database or running:

```bash
php artisan tinker
```

Then in tinker:
```php
\App\Models\Update::count();
\App\Models\UpdateLike::count();
\App\Models\UpdateComment::count();
```

### 3. Test API Endpoints

You can test the endpoints using Postman or any API client:

#### Get Updates (Charity Admin)
```
GET http://127.0.0.1:8000/api/updates
Headers:
  Authorization: Bearer {your_token}
  Accept: application/json
```

#### Create Update
```
POST http://127.0.0.1:8000/api/updates
Headers:
  Authorization: Bearer {your_token}
  Accept: application/json
  Content-Type: multipart/form-data
Body (form-data):
  content: "This is my first update!"
  media[]: (file upload - optional, max 4 images)
```

#### Create Threaded Update
```
POST http://127.0.0.1:8000/api/updates
Headers:
  Authorization: Bearer {your_token}
  Accept: application/json
Body (JSON):
  {
    "content": "This is a follow-up update",
    "parent_id": 1
  }
```

#### Toggle Like
```
POST http://127.0.0.1:8000/api/updates/1/like
Headers:
  Authorization: Bearer {your_token}
  Accept: application/json
```

#### Add Comment
```
POST http://127.0.0.1:8000/api/updates/1/comments
Headers:
  Authorization: Bearer {your_token}
  Accept: application/json
  Content-Type: application/json
Body:
  {
    "content": "Great update!"
  }
```

## API Endpoints Reference

### Charity Admin Routes (auth:sanctum, role:charity_admin)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/updates` | Get all updates for logged-in charity |
| POST | `/api/updates` | Create new update (with optional parent_id for threading) |
| PUT | `/api/updates/{id}` | Edit update content |
| DELETE | `/api/updates/{id}` | Delete update |
| POST | `/api/updates/{id}/pin` | Toggle pin status |
| POST | `/api/updates/{id}/like` | Like/unlike update |
| GET | `/api/updates/{id}/comments` | Get comments for update |
| POST | `/api/updates/{id}/comments` | Add comment to update |
| DELETE | `/api/comments/{id}` | Delete comment |
| PATCH | `/api/comments/{id}/hide` | Hide/unhide comment |

### Public Routes

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/charities/{charity_id}/updates` | Get all updates for a specific charity (public view) |

## Database Schema

### updates table
```
id: bigint (primary key)
charity_id: bigint (foreign key → charities)
parent_id: bigint (nullable, foreign key → updates)
content: text
media_urls: json (array of file paths)
is_pinned: boolean (default: false)
likes_count: integer (default: 0)
comments_count: integer (default: 0)
created_at: timestamp
updated_at: timestamp
```

### update_likes table
```
id: bigint (primary key)
update_id: bigint (foreign key → updates)
user_id: bigint (foreign key → users)
created_at: timestamp
updated_at: timestamp
UNIQUE(update_id, user_id)
```

### update_comments table
```
id: bigint (primary key)
update_id: bigint (foreign key → updates)
user_id: bigint (foreign key → users)
content: text
is_hidden: boolean (default: false)
created_at: timestamp
updated_at: timestamp
```

## Features Implemented

✅ **CRUD Operations**: Create, Read, Update, Delete updates
✅ **Threading**: Parent-child relationship via parent_id
✅ **Media Upload**: Support for up to 4 images per update
✅ **Pinning**: Pin important updates to top
✅ **Likes**: Users can like/unlike updates
✅ **Comments**: Full comment system with CRUD
✅ **Authorization**: Proper ownership checks
✅ **Validation**: Input validation on all endpoints
✅ **Error Handling**: Comprehensive error handling with logging
✅ **Cascading Deletes**: Automatic cleanup of related records

## Security Features

- **Authentication**: All routes require valid auth token
- **Authorization**: Charity can only manage their own updates
- **Role-based Access**: Charity admin role required for management
- **Input Validation**: All inputs validated
- **File Upload Security**: Image type and size validation
- **SQL Injection Protection**: Using Eloquent ORM
- **Unique Constraints**: Prevent duplicate likes

## Next Steps

Once migrations are run successfully, proceed with the frontend implementation. The frontend will connect to these endpoints to provide the full Updates feature with threading functionality.

## Troubleshooting

### Migration Errors

If you get foreign key errors:
```bash
php artisan migrate:fresh
# Or if you have data:
php artisan migrate:rollback
php artisan migrate
```

### Permission Errors

If you get storage permission errors:
```bash
php artisan storage:link
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Clear Cache

If routes don't work:
```bash
php artisan route:clear
php artisan cache:clear
php artisan config:clear
```

Backend setup is complete! Ready for frontend integration.
