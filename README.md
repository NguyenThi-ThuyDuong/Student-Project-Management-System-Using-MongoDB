# 🎓 ĐỒ ÁN MÔN DỮ LIỆU NoSQL: HỆ THỐNG QUẢN LÝ ĐỒ ÁN & KHÓA LUẬN TỐT NGHIỆP (HUIT)

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MongoDB](https://img.shields.io/badge/MongoDB-8.0-47A248?style=for-the-badge&logo=mongodb&logoColor=white)
![MongoDB Compass](https://img.shields.io/badge/MongoDB%20Compass-GUI-13AA52?style=for-the-badge&logo=mongodb&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)

Hệ thống Quản lý Đồ án và Khóa luận Tốt nghiệp dành riêng cho **Giảng Viên và Sinh Viên** Trường Đại Học Công Thương TP. Hồ Chí Minh (HUIT). 

Dự án đã được chuyển đổi hoàn toàn kiến trúc lưu trữ từ **MySQL (RDBMS)** sang **MongoDB (NoSQL Document Store)**, đáp ứng đầy đủ yêu cầu bài toán nghiệp vụ, tối ưu hóa truy vấn tài liệu nhúng (Embedded Documents) và tích hợp công cụ **MongoDB Compass** để kiểm thử, quản lý dữ liệu.

---

## 📌 1. Vì Sao KHÔNG Sử Dụng `DatabaseSeeder` Của Laravel?

Trong các dự án Laravel MySQL truyền thống, `DatabaseSeeder.php` thường được dùng để sinh dữ liệu qua Eloquent PHP. Tuy nhiên, đối với bài toán môn **Dữ liệu NoSQL**:

1. **Chuẩn hóa quy trình NoSQL thuần:** Dữ liệu và cấu trúc tài liệu (JSON/BSON) được tạo và quản lý trực tiếp thông qua các công cụ NoSQL chuẩn (`mongosh`, MongoDB Compass) thay vì phụ thuộc vào PHP ORM.
2. **Quản lý Schema Validation & Indexes bằng JavaScript:** MongoDB hỗ trợ JSON Schema Validation (`$jsonSchema`), Unique Index, Compound Index, và Text Index. Việc định nghĩa bằng các script `.js` chạy trong `mongosh` giúp giảng viên và người đánh giá kiểm tra trực tiếp cấu trúc NoSQL.
3. **Dễ dàng xem và làm việc trên MongoDB Compass:** Khi khởi tạo dữ liệu qua bộ script `mongodb/`, toàn bộ Collections, Document structures, Embedded arrays (`ThanhVien`, `DangKyDeTai`, `BaoCaoTienDo`, `SanPham`, `ChamDiem`) hiển thị trực quan và chuẩn xác 100% trên giao diện GUI của **MongoDB Compass**.

---

## 🛠️ 2. Hướng Dẫn Chi Tiết Cài Đặt & Kết Nối MongoDB Compass

### Bước 2.1. Cài đặt & Khởi động MongoDB Service
- Đảm bảo máy tính đã cài đặt **MongoDB Community Server** (Service `MongoDB` đang ở trạng thái `Running`).
- Cổng mặc định của MongoDB: `27017`.

### Bước 2.2. Kết nối bằng MongoDB Compass
1. Mở phần mềm **MongoDB Compass**.
2. Tại màn hình New Connection, nhập chuỗi URI:
   ```text
   mongodb://127.0.0.1:27017
   ```
3. Nhấn **Connect**.
4. Bạn sẽ thấy danh sách các Databases. Khi thực hiện nạp dữ liệu ở Bước 3, Database tên là **`quanly_doan`** sẽ xuất hiện tại đây.

---

## 🚀 3. Các Lệnh Tạo CSDL, Đánh Index, Validation & Nạp 50+ Dữ Liệu Mẫu

Dự án cung cấp sẵn bộ script JavaScript trong thư mục `mongodb/` phục vụ khởi tạo CSDL NoSQL từ đầu:

### 📂 Cấu trúc thư mục `mongodb/`:
```
mongodb/
├── 01_create_database.js       # Khởi tạo & chọn DB quanly_doan
├── 02_create_collections.js    # Tạo 14 Collections
├── 03_create_indexes.js        # Đánh Unique, Compound & Full-text Indexes
├── 04_create_validation.js     # Thiết lập JSON Schema Validation ($jsonSchema)
├── 05_import_data.js           # Nạp dữ liệu mẫu theo thứ tự chuẩn NoSQL
├── 06_check_data.js            # Kiểm tra số lượng & tính toàn vẹn bản ghi
├── run_all.js                  # Master script chạy tự động từ 01 -> 06
│
├── data/                       # Bộ 15 file dữ liệu mẫu (50+ bản ghi / file)
│   ├── 01_users.js             # 101 Tài khoản (Admin, 50 GV, 50 SV)
│   ├── 02_departments.js       # 50 Bộ môn
│   ├── 03_majors.js            # 50 Ngành
│   ├── 04_classes.js           # 50 Lớp
│   ├── 05_semesters.js         # 50 Học kỳ
│   ├── 06_lecturers.js         # 50 Giảng viên
│   ├── 07_students.js          # 50 Sinh viên
│   ├── 08_projects.js           # 50 Đề tài đồ án
│   ├── 09_groups.js            # 50 Nhóm đồ án (với embedded document đầy đủ)
│   ├── 10_registrations.js     # Đăng ký đề tài
│   ├── 11_progress_reports.js  # Báo cáo tiến độ
│   ├── 12_submissions.js       # Nộp sản phẩm
│   ├── 13_councils.js          # Hội đồng chấm đồ án
│   ├── 14_evaluations.js       # Đánh giá & chấm điểm
│   └── 15_notifications.js     # 50 Thông báo
│
└── queries/                    # Bộ 11 file truy vấn NoSQL nghiệp vụ (báo cáo đồ án)
```

### ⚡ Các Lệnh Thực Hiện (Chạy qua Terminal hoặc File Batch):

#### Cách 1: Chạy Master Script qua Terminal (`mongosh`)
```bash
mongosh mongodb://127.0.0.1:27017/quanly_doan mongodb/run_all.js
```

#### Cách 2: Chạy qua File Batch (Dành cho Windows)
Trong thư mục gốc dự án, double-click hoặc gõ lệnh:
```cmd
.\run_mongodb.bat
```

### 📊 Kết quả Thống kê Dữ liệu trong MongoDB Compass (50+ bản ghi/collection):
- `tai_khoan`: **101** bản ghi (Admin + 50 GV + 50 SV)
- `bo_mon`: **50** bản ghi
- `nganh`: **50** bản ghi
- `lop`: **50** bản ghi
- `hoc_ky`: **50** bản ghi
- `giang_vien`: **50** bản ghi
- `sinh_vien`: **50** bản ghi
- `de_tai`: **50** bản ghi
- `nhom_do_an`: **50** bản ghi (Tích hợp Embedded Documents: `ThanhVien`, `DangKyDeTai`, `HuongDan`, `BaoCaoTienDo`, `SanPham`, `ChamDiem`)
- `thong_bao`: **50** bản ghi
- `hoi_dong`: **50** bản ghi

---

## 🔗 4. Cách Kết Nối MongoDB Compass / MongoDB Server Với Source Code PHP (Laravel)

Sau khi dữ liệu đã được tạo sẵn trong MongoDB Server, source code PHP/Laravel sẽ kết nối tới CSDL thông qua các bước cấu hình sau:

### Bước 4.1. Cài đặt PHP Extension `mongodb`
PHP chạy dưới máy cục bộ cần extension `php_mongodb.dll` (dành cho PHP 8.3 NTS x64):
- Khai báo trong `php.ini`:
  ```ini
  extension=mongodb
  ```
- Thư viện kết nối trong Laravel (`composer.json`): `"mongodb/laravel-mongodb": "^5.11"`

### Bước 4.2. Cấu hình file `.env`
File `.env` khai báo kết nối trùng khớp với thông tin kết nối trên MongoDB Compass:
```env
DB_CONNECTION=mongodb
DB_HOST=127.0.0.1
DB_PORT=27017
DB_DATABASE=quanly_doan
DB_USERNAME=
DB_PASSWORD=
```

### Bước 4.3. Cấu hình Connection trong `config/database.php`
Trong `config/database.php`, connection `mongodb` được định nghĩa như sau:
```php
'mongodb' => [
    'driver'   => 'mongodb',
    'host'     => env('DB_HOST', '127.0.0.1'),
    'port'     => env('DB_PORT', 27017),
    'database' => env('DB_DATABASE', 'quanly_doan'),
    'username' => env('DB_USERNAME', ''),
    'password' => env('DB_PASSWORD', ''),
    'options'  => [
        'database' => env('DB_AUTHENTICATION_DATABASE', 'admin'),
    ],
],
```

### Bước 4.4. Đánh ánh quan hệ trong Model PHP
Các Model trong thư mục `app/Models/` (ví dụ `NhomDoAn.php`, `SinhVien.php`, `GiangVien.php`) kế thừa lớp Model của MongoDB Laravel Driver:
```php
use MongoDB\Laravel\Eloquent\Model;

class NhomDoAn extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'nhom_do_an';
    protected $primaryKey = '_id';
    
    // Cấu hình các trường nhúng (Embedded Arrays)
    protected $casts = [
        'ThanhVien' => 'array',
        'DangKyDeTai' => 'array',
        'BaoCaoTienDo' => 'array',
        'SanPham' => 'array',
        'ChamDiem' => 'array',
    ];
}
```
Nhờ cơ chế này, toàn bộ hàm điều khiển (Controllers) chỉ cần truy vấn dữ liệu từ MongoDB mà không hề phụ thuộc vào MySQL hay PHP Seeder.

---

## 🖥️ 5. Khởi Chạy Ứng Dụng Laravel & Tài Khoản Mẫu

### 1. Biên dịch tài nguyên giao diện:
```bash
npm run build
```

### 2. Chạy máy chủ Laravel:
```bash
php artisan serve
```
Truy cập ứng dụng tại địa chỉ: **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

### 🔑 Thông tin Đăng nhập Mẫu (Mật khẩu mặc định: `123456`):
- **Quản Trị Viên (Admin)**: `admin` / `123456`
- **Giảng Viên**: `gv01`, `gv02`, ..., `gv50` / `123456`
- **Sinh Viên (Trưởng nhóm)**: `sv01`, `sv02`, ..., `sv50` / `123456`

---

## 🔍 6. Bộ Truy Vấn NoSQL Nghiệp Vụ (`mongodb/queries/`)

Dự án chuẩn bị sẵn 11 file truy vấn NoSQL bằng `mongosh` để phục vụ demo và thuyết minh báo cáo đồ án:

| Tên File | Nghiệp Vụ Truy Vấn NoSQL | Lệnh Chạy Mẫu qua mongosh |
|:---|:---|:---|
| `01_users_queries.js` | Tra cứu tài khoản, phân quyền vai trò | `mongosh mongodb://127.0.0.1:27017/quanly_doan mongodb/queries/01_users_queries.js` |
| `02_students_queries.js` | Tra cứu sinh viên kết hợp `$lookup` sang Lớp học | `mongosh mongodb://127.0.0.1:27017/quanly_doan mongodb/queries/02_students_queries.js` |
| `03_lecturers_queries.js` | Lọc giảng viên theo học vị, thống kê theo bộ môn | `mongosh mongodb://127.0.0.1:27017/quanly_doan mongodb/queries/03_lecturers_queries.js` |
| `04_projects_queries.js` | Tìm kiếm full-text `$text` đề tài đồ án | `mongosh mongodb://127.0.0.1:27017/quanly_doan mongodb/queries/04_projects_queries.js` |
| `05_groups_queries.js` | Truy vấn mảng nhúng `ThanhVien`, `DangKyDeTai` | `mongosh mongodb://127.0.0.1:27017/quanly_doan mongodb/queries/05_groups_queries.js` |
| `06_registrations_queries.js` | Tra cứu đăng ký đề tài | `mongosh mongodb://127.0.0.1:27017/quanly_doan mongodb/queries/06_registrations_queries.js` |
| `07_progress_queries.js` | Thao tác trên mảng `BaoCaoTienDo` với `$unwind` | `mongosh mongodb://127.0.0.1:27017/quanly_doan mongodb/queries/07_progress_queries.js` |
| `08_submissions_queries.js` | Truy vấn trạng thái nộp sản phẩm | `mongosh mongodb://127.0.0.1:27017/quanly_doan mongodb/queries/08_submissions_queries.js` |
| `09_evaluations_queries.js` | Lọc điểm số đồ án cao hơn threshold | `mongosh mongodb://127.0.0.1:27017/quanly_doan mongodb/queries/09_evaluations_queries.js` |
| `10_statistics_queries.js` | Aggregation Pipeline: `$group`, `$avg`, `$max`, `$min` | `mongosh mongodb://127.0.0.1:27017/quanly_doan mongodb/queries/10_statistics_queries.js` |
| `11_advanced_queries.js` | Multi-facet Search (`$facet`) | `mongosh mongodb://127.0.0.1:27017/quanly_doan mongodb/queries/11_advanced_queries.js` |

---

## 📄 Bản Quyền (License)
Đồ án thuộc bản quyền sinh viên Trường Đại Học Công Thương TP. Hồ Chí Minh (HUIT). Giấy phép mã nguồn [MIT License](LICENSE).
