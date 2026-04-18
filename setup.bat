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
    echo Verifique se o XAMPP esta instalado em C:\xampp
    pause & exit /b
)

:: Corrige aviso de extensao invalida no php.ini do XAMPP
echo Verificando php.ini...
php -r "echo php_ini_loaded_file();" > tmp_ini_path.txt 2>nul
set /p INI_PATH=<tmp_ini_path.txt
del tmp_ini_path.txt

:: Remove linha com extensao invalida (.so no Windows)
powershell -Command "(Get-Content '%INI_PATH%') | Where-Object { $_ -notmatch '\.so$' } | Set-Content '%INI_PATH%'"
echo php.ini corrigido.
echo.

:: Baixa Composer se necessario
if not exist composer.phar (
    echo Baixando Composer...
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    if not exist composer-setup.php (
        echo ERRO: Falha ao baixar o Composer. Verifique sua conexao com a internet.
        pause & exit /b
    )
    php composer-setup.php
    del composer-setup.php
    echo.
)

echo [1/5] Instalando dependencias PHP...
php composer.phar install --no-interaction
if not exist vendor\autoload.php (
    echo ERRO: composer install falhou. Verifique a saida acima.
    pause & exit /b
)
echo.

echo [2/5] Configurando .env...
if not exist .env (
    copy .env.example .env
    php artisan key:generate
    echo .env criado e chave gerada.
    echo.
    echo IMPORTANTE: Abra o arquivo .env e configure:
    echo   DB_DATABASE=financecontrol
    echo   DB_USERNAME=root
    echo   DB_PASSWORD=
    echo.
    pause
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
