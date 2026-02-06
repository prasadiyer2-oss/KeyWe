KeyWe - Real Estate Transaction Platform
This is the backend and admin repository for KeyWe, built with Laravel and Orchid Platform.

🛠 Prerequisites
Ensure you have the following installed on your local machine:

PHP >= 8.2

Composer

MySQL (or compatible database)

Redis (for session & caching)

Node.js & NPM

🚀 Installation & Setup (From Scratch)
Follow these steps to set up the project locally.

1. Clone the Repository
Bash
git clone https://github.com/your-org/keywe-backend.git
cd keywe-backend
2. Install Dependencies
Install PHP and JavaScript dependencies:

Bash
composer install
npm install
3. Environment Configuration
Copy the example environment file and generate the application key:

Bash
cp .env.example .env
php artisan key:generate
Configure Database & Redis: Open the .env file and update your database credentials:

Ini, TOML
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=keywe_db
DB_USERNAME=root
DB_PASSWORD=

# Session & Cache (Recommended: Redis)
SESSION_DRIVER=redis
CACHE_STORE=redis
4. Database Migration & Storage
Run the migrations to set up the schema and link the public storage:

Bash
php artisan migrate
php artisan db:seed
php artisan storage:link
5. Create Super Admin (Orchid)
To create the initial Super Admin user with full permissions (Dashboard access, Role management, etc.), run the official Orchid command:

Bash
# Syntax: php artisan orchid:admin [username] [email] [password]
php artisan orchid:admin admin admin@keywe.com password
Note: This command automatically grants the platform.index permission required to access the admin panel.

6. Compile Assets
Compile the frontend assets for the dashboard and custom views:

Bash
npm run build
7. Run the Application
Start the local development server:

Bash
php artisan serve
You can now access the Admin Dashboard at: http://localhost:8000/admin/login

🔑 Useful Commands
Create a New Admin (Interactive)
If you prefer a step-by-step prompt:

Bash
php artisan orchid:admin