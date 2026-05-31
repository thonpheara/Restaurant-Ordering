# 🍔 FastBite - Premium Restaurant Ordering System

![Premium Dashboard](/Users/apple/.gemini/antigravity/brain/8c70b690-8961-4e42-9053-e4f16238cade/dashboard_mockup_1776701000713.png)

FastBite is a high-performance, full-featured restaurant management and ordering ecosystem built with **Laravel 12**. Designed for modern dining establishments, it prioritizes speed, visual excellence, and operational intelligence.

---

## 🌟 Modern Feature Suite

### 🏛️ Smart Admin Dashboard
*Manage your entire business from a single, glassmorphic interface.*
- **Live Analytics**: Monitor sales, average order value, and daily volume at a glance.
- **Menu Architecture**: Create, update, and categorize products with real-time stock tracking.
- **Reservation Engine**: Real-time table booking management with guest tracking.
- **Staff Governance**: Role-based access control (Admin/Staff) with audit logs.

### 🍱 Premium Customer Experience
*Provide your guests with a sub-second, mobile-responsive ordering journey.*
- **Reactive Menu**: Instant category filtering and smooth cart management.
- **Personalization**: Support for Light/Dark modes and saved user preferences.
- **Fast Checkout**: Direct table-to-kitchen ordering system.

### 🤖 Intelligent Operations
*Automate your restaurant with background processing and instant alerts.*
- **Telegram Bot**: Instant notifications for new orders and stock warnings.
- **Redis Scaling**: Sub-millisecond data retrieval for sessions and caching.

---

## 🚀 Rapid Setup Guide

### 1. Prerequisites
- **PHP 8.2+** | **Composer** | **Node.js 18+** | **Database** (MySQL/MariaDB)

### 2. High-Speed Installation
To get the system running with all dependencies and sample data using our automated scripts:

```bash
# 1. Clone the project
git clone < repository-url > && cd Restaurant_Ordering_System

# 2. Automated Foundation Setup
composer run setup

# Which is equivalent to running these manually:
# composer install
# cp .env.example .env
# php artisan key:generate
# php artisan migrate --force
# npm install
# npm run build

# 3. Seed Admin Access
php artisan db:seed --class=AdminSeeder
```

> [!IMPORTANT]
> **Default Credentials:**
> - **Username**: `admin` | **Password**: `admin123`
> - *Please change your password immediately after your first login.*

---

## 💾 Performance & Scaling (Redis)

FastBite leverages **Redis** to ensure your application stays responsive even during peak hours.

- **Persistent Sessions**: User sessions are stored in memory for instant transitions.
- **Dynamic Caching**: Critical data like popular dishes and sales stats are cached to reduce database load.
- **Background Workers**: Heavy tasks (notifications, reports) are processed in the background, never slowing down the user.

---

## 📢 Telegram Integration

Connect your restaurant directly to your pocket with instant Telegram alerts.

### Setup Instructions:
1. **Create Bot**: Message [@BotFather](https://t.me/botfather) for your unique **API Token**.
2. **Get ID**: Message your bot and visit the Telegram API updates page to find your **Chat ID**.
3. **Connect**: Update your `.env` file with the **Token** and **Chat ID**.

*The system will automatically notify you of New Orders, Status Changes, and Stock Warnings.*

---

## 📱 Running on Local Network (LAN)

If you want to access the system from a phone, tablet, or another computer on the same WiFi network (e.g., for kitchen staff or waiters):

1. **Build frontend assets** (so you don't need a running Vite server):
   ```bash
   npm run build
   ```
2. **Start the server on your network IP**:
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```
3. **Access it on other devices**:
   Find your computer's IP address (e.g., `[IP_ADDRESS]`) and open this in the device's browser: 
   `http://[IP_ADDRESS]`

---

## 🔧 Maintenance Toolkit

| Category | Command | Purpose |
| :--- | :--- | :--- |
| **Development** | `composer run dev` | Start Server, Vite, and Queues simultaneously |
| **Database** | `php artisan migrate:fresh --seed` | Wipe and re-populate the entire system |
| **Filesystem** | `php artisan storage:link` | Fix missing product images by linking folders |
| **Security** | `php artisan session:clear` | Log out all active users and clear Redis sessions |

---

Designed with ❤️ for modern dining establishments.