VideoScheduler
A dashboard application for managing video schedules, built with Laravel and Vue.js.
Prerequisites

PHP >= 8.0
Composer
Node.js & npm
MySQL

Installation

Clone the repository:
git clone <repository-url>
cd videoscheduler-laravel


Install PHP dependencies:
composer install


Install JavaScript dependencies:
npm install
npm run dev


Copy the .env.example to .env and configure your database:
cp .env.example .env


Generate application key:
php artisan key:generate


Run migrations:
php artisan migrate


Seed the database (optional):
php artisan db:seed


Serve the application:
php artisan serve



Usage

Access the application at http://localhost:8000.
Register or login to access the dashboard.
Navigate through the sections: Dashboard, Schedules, Videos, Logs, and Settings.

License
MIT
