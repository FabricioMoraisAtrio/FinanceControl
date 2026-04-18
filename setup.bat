@echo off
cd /d "%~dp0"

:: PHP do XAMPP no PATH
set PATH=C:\xampp\php;C:\xampp\mysql\bin;%PATH%

echo ========================================
echo  FinanceControl - Setup inicial
echo ========================================
echo.

:: Verifica se PHP existe
php -v >nul 2>&1
if errorlevel 1 (
    echo ERRO: PHP nao encontrado em C:\xampp\php
    echo Verifique se o XAMPP esta instalado em C:\xampp
    pause
    exit /b
)

:: Verifica/baixa Composer
if not exist composer.phar (
    echo Baixando Composer...
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php
    del composer-setup.php
    echo.
)

echo [1/5] Instalando dependencias PHP...
php composer.phar install --no-interaction
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

echo [3/5] Instalando dependencias JS...
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
