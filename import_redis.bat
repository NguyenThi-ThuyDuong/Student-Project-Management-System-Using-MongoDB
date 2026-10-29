@echo off
chcp 65001 > nul
set REDIS_CLI="D:\RedisLab\Memurai\memurai-cli.exe"

%REDIS_CLI% -h 127.0.0.1 -p 6379 SET otp:SV100 "583921" EX 300
%REDIS_CLI% -h 127.0.0.1 -p 6379 SET otp:GV200 "912405" EX 180
%REDIS_CLI% -h 127.0.0.1 -p 6379 SET cache:de_tai:65f1a2b3c4d5e6f7a8b9c0d1 "[{\"_id\":\"65f1a2b3c4d5e6f7a8b9c0d1\",\"TenDeTai\":\"Xay dung website Quan ly Do an\",\"TrangThai\":\"Dang mo dang ky\"}]" EX 3600
%REDIS_CLI% -h 127.0.0.1 -p 6379 SET cache:dashboard:admin "{\"tong_sinh_vien\":120,\"tong_giang_vien\":15,\"tong_nhom\":30,\"tong_de_tai\":45,\"de_tai_da_duyet\":40,\"bao_cao_da_nop\":85,\"nhom_cham_tien_do\":3}" EX 600
%REDIS_CLI% -h 127.0.0.1 -p 6379 HSET nosql_demo:session:demo001 TenDangNhap sv_demo1 VaiTro SinhVien MaSV SV001
%REDIS_CLI% -h 127.0.0.1 -p 6379 EXPIRE nosql_demo:session:demo001 1200
%REDIS_CLI% -h 127.0.0.1 -p 6379 ZADD leaderboard:diem_do_an 8.5 "Nhom minh hoa 01" 9.0 "Nhom minh hoa 02" 7.5 "Nhom minh hoa 03"

echo.
echo ============================================================
echo [OK] DA NAP THANH CONG DU LIEU VAO REDIS!
echo ============================================================
echo.
