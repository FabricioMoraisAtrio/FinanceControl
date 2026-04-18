@echo off
cd /d "%~dp0"

:: Adiciona PHP do XAMPP ao PATH desta sessao
set PATH=C:\xampp\php;C:\xampp\mysql\bin;%PATH%

start "FinanceControl" cmd /k "php artisan serve --host=0.0.0.0 --port=8000"
timeout /t 2 >nul
start http://localhost:8000
