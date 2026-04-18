@echo off
cd /d "%~dp0"

set PATH=C:\xampp\php;C:\xampp\mysql\bin;%PATH%

:: Descobre o IP local automaticamente
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /i "IPv4"') do (
    set IP=%%a
    goto :found
)
:found
set IP=%IP: =%

:: Atualiza APP_URL no .env com o IP atual
php -r "
    \$env = file_get_contents('.env');
    \$env = preg_replace('/^APP_URL=.*/m', 'APP_URL=http://%IP%:8000', \$env);
    file_put_contents('.env', \$env);
" 2>nul

echo ========================================
echo  FinanceControl iniciando...
echo ========================================
echo.
echo  Acesso local:   http://localhost:8000
echo  Acesso na rede: http://%IP%:8000
echo.
echo  Compartilhe o endereco da rede com
echo  outros dispositivos no mesmo Wi-Fi.
echo ========================================
echo.

start "FinanceControl" cmd /k "C:\xampp\php\php.exe artisan serve --host=0.0.0.0 --port=8000"
timeout /t 2 >nul
start http://localhost:8000
