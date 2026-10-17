# HƯỚNG DẪN THIẾT LẬP VÀ MÔ PHỎNG REDIS DEMO

## 1. Yêu cầu môi trường
- PHP >= 8.3
- Redis Server (port 6379)
- Composer package `predis/predis` (đã được cài đặt sẵn trong dự án)

## 2. Kiểm tra Redis kết nối
Chạy lệnh kiểm tra trong terminal:
```bash
redis-cli ping
```
Nếu nhận kết quả `PONG` nghĩa là Redis server đang hoạt động bình thường.

## 3. Các bước Demo cho Giảng viên xem trực tiếp:
1. Mở trình duyệt và đăng nhập tài khoản **Admin**.
2. Truy cập **Hệ Thống & Báo Cáo -> NoSQL Monitoring (Demo)** (`http://localhost:8000/admin/nosql-monitor`).
3. Mở một tab trình duyệt khác (hoặc tab ẩn danh):
   - Truy cập trang Quên mật khẩu `http://localhost:8000/password/reset-request`.
   - Nhập Mã số và gửi yêu cầu -> Hệ thống lập tức ghi mã OTP vào Redis key `otp:{username}`.
4. Quay lại trang **NoSQL Monitoring**, bấm nút **Làm mới trạng thái**:
   - Bạn sẽ thấy Key `otp:{username}` xuất hiện trong bảng Redis với thời gian TTL lùi dần từ 300s về 0s.
5. Truy cập Đăng ký đề tài Sinh viên hoặc Dashboard Admin để quan sát `cache:de_tai:{lhp_id}` và `cache:dashboard:admin`.
