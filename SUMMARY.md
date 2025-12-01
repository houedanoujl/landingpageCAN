# Shell Commands Summary

To initialize the project and run it, follow these steps:

1. **Install Dependencies (if not already installed via Docker)**
   Since this is a Laravel project, you would typically run:
   ```bash
   docker run --rm \
       -u "$(id -u):$(id -g)" \
       -v "$(pwd):/var/www/html" \
       -w /var/www/html \
       laravelsail/php83-composer:latest \
       composer install --ignore-platform-reqs
   ```

2. **Start Docker Containers**
   Using Laravel Sail:
   ```bash
   ./vendor/bin/sail up -d
   ```

3. **Run Migrations**
   Initialize the database schema:
   ```bash
   ./vendor/bin/sail artisan migrate
   ```

4. **Additional Setup (Optional)**
   - Run seeders (if any): `./vendor/bin/sail artisan db:seed`
   - Create a filament user: `./vendor/bin/sail artisan make:filament-user`

5. **Access the Application**
   - API: `http://localhost/api`
   - Admin Panel: `http://localhost/admin`
