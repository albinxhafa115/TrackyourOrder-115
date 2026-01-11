# 🚀 KUOSHT GPS Tracking System - Laravel Edition

Sistema profesionale e GPS tracking për menaxhimin e dërgesave dhe kurierëve.

---

## ✅ Projekti i Kompletuar 100%

**Status:** ✅ Production Ready  
**Version:** 1.0.0  
**Technology:** Laravel 11 + PostgreSQL + Leaflet Maps

---

## 🎯 Quick Start

### 1. Start Server
```bash
cd kuosht-tracking-laravel
php artisan serve
```

Server: **http://127.0.0.1:8000**

### 2. Login
```
URL:      http://127.0.0.1:8000/courier/login
Email:    leart@kuosht.com
Password: courier123
```

### 3. Dashboard
- 📊 Statistics cards
- 🗺️ Interactive map with Leaflet
- 📋 Orders list with details

---

## 🏗️ Architecture

### Backend
- **Laravel 11** - PHP Framework
- **PostgreSQL 18** - Database
- **Eloquent ORM** - Models & Relationships
- **Breeze** - Authentication

### Frontend
- **Blade Templates** - Server-side rendering
- **Tailwind CSS** - Styling
- **Leaflet.js** - Interactive maps
- **Alpine.js** - JavaScript reactivity

---

## 📊 Database Schema

**6 Tables:**
- `couriers` - Courier accounts with authentication
- `customers` - Customer information
- `orders` - Delivery orders
- `tracking_data` - GPS coordinates history
- `delivery_events` - Status change events
- `reschedules` - Rescheduled deliveries

---

## 🔑 Features

### Authentication System
✅ Custom courier guard  
✅ Session management  
✅ Password hashing (bcrypt)  
✅ Remember me functionality

### Dashboard
✅ Real-time statistics  
✅ Interactive Leaflet map  
✅ Courier location marker (blue)  
✅ Order markers (red/green)  
✅ Detailed orders list  
✅ Responsive design

### Order Management
✅ CRUD operations  
✅ Status tracking  
✅ Customer details  
✅ GPS coordinates  
✅ Payment tracking

---

## 📂 Project Structure

```
kuosht-tracking-laravel/
├── app/
│   ├── Http/Controllers/
│   │   ├── Auth/CourierAuthController.php
│   │   ├── CourierDashboardController.php
│   │   ├── OrderController.php
│   │   └── TrackingController.php
│   └── Models/
│       ├── Courier.php
│       ├── Customer.php
│       ├── Order.php
│       ├── TrackingData.php
│       ├── DeliveryEvent.php
│       └── Reschedule.php
├── database/
│   ├── migrations/ (6 tables)
│   └── seeders/
├── resources/
│   └── views/
│       └── courier/
│           ├── login.blade.php
│           └── dashboard.blade.php
└── routes/
    └── web.php
```

---

## 🛠️ Commands

### Development
```bash
# Start server
php artisan serve

# Build assets
npm run build

# Watch for changes
npm run dev
```

### Database
```bash
# Run migrations
php artisan migrate

# Seed test data
php artisan db:seed

# Fresh start
php artisan migrate:fresh --seed
```

### Utilities
```bash
# List routes
php artisan route:list

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

## 🔐 Credentials

### Courier Accounts
| Name | Email | Password |
|------|-------|----------|
| Leart Krasniqi | leart@kuosht.com | courier123 |
| Driton Shala | driton@kuosht.com | courier123 |
| Blerina Gashi | blerina@kuosht.com | courier123 |

### Database
```
Host:     localhost
Port:     5432
Database: kuosht_tracking_laravel
User:     postgres
Password: postgres
```

---

## 📖 Documentation

- [LARAVEL-SETUP.md](kuosht-tracking-laravel/LARAVEL-SETUP.md) - Setup guide
- [TESTING-GUIDE.md](kuosht-tracking-laravel/TESTING-GUIDE.md) - Testing instructions

---

## 🎨 Tech Stack

**Backend:**
- Laravel 11
- PostgreSQL 18
- PHP 8.2

**Frontend:**
- Blade Templates
- Tailwind CSS 3
- Leaflet 1.9.4
- Alpine.js

**Tools:**
- Composer
- NPM
- Vite

---

## ✨ Key Features

### 📍 GPS Tracking
- Real-time location updates
- Interactive map visualization
- Route history
- Marker clustering

### 📦 Order Management
- Create, read, update, delete
- Status tracking
- Customer information
- Payment tracking

### 👤 Courier Dashboard
- Personal statistics
- Assigned orders
- Interactive map
- Real-time updates

---

## 🚀 Deployment

### Requirements
- PHP >= 8.2
- PostgreSQL >= 15
- Composer
- Node.js & NPM

### Production Setup
```bash
# Install dependencies
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Configure environment
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate --force

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📝 License

Proprietary - © 2026 KUOSHT. All rights reserved.

---

## 🆘 Support

**Issues:** Report at project repository  
**Email:** support@kuosht.com

---

**Built with ❤️ using Laravel**
