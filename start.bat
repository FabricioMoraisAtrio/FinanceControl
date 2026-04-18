@echo off
cd /d "%~dp0"
start "FinanceControl" cmd /k "php artisan serve --host=0.0.0.0 --port=8000"
timeout /t 2 >nul
start http://localhost:8000
