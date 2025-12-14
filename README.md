# WIZniche Job Costing Analyzer

A profit margin calculator built for service businesses, specifically tailored for Radon mitigation workflows.

## Tech Stack

**Backend:**
- Laravel 11
- MySQL
- PHP 8.3

**Frontend:**
- Nuxt 3
- Tailwind CSS
- Chart.js

## Architecture Highlights

- **Service-based business logic separation** - Profit calculations abstracted into `ProfitCalculatorService`
- **Eloquent relationship optimization** - Eager loading to prevent N+1 query issues
- **Responsive UI** - Mobile-first design matching WIZniche aesthetic

## Features

- Real-time profit margin calculation
- Job-by-job cost breakdown (labor + materials)
- Visual profit analysis by job type
- Radon industry-specific data modeling

## Local Setup

### Backend
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

### Frontend
```bash
npm install
npm run dev
```

## Demo

Frontend: https://wizniche-demo.mirrorlog.com
Backend API: https://api-wizniche-demo.mirrorlog.com

---

Built as a demonstration of full-stack architecture for WIZniche's Senior Full Stack Engineer role.

Demonstrates:
- Laravel best practices (Services, Eloquent optimization)
- Modern Nuxt development
- Production deployment experience
- Domain knowledge application (Radon industry)