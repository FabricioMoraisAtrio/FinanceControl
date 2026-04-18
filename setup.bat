@echo off
cd /d "%~dp0"

:: Adiciona PHP e MySQL do XAMPP ao PATH desta sessao
set PATH=C:\xampp\php;C:\xampp\mysql\bin;%PATH%

:: Tenta encontrar Composer globalmente ou dentro do XAMPP
where composer >nul 2>&1 || set PATH=C:\xampp\php;%PATH%

echo ========================================
echo  FinanceControl - Setup inicial
echo ========================================
echo.

echo [1/5] Instalando dependencias PHP (Composer)...
composer install --no-interaction
echo.

echo [2/5] Configurando .env...
if not exist .env (
    copy .env.example .env
    php artisan key:generate
    echo .env criado e chave gerada.
) else (
    echo .env ja existe, pulando.
)
echo.

echo [3/5] Instalando dependencias JS (npm)...
npm install
echo.

echo [4/5] Compilando assets...
npm run build
echo.

echo [5/5] Rodando migrations...
php artisan migrate --force
echo.

echo ========================================
echo  Setup concluido! Inicie com start.bat
echo ========================================
pause
