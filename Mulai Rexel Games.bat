@echo off
setlocal
cd /d "%~dp0"
set "PHP_EXE=php"
where php >nul 2>nul
if errorlevel 1 if exist "C:\xampp\php\php.exe" set "PHP_EXE=C:\xampp\php\php.exe"
if not exist database\database.sqlite type nul > database\database.sqlite
if not exist vendor\autoload.php (
  echo Dependensi Laravel belum ada. Jalankan Composer install terlebih dahulu.
  pause
  exit /b 1
)
%PHP_EXE% artisan migrate --seed --force
if errorlevel 1 (
  echo Inisialisasi database gagal. Pastikan ekstensi pdo_sqlite aktif di PHP.
  pause
  exit /b 1
)
%PHP_EXE% artisan storage:link >nul 2>nul
start "Rexel Games" http://127.0.0.1:8000
%PHP_EXE% artisan serve --host=127.0.0.1 --port=8000
pause
