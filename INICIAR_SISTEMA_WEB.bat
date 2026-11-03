@echo off
title Sistema Monitoreo Invernadero COTECNOVA
echo =========================================================
echo   INICIANDO SERVIDOR WEB LOCAL (PHP)
echo   Proyecto: Invernadero COTECNOVA + Bitacora TinyML
echo =========================================================
echo.

set PHP_BIN=
where php >nul 2>nul
if %ERRORLEVEL% equ 0 (
    set PHP_BIN=php
) else if exist "C:\xampp\php\php.exe" (
    set PHP_BIN="C:\xampp\php\php.exe"
) else if exist "%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.2_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" (
    set PHP_BIN="%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.2_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
)

if "%PHP_BIN%"=="" (
    echo [ERROR] No se encontro el ejecutable de PHP.
    pause
    exit /b 1
)

echo Servidor escuchando en: http://localhost:8085
echo Presiona Ctrl + C en esta ventana para detener el servidor.
echo.

start http://localhost:8085

%PHP_BIN% -S localhost:8085 -t "%~dp0stem"
pause

