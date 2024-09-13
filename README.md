## Clone the Repository

git clone https://github.com/renzosiza7/api-academico.git
cd your-repository

## Install Composer Dependencies

composer install

## Copy the Environment File

cp .env.example .env

## Generate Application Key

php artisan key:generate

## Configure the Database

Update the .env file with your database credentials:

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password

create database siac_test in mysql and configure connection:

DB_CONNECTION2=mysql
DB_HOST2=127.0.0.1
DB_PORT2=3306
DB_DATABASE2=your_database_name
DB_USERNAME2=your_username
DB_PASSWORD2=your_password

## Run Database Migrations

php artisan migrate

## Generate JWT_SECRET

php artisan jwt:secret

## Start the Development Server

php artisan serve --port=8000# api-unsa-app
