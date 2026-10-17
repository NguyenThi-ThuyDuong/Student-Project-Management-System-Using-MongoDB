# HƯỚNG DẪN CẤU HÌNH VÀ SỬ DỤNG REDIS - ĐỒ ÁN NoSQL HUIT

Dự án **Hệ Thống Quản Lý Đồ Án & Khóa Luận Tốt Nghiệp HUIT** sử dụng **Redis** làm **Key-Value Store** để xử lý 3 chức năng chính theo đúng bản chất CSDL In-Memory:

---

## I. CÁC TÍNH NĂNG REDIS TRONG HỆ THỐNG

### 1. Feature 1 – OTP Quên Mật Khẩu
- **Mô tả**: Khi người dùng gửi yêu cầu quên mật khẩu, hệ thống tự động sinh mã OTP ngẫu nhiên 6 chữ số và lưu vào Redis.
- **Redis Key**: `otp:{username}` (Ví dụ: `otp:2001201234`)
- **TTL**: 300 giây (5 phút - tự động xóa sau khi hết hạn).
- **Quy trình**: `Generate OTP` -> `Lưu Redis (setex 300s)` -> `Kiểm tra OTP` -> `Xóa key khi xác thực thành công`.

### 2. Feature 2 – Cache Danh Sách Đề Tài
- **Mô tả**: Lưu bộ nhớ tạm danh sách đề tài thuộc từng Lớp Học Phần để giảm tải truy vấn MongoDB khi nhiều sinh viên truy cập đồng thời.
- **Redis Key**: `cache:de_tai:{lop_hoc_phan_id}` (Ví dụ: `cache:de_tai:LHP001`)
- **TTL**: 3600 giây (1 giờ).
- **Invalidation**: Khi Giảng viên/Giáo vụ tạo mới, chỉnh sửa, xóa hoặc đổi trạng thái phê duyệt đề tài, Redis key tương ứng sẽ tự động được xóa (`Cache::forget`) để lấy dữ liệu mới nhất từ MongoDB.

### 3. Feature 3 – Cache Dashboard Admin Statistics
- **Mô tả**: Cache tổng quan số liệu thống kê trên Dashboard Admin.
- **Redis Key**: `cache:dashboard:admin`
- **TTL**: 600 giây (10 phút).
- **Structure**:
```json
{
    "tong_sinh_vien": 120,
    "tong_giang_vien": 15,
    "tong_nhom": 30,
    "tong_de_tai": 45,
    "de_tai_da_duyet": 40,
    "bao_cao_da_nop": 85,
    "nhom_cham_tien_do": 3
}
```

---

## II. TRANG GIÁM SÁT REAL-TIME (DEMO)
- Đường dẫn Admin: **Admin -> NoSQL Monitoring (Demo)** (`/admin/nosql-monitor`).
- Hiển thị trực tiếp:
  - Trạng thái kết nối Redis server.
  - Danh sách Redis Keys đang tồn tại kèm **Type**, **TTL còn lại (s)**, và **Sample Value**.

---

## III. CÁCH KHỞI CHẠY REDIS LOCAL (WINDOWS)
1. Mở PowerShell / Command Prompt với quyền Admin:
   ```cmd
   redis-server
   ```
2. Hoặc sử dụng Memurai / Docker Redis:
   ```bash
   docker run -d -p 6379:6379 --name redis-huit redis:latest
   ```
3. Cấu hình `.env`:
   ```env
   CACHE_STORE=redis
   REDIS_CLIENT=predis
   REDIS_HOST=127.0.0.1
   REDIS_PORT=6379
   ```
