@echo off
title FitPulse Lab 7 setup
echo ===== FitPulse Lab 7 setup =====
echo Make sure MySQL is running in the XAMPP Control Panel first.
pause
echo.
echo [1/5] Creating database lab7_2411600026 ...
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS lab7_2411600026 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
if errorlevel 1 (echo Could not create the database. Is MySQL running? & pause & exit /b 1)
echo [2/5] Installing PDF package (needs internet) ...
call composer require barryvdh/laravel-dompdf:^2.0
echo [3/5] Clearing caches ...
php artisan config:clear
echo [4/5] Creating tables ...
php artisan migrate --force
echo [5/5] Loading demo data ...
php artisan db:seed --force
echo.
echo Done. Starting the server at http://127.0.0.1:8000  (login: admin / password123)
php artisan serve
