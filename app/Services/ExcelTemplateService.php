<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelTemplateService
{
    /**
     * Get templates dictionary mapping keys to file details.
     */
    public function getTemplatesConfig(): array
    {
        return [
            'bomon' => [
                'filename' => 'Template_BoMon.xlsx',
                'title'    => 'MẪU NHẬP LIỆU BỘ MÔN',
                'headers'  => ['TenBoMon', 'MoTa'],
                'examples' => [
                    ['Công Nghệ Phần Mềm', 'Bộ môn phụ trách đào tạo công nghệ phần mềm'],
                    ['Hệ Thống Thông Tin', 'Bộ môn quản lý dữ liệu và hệ thống thông tin'],
                ],
                'note'     => 'Lưu ý: TenBoMon là bắt buộc và không được trùng lặp.'
            ],
            'sinhvien_lophocphan' => [
                'filename' => 'Template_SinhVien_LopHocPhan.xlsx',
                'title'    => 'MẪU NHẬP DANH SÁCH SINH VIÊN VÀO LỚP HỌC PHẦN',
                'headers'  => ['MSSV', 'HoTen', 'TenLop', 'Email', 'SoDienThoai', 'TenLopHP'],
                'examples' => [
                    ['2001210001', 'Nguyễn Văn Bảo', '21DTH01', 'bao.nv@st.edu.vn', '0901234567', 'LHP06'],
                    ['2001210002', 'Trần Thị Bình', '21DTH02', 'binh.tt@st.edu.vn', '0987654321', 'LHP06'],
                ],
                'note'     => 'Lưu ý: Cột MSSV là bắt buộc. Cột TenLop (Lớp hành chính), Email, SoDienThoai dùng để tự động tạo sinh viên nếu chưa có. Cột TenLopHP có thể bỏ qua khi import trực tiếp tại trang Chi Tiết Lớp HP.'
            ],
            'nganh' => [
                'filename' => 'Template_Nganh.xlsx',
                'title'    => 'MẪU NHẬP LIỆU NGÀNH HỌC',
                'headers'  => ['TenNganh', 'MoTa', 'MaBoMon'],
                'examples' => [
                    ['Công Nghệ Thông Tin', 'Đào tạo kỹ sư CNTT toàn diện', 'Công Nghệ Phần Mềm'],
                    ['Kỹ Thuật Phần Mềm', 'Chuyên ngành phát triển phần mềm', 1],
                ],
                'note'     => 'Lưu ý: TenNganh bắt buộc. MaBoMon có thể điền ID bộ môn hoặc Tên bộ môn.'
            ],
            'lop' => [
                'filename' => 'Template_Lop.xlsx',
                'title'    => 'MẪU NHẬP LIỆU LỚP HỌC HÀNH CHÍNH',
                'headers'  => ['TenLop', 'MaNganh', 'KhoaHoc'],
                'examples' => [
                    ['21DTH01', 'Công Nghệ Thông Tin', '2021-2025'],
                    ['21DTH02', 1, '2021-2025'],
                ],
                'note'     => 'Lưu ý: TenLop bắt buộc không trùng. MaNganh có thể nhập ID Ngành hoặc Tên Ngành.'
            ],
            'monhoc' => [
                'filename' => 'Template_MonHoc.xlsx',
                'title'    => 'MẪU NHẬP LIỆU MÔN HỌC',
                'headers'  => ['TenMon', 'SoTinChi', 'MaBoMon'],
                'examples' => [
                    ['Đồ Án Chuyên Ngành Web', 3, 'Công Nghệ Phần Mềm'],
                    ['Lập Trình Di Động', 3, 1],
                ],
                'note'     => 'Lưu ý: TenMon bắt buộc. SoTinChi là số nguyên (>0). MaBoMon có thể nhập ID hoặc Tên Bộ Môn.'
            ],
            'hocky' => [
                'filename' => 'Template_HocKy.xlsx',
                'title'    => 'MẪU NHẬP LIỆU HỌC KỲ',
                'headers'  => ['TenHocKy', 'NamHoc', 'NgayBatDau', 'NgayKetThuc'],
                'examples' => [
                    ['Học kỳ 1', '2025-2026', '2025-09-01', '2026-01-15'],
                    ['Học kỳ 2', '2025-2026', '2026-01-20', '2026-06-01'],
                ],
                'note'     => 'Lưu ý: TenHocKy và NamHoc không trùng. Định dạng ngày YYYY-MM-DD (ví dụ 2025-09-01).'
            ],
            'giangvien' => [
                'filename' => 'Template_GiangVien.xlsx',
                'title'    => 'MẪU NHẬP LIỆU GIẢNG VIÊN',
                'headers'  => ['MaGV', 'HoTen', 'Email', 'SoDienThoai', 'HocVi', 'MaBoMon'],
                'examples' => [
                    ['GV001', 'Nguyễn Văn A', 'gv001@fe.edu.vn', '0901234567', 'Thạc sĩ', 'Công Nghệ Phần Mềm'],
                    ['GV002', 'Trần Thị B', 'gv002@fe.edu.vn', '0912345678', 'Tiến sĩ', 'Hệ Thống Thông Tin'],
                ],
                'note'     => 'Lưu ý: MaGV (Tên đăng nhập) bắt buộc không trùng. Email & SDT chuẩn 10 số. MaBoMon có thể nhập ID hoặc Tên Bộ Môn.'
            ],
            'sinhvien' => [
                'filename' => 'Template_SinhVien.xlsx',
                'title'    => 'MẪU NHẬP LIỆU SINH VIÊN',
                'headers'  => ['MSSV', 'HoTen', 'NgaySinh', 'Email', 'SoDienThoai', 'MaLop', 'MaNganh', 'KhoaHoc'],
                'examples' => [
                    ['SV001', 'Nguyễn Văn An', '2002-03-15', 'sv001@student.huit.edu.vn', '0381000001', '12DHTH01', 'CNTT', '12'],
                    ['SV002', 'Trần Thị Bình', '2002-07-22', 'sv002@student.huit.edu.vn', '0381000002', '12DHTH01', 'CNTT', '12'],
                ],
                'note'     => 'Lưu ý: Cột MSSV là bắt buộc và không trùng lặp. Ngày sinh định dạng YYYY-MM-DD. MaLop và MaNganh có thể điền Mã hoặc Tên.'
            ],
            'lophocphan' => [
                'filename' => 'Template_LopHocPhan.xlsx',
                'title'    => 'MẪU NHẬP LIỆU LỚP HỌC PHẦN (LỚP TÍN CHỈ)',
                'headers'  => ['TenLopHP', 'MaMon', 'MaHocKy', 'MaGV', 'SiSoToiDa', 'MoTa'],
                'examples' => [
                    ['21DTH01_WEB_N01', 'Đồ Án Chuyên Ngành Web', 'Học kỳ 1', 'gv001', 40, 'Lớp học phần tín chỉ đồ án web nhom 01'],
                    ['21DTH02_MOBILE_N01', 'Lập Trình Di Động', 'Học kỳ 1', 'gv002', 45, 'Lớp học phần di động'],
                ],
                'note'     => 'Lưu ý: TenLopHP bắt buộc không trùng. MaMon (ID hoặc Tên môn), MaHocKy (ID hoặc Tên học kỳ), MaGV (ID hoặc Mã GV/Tên GV).'
            ],
            'detai' => [
                'filename' => 'Template_DeTai.xlsx',
                'title'    => 'MẪU NHẬP LIỆU ĐỀ TÀI ĐỒ ÁN',
                'headers'  => ['TenDeTai', 'MaLopHP', 'MaMon', 'MaHocKy', 'MoTa', 'YeuCau', 'HanDangKy', 'HanBaoCao', 'HanNopSanPham'],
                'examples' => [
                    [
                        'Xây Dựng Hệ Thống Quản Lý Đồ Án Tín Chỉ',
                        '21DTH01_WEB_N01',
                        'Đồ Án Chuyên Ngành Web',
                        'Học kỳ 1',
                        'Đề tài nghiên cứu và lập trình ứng dụng Web Laravel',
                        'Sử dụng MySQL, Bootstrap 5 và Vite',
                        '2026-08-10',
                        '2026-08-25',
                        '2026-09-05'
                    ],
                ],
                'note'     => 'Lưu ý: TenDeTai bắt buộc. MaLopHP có thể nhập ID Lớp HP hoặc Tên Lớp HP. MaMon, MaHocKy tự động điền theo Lớp HP hoặc nhập tên.'
            ],
            'nhom' => [
                'filename' => 'Template_NhomDoAn.xlsx',
                'title'    => 'MẪU NHẬP LIỆU NHÓM ĐỒ ÁN',
                'headers'  => ['TenNhom', 'MaLopHP', 'MaMon', 'MaHocKy', 'MaSV_TruongNhom'],
                'examples' => [
                    ['Nhóm Đồ Án Web 01', '21DTH01_WEB_N01', 'Đồ Án Chuyên Ngành Web', 'Học kỳ 1', 'sv001'],
                    ['Nhóm Mobile 02', 1, 1, 1, 'sv002'],
                ],
                'note'     => 'Lưu ý: TenNhom không trùng trong môn/lớp. MaSV_TruongNhom có thể nhập ID hoặc MSSV.'
            ],
            'phancong_hc' => [
                'filename' => 'Template_PhanCong_LopHanhChinh.xlsx',
                'title'    => 'MẪU NHẬP LIỆU PHÂN CÔNG GIẢNG VIÊN CHỦ NHIỆM LỚP HÀNH CHÍNH',
                'headers'  => ['MaGV', 'HoTenGV', 'MaLop', 'TenLop', 'MaHocKy', 'NgayPhanCong'],
                'examples' => [
                    ['GV01', 'TS. Nguyễn Văn Minh', 'LOP01', '12DHTH01', 'HK241', date('Y-m-d')],
                    ['GV02', 'ThS. Lê Thị Lan', 'LOP02', '12DHTH02', 'HK241', date('Y-m-d')],
                ],
                'note'     => 'Lưu ý: MaGV (Mã GV hoặc Họ Tên). MaLop (Mã Lớp hoặc Tên Lớp). MaHocKy (Mã hoặc Tên Học Kỳ). NgayPhanCong (Định dạng YYYY-MM-DD).'
            ],
            'phancong_hp' => [
                'filename' => 'Template_PhanCong_LopHocPhan.xlsx',
                'title'    => 'MẪU NHẬP LIỆU PHÂN CÔNG GIẢNG VIÊN LỚP HỌC PHẦN',
                'headers'  => ['MaLopHP', 'TenLopHP', 'MaGV', 'HoTenGV', 'Thu', 'CaHoc'],
                'examples' => [
                    ['LHP01', '12DHTH01 - Đồ án tốt nghiệp (HK241)', 'GV01', 'TS. Nguyễn Văn Minh', 'Thứ 2', 'Ca 1 (07h00 - 09h30)'],
                    ['LHP02', '12DHTH02 - Đồ án môn học 1 (HK241)', 'GV02', 'ThS. Lê Thị Lan', 'Thứ 3', 'Ca 2 (09h30 - 12h00)'],
                ],
                'note'     => 'Lưu ý: MaLopHP (Mã Lớp HP hoặc Tên Lớp HP). MaGV (Mã GV hoặc Họ Tên). Thu (VD: Thứ 2). CaHoc (VD: Ca 1 (07h00 - 09h30)).'
            ],
            'phancong' => [
                'filename' => 'Template_PhanCong_LopHanhChinh.xlsx',
                'title'    => 'MẪU NHẬP LIỆU PHÂN CÔNG GIẢNG VIÊN CHỦ NHIỆM LỚP HÀNH CHÍNH',
                'headers'  => ['MaGV', 'HoTenGV', 'MaLop', 'TenLop', 'MaHocKy', 'NgayPhanCong'],
                'examples' => [
                    ['GV01', 'TS. Nguyễn Văn Minh', 'LOP01', '12DHTH01', 'HK241', date('Y-m-d')],
                    ['GV02', 'ThS. Lê Thị Lan', 'LOP02', '12DHTH02', 'HK241', date('Y-m-d')],
                ],
                'note'     => 'Lưu ý: MaGV (Mã GV hoặc Họ Tên). MaLop (Mã Lớp hoặc Tên Lớp). MaHocKy (Mã hoặc Tên Học Kỳ). NgayPhanCong (Định dạng YYYY-MM-DD).'
            ]
        ];
    }

    /**
     * Download styled .xlsx template file using StreamedResponse.
     */
    public function downloadTemplate(string $key): StreamedResponse
    {
        $configs = $this->getTemplatesConfig();
        
        // Normalize alias
        $aliasMap = [
            'detais'              => 'detai',
            'bomons'              => 'bomon',
            'nganhs'              => 'nganh',
            'lops'                => 'lop',
            'monhocs'             => 'monhoc',
            'giangviens'          => 'giangvien',
            'sinhviens'           => 'sinhvien',
            'hockys'              => 'hocky',
            'hockies'             => 'hocky',
            'phancongs'           => 'phancong_hc',
            'phancong-hc'         => 'phancong_hc',
            'phancong_hanhchinh'  => 'phancong_hc',
            'phancong-hanhchinh'  => 'phancong_hc',
            'phancong-hp'         => 'phancong_hp',
            'phancong_hocphan'    => 'phancong_hp',
            'phancong-hocphan'    => 'phancong_hp',
            'nhoms'               => 'nhom',
            'lophocphan'          => 'lophocphan',
            'lophocphans'         => 'lophocphan',
            'lop-hoc-phan'        => 'lophocphan',
            'lop-hoc-phans'       => 'lophocphan',
            'sinhvien_lophocphan' => 'sinhvien_lophocphan',
            'sinhvien-lophocphan' => 'sinhvien_lophocphan',
        ];

        $key = $aliasMap[strtolower($key)] ?? strtolower($key);

        if (!isset($configs[$key])) {
            abort(404, "Không tìm thấy cấu hình file mẫu cho loại: {$key}");
        }

        $cfg = $configs[$key];
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template');

        // Row 1: Instruction Note (Merged across headers to avoid wide column A)
        $headers = $cfg['headers'];
        $colCount = count($headers);
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);
        if ($colCount > 1) {
            $sheet->mergeCells("A1:{$lastColLetter}1");
        }

        $sheet->setCellValue('A1', '💡 ' . $cfg['note']);
        $sheet->getStyle('A1')->getFont()->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF555555'));

        // Row 3: Header Row
        $startRow = 3;
        $headers = $cfg['headers'];
        $colCount = count($headers);

        foreach ($headers as $index => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
            $cell = $colLetter . $startRow;
            $sheet->setCellValue($cell, $header);
            
            // Header Styling
            $sheet->getStyle($cell)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E40AF'], // Deep Blue
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);
        }
        $sheet->getRowDimension($startRow)->setRowHeight(26);

        // Row 4+: Data Examples
        $dataRowIndex = $startRow + 1;
        foreach ($cfg['examples'] as $exampleRow) {
            foreach ($exampleRow as $index => $val) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
                $cell = $colLetter . $dataRowIndex;
                $sheet->setCellValue($cell, $val);

                // Data Styling
                $sheet->getStyle($cell)->applyFromArray([
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'E5E7EB'],
                        ],
                    ],
                ]);
            }
            $sheet->getRowDimension($dataRowIndex)->setRowHeight(22);
            $dataRowIndex++;
        }

        // Auto-fit Column Widths
        for ($i = 1; $i <= $colCount; $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $filename = $cfg['filename'];

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
