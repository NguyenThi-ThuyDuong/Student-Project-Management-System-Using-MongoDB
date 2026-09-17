@echo off
chcp 65001 > nul
echo ==================================================
echo 🚀 THIẾT LẬP CSDL MONGO DB VÀ NẠP DỮ LIỆU MẪU (MONGOSH)
echo ==================================================

mongosh mongodb://127.0.0.1:27017/quanly_doan mongodb/run_all.js

echo.
echo ==================================================
echo 🎉 ĐÃ HOÀN THÀNH SETUP MONGO DB TỪ MONGOSH!
echo ==================================================
pause
