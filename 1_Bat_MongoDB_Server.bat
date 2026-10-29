@echo off
title MongoDB Server - Port 27017
color 0B
echo ============================================================
echo   KHOI DONG MONGODB SERVER (127.0.0.1:27017)
echo   DATABASE: quanly_doan
echo   (Vui long KHONG TAT cua so nay khi dang chay ung dung)
echo ============================================================
"D:\MongoDB\bin\mongod.exe" --dbpath "D:\MongoDB\data" --bind_ip 127.0.0.1 --port 27017
pause
