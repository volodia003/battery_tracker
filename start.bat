@echo off
chcp 65001 >nul
title Battery Tracker - Запуск

set XAMPP_PATH=C:\xampp
set PROJECT_NAME=battery_tracker

echo ╔══════════════════════════════════════════════════════════════╗
echo ║              BATTERY TRACKER - ЗАПУСК                        ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.

if not exist "%XAMPP_PATH%" (
    echo [ОШИБКА] XAMPP не найден!
    echo Сначала запустите install.bat
    pause
    exit /b 1
)

echo [1/2] Запуск серверов XAMPP...
start "" "%XAMPP_PATH%\xampp_start.exe" >nul 2>&1
if errorlevel 1 (
    start "" "%XAMPP_PATH%\xampp-control.exe"
)
timeout /t 3 >nul
echo       Готово!
echo.

echo [2/2] Открытие сайта...
timeout /t 1 >nul
start "" "http://localhost/%PROJECT_NAME%/"

echo.
echo ════════════════════════════════════════════════════════════════
echo   Battery Tracker запущен!
echo   http://localhost/%PROJECT_NAME%/
echo ════════════════════════════════════════════════════════════════
echo.
echo Нажмите любую клавишу для закрытия этого окна...
pause >nul

