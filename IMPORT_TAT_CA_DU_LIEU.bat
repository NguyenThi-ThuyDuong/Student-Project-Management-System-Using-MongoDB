@echo off
title Import Toan Bo Du Lieu - MongoDB va Redis
color 0A
chcp 65001 > nul

echo ============================================================
echo   TỰ ĐỘNG NẠP DỮ LIỆU MẪU CHO CẢ MONGODB VÀ REDIS
echo ============================================================
echo.

echo [*] 1. Kiem tra va khoi dong MongoDB Server (Port 27017)...
powershell -Command "if (-not (Get-NetTCPConnection -LocalPort 27017 -ErrorAction SilentlyContinue)) { Start-Process -FilePath 'D:\MongoDB\bin\mongod.exe' -ArgumentList '--dbpath \"D:\MongoDB\data\" --bind_ip 127.0.0.1 --port 27017' -WindowStyle Minimized }"

echo [*] 2. Kiem tra va khoi dong Redis Server (Port 6379)...
powershell -Command "if (-not (Get-NetTCPConnection -LocalPort 6379 -ErrorAction SilentlyContinue)) { Start-Process -FilePath 'cmd.exe' -ArgumentList '/c cd /d D:\RedisLab\Memurai && memurai.exe \"D:\RedisLab\config\redis-lab.conf\"' -WindowStyle Minimized }"

timeout /t 2 > nul

echo [*] 3. Nap du lieu vao MongoDB (15 collection, 32 documents)...
call import_mongodb.bat

echo [*] 4. Nap du lieu vao Redis (OTP, Cache, Session, Leaderboard)...
call import_redis.bat

echo.
echo ============================================================
echo 🎉 HOAN TAT NAP DU LIEU MONGODB + REDIS SAN SANG DEMO!
echo ============================================================
echo.
pause
