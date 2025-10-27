# Quick Start Guide

## 🚀 Get Started in 5 Minutes

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- SQLite (or MySQL)

### Backend Setup (2 minutes)
```bash
cd capstone_backend

# Install dependencies (if needed)
composer install

# Generate app key
php artisan key:generate

# Setup database
php artisan migrate:fresh --seed

# Start server
php artisan serve
```
✅ Backend running at `http://127.0.0.1:8000`

### Frontend Setup (2 minutes)
```bash
cd capstone_frontend

# Install dependencies (if needed)
npm install

# Create .env.local file
echo "VITE_API_URL=http://127.0.0.1:8000" > .env.local

# Start dev server
npm run dev
```
✅ Frontend running at `http://localhost:8080`

### Login (1 minute)

Navigate to `http://localhost:8080/auth/login`

#### Admin Account
- **Email:** `admin@example.com`
- **Password:** `admin123`
- **Access:** Full admin dashboard at `/admin`

#### Donor Account
- **Email:** `donor@example.com`
- **Password:** `donor123`
- **Access:** Donor dashboard at `/donor`

#### Charity Admin Account
- **Email:** `charity@example.com`
- **Password:** `charity123`
- **Access:** Charity dashboard at `/charity`

## 🎯 Admin Features Quick Reference

| Feature | Route | Description |
|---------|-------|-------------|
| Dashboard | `/admin` | View platform metrics |
| Charities | `/admin/charities` | Manage charity verifications |
| Users | `/admin/users` | Manage user accounts |
| Audit Logs | `/admin/logs` | View system activity |
| Settings | `/admin/settings` | Configure system |

## 🔧 Common Commands

### Backend
```bash
# Reset database
php artisan migrate:fresh --seed

# Clear cache
php artisan cache:clear
php artisan config:clear

# View routes
php artisan route:list
```

### Frontend
```bash
# Build for production
npm run build

# Run linter
npm run lint

# Type check
npm run type-check
```

## 🐛 Troubleshooting

### "404 Error" on login
```bash
# Frontend: Check .env.local exists
cat capstone_frontend/.env.local
# Should show: VITE_API_URL=http://127.0.0.1:8000

# Restart frontend dev server
```

### "No application encryption key"
```bash
cd capstone_backend
php artisan key:generate
```

### "Database not found"
```bash
cd capstone_backend
php artisan migrate:fresh --seed
```

### Frontend not loading data
```bash
# 1. Check backend is running
curl http://127.0.0.1:8000/api/ping

# 2. Check .env.local
cat capstone_frontend/.env.local

# 3. Restart frontend (Ctrl+C then npm run dev)
```

## 📚 Full Documentation

See `ADMIN_GUIDE.md` for complete documentation including:
- Detailed feature descriptions
- API endpoints reference
- Testing procedures
- Security considerations
- Troubleshooting guide

## ✅ Verification Checklist

- [ ] Backend server running on port 8000
- [ ] Frontend server running on port 8080
- [ ] `.env.local` file exists in frontend
- [ ] Database seeded with test users
- [ ] Can login as admin
- [ ] Admin dashboard loads with metrics
- [ ] Can view charities list
- [ ] Can view users list

## 🎉 You're Ready!

Your admin system is now fully functional. Login as admin and explore the features!
