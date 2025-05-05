# Tiktok Clone

##  Tech Stack

- **Frontend**: Vue 3, Tailwind CSS
- **Backend**: Laravel 11 (PHP 8+), MySQL
- **API Communication**: Axios

## Frontend

```
git clone https://github.com/A-Arhamm/tiktok-clone

cd frontend

npm i

npm run dev
```
Inside Plugins/axios.js make sure the baseUrl is the same as your API.

### For this Tiktok Clone to work you'll need the API/Backend:

## Backend

```
cd backend

composer install 

cp .env.example .env 

php artisan cache:clear 

composer dump-autoload 

php artisan key:generate

composer require laravel/breeze --dev

php artisan breeze:install (FOR THIS SELECT THE API INSTALL)

php artisan serve
```

Create a DATABASE. Make sure the DB_DATABASE in the .env is the same and then run this command 
```
php artisan migrate
```

You should be good to go!
