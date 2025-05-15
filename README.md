
# 🚀 Symfony Project Setup Guide

Welcome to the **EasyTrip WebApp** repository! This guide will help you quickly set up the project, configure the environment, and import the database.

---

## 📚 Prerequisites

- PHP >= 8.1
- Composer (Dependency Manager)  
  [Install Composer](https://getcomposer.org/download/)
- XAMPP (Apache + MySQL)  
  [Download XAMPP](https://www.apachefriends.org/index.html)
- Node.js & npm (For using Webpack Encore or front-end assets)  
  [Download Node.js](https://nodejs.org/)

---

## 📥 Clone the Repository

```bash
git clone https://github.com/mAmineSouissi/easyTrip_WebApp
cd easyTrip_WebApp
```

---

## ⚙️ Install Dependencies

```bash
composer install
```

If the project uses front-end assets:

```bash
npm install
npm run dev   # or npm run build for production
```

---

## 🗂️ Configure Environment

1. Duplicate the `.env.example` file:

```bash
cp .env.exmaple .env.local
```

2. Update the `.env.local` file with your api keys configuration:

```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/your_database_name"
```

> 📝 *By default, XAMPP uses `root` with an empty password. Adjust if necessary.*

---

## 🗃️ Import Database via XAMPP (phpMyAdmin)

1. Start **Apache** and **MySQL** from the XAMPP Control Panel.  
2. Open [phpMyAdmin](http://localhost/phpmyadmin).  
3. Create a new database:

   - Click on **Databases** > Enter your database name > Click **Create**.

4. Import `.sql` File:

   - Select the created database.
   - Go to the **Import** tab.
   - Click **Choose File** and select your `.sql` file.
   - Click **Go** to execute the import.

---

## 📦 Run Database Migrations (If Needed)

If the database is empty or you want to regenerate tables via Doctrine:

```bash
php bin/console doctrine:migrations:migrate
```

Or to reset the schema:

```bash
php bin/console doctrine:schema:update --force
```

---

## ▶️ Run the Project

Start the Symfony local server:

```bash
symfony server:start
```

Or using PHP’s built-in server:

```bash
php -S 127.0.0.1:8000 -t public
```

Visit [http://127.0.0.1:8000](http://127.0.0.1:8000) to view the application.

---

## ✅ Common Commands

| Task               | Command                         |
|--------------------|---------------------------------|
| Clear Cache        | `php bin/console cache:clear`  |
| Create Migration   | `php bin/console make:migration` |
| Run Migrations     | `php bin/console doctrine:migrations:migrate` |
| Generate Entities  | `php bin/console make:entity`  |
| Check Routes       | `php bin/console debug:router` |

---

## 📞 Need Help?

If you encounter any issues, feel free to open an issue or contact the project maintainers.