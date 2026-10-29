@echo off
chcp 65001 > nul
set MONGOSH="D:\MongoDB\bin\mongosh.exe"
set MONGO_URI=mongodb://127.0.0.1:27017/quanly_doan

cd /d "D:\laragon\www\Student-Project-Management-System-Using-MongoDB"

echo [*] Reset du lieu cu neu co de nap moi...
%MONGOSH% %MONGO_URI% --quiet --eval "db.dropDatabase()"

echo [*] 1. Tao 15 collection va chi muc...
%MONGOSH% %MONGO_URI% --file "mongodb/01_tao_collections.js"

echo [*] 2. Import 32 documents tu du_lieu_mau...
%MONGOSH% %MONGO_URI% --file "mongodb/02_import_du_lieu_mau.js"

echo [*] 3. Kiem tra truy van du lieu...
%MONGOSH% %MONGO_URI% --file "mongodb/03_truy_van_kiem_tra.js"

echo.
echo ============================================================
echo [OK] DA NAP THANH CONG DU LIEU VAO MONGODB!
echo ============================================================
echo.
