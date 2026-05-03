# Project setup

Steps transcribed from `steps for project setup.txt`.

## Stack

- **Theme:** AdminLTE 3  
- **Backend:** Laravel with Vite  
- **UI:** Livewire  

---

## Git

```bash
git clone <url of git>

git checkout staging
git checkout -b <new-branch-name>
```

---

## Configuration files

Copy examples to local files:

```bash
cp .env.example .env
cp docker-compose.yml.example docker-compose.yml
cp Dockerfile.example Dockerfile
```

---

## Docker

Build and start containers:

```bash
docker-compose up -d --build
```

Open a shell in the app container:

```bash
docker-compose exec app bash
```

Inside the container (or on the host if you mirror these commands):

### PHP dependencies

```bash
composer install
```

### Frontend

```bash
npm install
npm run build
```

### Laravel

```bash
php artisan key:generate
php artisan migrate
```

---

## `.env` database settings (example)

Configure MySQL in `.env` as needed. Example from the original steps:

```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=pulse-data
DB_USERNAME=root
DB_PASSWORD=root
```

Adjust database name, host, user, and password to match your `docker-compose.yml` and local setup.

---

## Seeders (roles and permissions)

```bash
php artisan db:seed --class=ModuleAndPermissionSeeder
php artisan db:seed --class=SystemAdminSeeder
```

---

## Optional: controller scaffold

```bash
php artisan make:controller MasterApp/PermissionController
```

---

## DB baseline

### Method 1 — schema only (Paperboy DB structure)

```bash
php artisan migrate:fresh
php artisan db:seed
```

### Method 2 — Paperboy structure **and** data

1. Obtain the latest dataset dump with structure.

2. In migration `0001_01_01_000000_baseline_schema.php`, **uncomment** line 15:

   ```php
   DB::unprepared(file_get_contents(database_path('/schema/doorPulsePaperBoyBaseline.sql')));
   ```

3. Run:

   ```bash
   php artisan migrate
   php artisan db:seed
   ```

---

## Notes

- Prefer **`docker compose`** (plugin) if your Docker installation uses it; **`docker-compose`** is the legacy standalone binary. Use whichever matches your machine.
- If this repository uses different seeders, migration names, or SQL baseline paths than above, follow the versions present in **this** repo’s `database/` folder.
