@echo off
cd /d "%~dp0"

set PATH=C:\xampp\php;C:\xampp\mysql\bin;%PATH%

echo ========================================
echo  FinanceControl - Setup inicial
echo ========================================
echo.

:: Verifica PHP
php -v >nul 2>&1
if errorlevel 1 (
    echo ERRO: PHP nao encontrado em C:\xampp\php
    echo Instale o XAMPP: https://www.apachefriends.org/
    pause & exit /b
)

:: Verifica versao do PHP (precisa 8.2+)
php -r "exit(PHP_MAJOR_VERSION >= 8 && PHP_MINOR_VERSION >= 2 ? 0 : 1);" 2>nul
if errorlevel 1 (
    echo.
    echo ERRO: PHP 8.2 ou superior e necessario.
    echo Versao encontrada: inferior a 8.2
    echo Atualize o XAMPP em: https://www.apachefriends.org/
    echo.
    pause & exit /b
)
echo PHP OK.
echo.

:: Habilita extensao zip no php.ini (necessaria para o Composer)
echo Habilitando extensao zip no php.ini...
for /f %%i in ('php -r "echo php_ini_loaded_file();"') do set INI=%%i
powershell -Command "(Get-Content '%INI%') -replace '^;extension=zip', 'extension=zip' | Set-Content '%INI%'"
echo Feito.
echo.

:: Baixa Composer se necessario
if not exist composer.phar (
    echo Baixando Composer...
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --quiet
    del composer-setup.php
    echo Composer baixado.
    echo.
)

echo [1/5] Instalando dependencias PHP...
php composer.phar install --no-interaction --prefer-dist
if not exist vendor\autoload.php (
    echo.
    echo ERRO: composer install falhou. Veja a mensagem acima.
    pause & exit /b
)
echo.

echo [2/5] Configurando .env...
if not exist .env (
    copy .env.example .env >nul
    php artisan key:generate
    echo.
    echo IMPORTANTE: Abra o arquivo .env e configure:
    echo   DB_CONNECTION=mysql
    echo   DB_HOST=127.0.0.1
    echo   DB_PORT=3306
    echo   DB_DATABASE=financecontrol
    echo   DB_USERNAME=root
    echo   DB_PASSWORD=
    echo.
    echo Pressione qualquer tecla apos salvar o .env...
    pause >nul
) else (
    echo .env ja existe, pulando.
)
echo.

echo [3/5] Instalando dependencias JS...
npm install --silent
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
