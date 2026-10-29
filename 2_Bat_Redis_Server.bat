@echo off
title Redis Server - Port 6379
color 0E
echo ============================================================
echo   KHOI DONG REDIS SERVER (127.0.0.1:6379)
echo   (Vui long KHONG TAT cua so nay khi dang chay ung dung)
echo ============================================================
cd /d "D:\RedisLab\Memurai"
memurai.exe "D:\RedisLab\config\redis-lab.conf"
pause
