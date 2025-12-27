@echo off
chcp 65001 >nul
title Battery Tracker - Установка

echo ╔══════════════════════════════════════════════════════════════╗
echo ║         BATTERY TRACKER - УСТАНОВКА ПРОЕКТА                  ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.

set XAMPP_PATH=C:\xampp
set PROJECT_NAME=battery_tracker

if not exist "%XAMPP_PATH%" (
    echo [ОШИБКА] XAMPP не найден в %XAMPP_PATH%
    echo.
    echo Скачайте XAMPP: https://www.apachefriends.org/download.html
    echo После установки запустите этот скрипт снова.
    echo.
    pause
    exit /b 1
)

echo [1/4] XAMPP найден: %XAMPP_PATH%
echo.

echo [2/4] Копирование файлов проекта...
if exist "%XAMPP_PATH%\htdocs\%PROJECT_NAME%" (
    echo       Папка уже существует, обновляем файлы...
)
xcopy /E /I /Y "%~dp0*" "%XAMPP_PATH%\htdocs\%PROJECT_NAME%\" >nul 2>&1
echo       Файлы скопированы в %XAMPP_PATH%\htdocs\%PROJECT_NAME%
echo.

echo [3/4] Запуск Apache и MySQL...
start "" "%XAMPP_PATH%\xampp_start.exe" >nul 2>&1
if errorlevel 1 (
    echo       Запускаем через control panel...
    start "" "%XAMPP_PATH%\xampp-control.exe"
)
timeout /t 3 >nul
echo       Серверы запущены
echo.

echo [4/4] Открытие браузера...
echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║  ВАЖНО! Выполните импорт базы данных:                        ║
echo ║                                                              ║
echo ║  1. Откроется phpMyAdmin                                     ║
echo ║  2. Нажмите вкладку "Импорт"                                 ║
echo ║  3. Выберите файл: database.sql                              ║
echo ║  4. Нажмите "Вперёд"                                         ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.

timeout /t 2 >nul
start "" "http://localhost/phpmyadmin"

echo.
echo После импорта базы данных нажмите любую клавишу для открытия сайта...
pause >nul

start "" "http://localhost/%PROJECT_NAME%/"

echo.
echo ════════════════════════════════════════════════════════════════
echo   Установка завершена!
echo   Сайт: http://localhost/%PROJECT_NAME%/
echo ════════════════════════════════════════════════════════════════
echo.
pause

