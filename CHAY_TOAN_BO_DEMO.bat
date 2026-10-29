@echo off
title He Thong Quan Ly Do An - HUIT
color 0A
chcp 65001 > nul

echo ============================================================
echo   HE THONG QUAN LY DO AN SINH VIEN (MONGODB + REDIS + LARAVEL)
echo ============================================================
echo.

echo [*] 1. Kiem tra va khoi dong MongoDB Server (Port 27017)...
powershell -Command "if (-not (Get-NetTCPConnection -LocalPort 27017 -ErrorAction SilentlyContinue)) { Start-Process -FilePath 'D:\MongoDB\bin\mongod.exe' -ArgumentList '--dbpath \"D:\MongoDB\data\" --bind_ip 127.0.0.1 --port 27017' -WindowStyle Minimized }"

echo [*] 2. Kiem tra va khoi dong Redis Server (Port 6379)...
powershell -Command "if (-not (Get-NetTCPConnection -LocalPort 6379 -ErrorAction SilentlyContinue)) { Start-Process -FilePath 'cmd.exe' -ArgumentList '/c cd /d D:\RedisLab\Memurai && memurai.exe \"D:\RedisLab\config\redis-lab.conf\"' -WindowStyle Minimized }"

timeout /t 2 > nul

echo [*] 3. Nap du lieu mau vao Redis...
call import_redis.bat > nul 2>&1

echo [*] 4. Chuan bi ung dung Laravel...
php artisan config:clear > nul 2>&1
php artisan view:clear > nul 2>&1
php artisan route:clear > nul 2>&1

echo.
echo ============================================================
echo  ?? WEB DANG CHAY TAI: http://localhost:8000
echo.
echo  ?? TAI KHOAN DANG NHAP MAU (Mat khau: DemoNoSQL2026!):
echo     - Admin:      admin_demo
echo     - Giang vien: gv_demo1 / gv_demo2
echo     - Sinh vien:  sv_demo1 den sv_demo5
echo.
echo  (Vui long KHONG TAT cua so nay khi dang demo web)
echo ============================================================
echo.

start http://localhost:8000
php artisan serve
pause
