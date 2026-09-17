<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NhomDoAn;
use App\Models\GiangVien;
use App\Models\SinhVien;
use App\Models\DeTai;
use App\Models\HocKy;
use App\Models\MonHoc;
use App\Models\LopHocPhan;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ThongKeBaoCaoController extends Controller
{
    private function getDiemTong($nhom)
    {
        $cd = $nhom->ChamDiem ?? $nhom->attributes['ChamDiem'] ?? null;
        if (is_object($cd) && isset($cd->DiemTong)) return (float)$cd->DiemTong;
        if (is_array($cd) && isset($cd['DiemTong'])) return (float)$cd['DiemTong'];
        return null;
    }

    private function getGiangVienHuongDanId($nhom)
    {
        $hd = $nhom->HuongDan ?? $nhom->attributes['HuongDan'] ?? null;
        if (is_object($hd)) return (string)($hd->MaGV ?? '');
        if (is_array($hd)) return (string)($hd['MaGV'] ?? '');
        return null;
    }

    /**
     * Báo cáo thống kê tổng hợp 8 góc nhìn
     */
    public function index(Request $request)
    {
        $allNhoms = NhomDoAn::with(['monHoc', 'hocKy', 'lopHocPhan'])->get();
        $allSvs   = SinhVien::all();
        $allGvs   = GiangVien::all();
        $allDts   = DeTai::all();
        $hocKys   = HocKy::orderBy('_id', 'desc')->get();
        $monHocs  = MonHoc::all();
        $lhpList  = LopHocPhan::all();

        // 1. Thống kê Theo Học Kỳ
        $statsByHocKy = [];
        foreach ($hocKys as $hk) {
            $nhomsInHk = $allNhoms->filter(fn($n) => (string)$n->MaHocKy === (string)$hk->_id || (string)$n->MaHK === (string)$hk->_id || (string)$n->MaHocKy === (string)$hk->MaHocKy);
            $graded = $nhomsInHk->filter(fn($n) => $this->getDiemTong($n) !== null);
            $avgDiem = $graded->count() > 0 ? round($graded->avg(fn($n) => $this->getDiemTong($n)), 2) : 0;
            $statsByHocKy[] = [
                'tenHocKy' => $hk->TenHocKy,
                'namHoc' => $hk->NamHoc,
                'tongSoNhom' => $nhomsInHk->count(),
                'daCoDiem' => $graded->count(),
                'diemTrungBinh' => $avgDiem
            ];
        }

        // 2. Thống kê Theo Điểm Số / Xếp Loại
        $distGrade = [
            'XuatSac' => 0,   // >= 9.0
            'Gioi' => 0,      // 8.0 - 8.9
            'Kha' => 0,       // 6.5 - 7.9
            'TrungBinh' => 0, // 5.0 - 6.4
            'KhongDat' => 0,  // < 5.0
            'ChuaNop' => 0    // chưa nộp/chưa chấm
        ];

        foreach ($allNhoms as $nhom) {
            $dt = $this->getDiemTong($nhom);
            if ($dt === null) {
                $distGrade['ChuaNop']++;
            } else {
                $score = (float)$dt;
                if ($score >= 9.0) $distGrade['XuatSac']++;
                elseif ($score >= 8.0) $distGrade['Gioi']++;
                elseif ($score >= 6.5) $distGrade['Kha']++;
                elseif ($score >= 5.0) $distGrade['TrungBinh']++;
                else $distGrade['KhongDat']++;
            }
        }

        // 3. Thống kê Theo Giảng Viên Hướng Dẫn
        $statsByGiangVien = [];
        foreach ($allGvs as $gv) {
            $gvCode = (string)$gv->MaGV;
            $gvMongoId = (string)$gv->_id;

            $myNhoms = $allNhoms->filter(function($n) use ($gvCode, $gvMongoId) {
                $hd = $this->getGiangVienHuongDanId($n);
                return $hd && ($hd === $gvMongoId || $hd === $gvCode);
            });

            $gradedCount = $myNhoms->filter(fn($n) => $this->getDiemTong($n) !== null)->count();
            $statsByGiangVien[] = [
                'hoTen' => $gv->HoTen,
                'maGV' => $gv->MaGV,
                'tongNhomHD' => $myNhoms->count(),
                'daChamDiem' => $gradedCount
            ];
        }

        // 4. Thống kê Tiến độ (Đúng tiến độ vs Chậm tiến độ)
        $progressOverdueCount = 0;
        $progressOnTimeCount = 0;
        foreach ($allNhoms as $nhom) {
            $dk = $nhom->getDangKyDeTai();
            $deTai = ($dk && !empty($dk['MaDeTai'])) ? DeTai::find($dk['MaDeTai']) : null;
            $baocaos = $nhom->getBaoCaoList();
            if ($deTai && $deTai->HanBaoCao && date('Y-m-d') > $deTai->HanBaoCao && $baocaos->isEmpty()) {
                $progressOverdueCount++;
            } else {
                $progressOnTimeCount++;
            }
        }

        return view('admin.thongke.baocao', compact(
            'statsByHocKy', 'distGrade', 'statsByGiangVien',
            'progressOverdueCount', 'progressOnTimeCount',
            'allNhoms', 'allSvs', 'allGvs', 'allDts', 'hocKys', 'monHocs'
        ));
    }

    /**
     * Xuất Báo Cáo Excel Kết Quả Đồ Án Tổng Hợp
     */
    public function exportExcel(Request $request)
    {
        $nhoms = NhomDoAn::with(['monHoc', 'hocKy', 'lopHocPhan'])->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('BaoCaoKetQuaDoAn');

        // Header Title
        $sheet->mergeCells("A1:I1");
        $sheet->setCellValue('A1', 'BÁO CÁO TỔNG HỢP KẾT QUẢ ĐỒ ÁN / KHÓA LUẬN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E40AF'));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header Table
        $headers = ['STT', 'Mã Nhóm', 'Tên Nhóm', 'Đề Tài Đồ Án', 'Môn Học / Học Phần', 'Học Kỳ', 'Điểm Báo Cáo', 'Điểm Bảo Vệ', 'Điểm Tổng Kết', 'Kết Quả'];
        foreach ($headers as $i => $h) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . '3', $h);
            $sheet->getStyle($col . '3')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
        }

        $rowIdx = 4;
        foreach ($nhoms as $idx => $nhom) {
            $dk = $nhom->getDangKyDeTai();
            $deTai = ($dk && !empty($dk['MaDeTai'])) ? DeTai::find($dk['MaDeTai']) : null;
            $cd = $nhom->ChamDiem ?? $nhom->attributes['ChamDiem'] ?? null;

            $diemBC = is_object($cd) ? ($cd->DiemBaoCao ?? '—') : ($cd['DiemBaoCao'] ?? '—');
            $diemBV = is_object($cd) ? ($cd->DiemBaoVe ?? '—') : ($cd['DiemBaoVe'] ?? '—');
            $diemTongVal = $this->getDiemTong($nhom);
            $diemTong = $diemTongVal !== null ? number_format((float)$diemTongVal, 1) : '—';
            $ketQua = $diemTongVal !== null ? ($diemTongVal >= 5.0 ? 'ĐẠT' : 'KHÔNG ĐẠT') : 'Chưa có điểm';

            $sheet->setCellValue("A{$rowIdx}", $idx + 1);
            $sheet->setCellValue("B{$rowIdx}", $nhom->MaNhom ?? (string)$nhom->_id);
            $sheet->setCellValue("C{$rowIdx}", $nhom->TenNhom);
            $sheet->setCellValue("D{$rowIdx}", $deTai->TenDeTai ?? 'Chưa đăng ký');
            $sheet->setCellValue("E{$rowIdx}", $nhom->monHoc->TenMon ?? 'N/A');
            $sheet->setCellValue("F{$rowIdx}", $nhom->hocKy->TenHocKy ?? 'N/A');
            $sheet->setCellValue("G{$rowIdx}", $diemBC);
            $sheet->setCellValue("H{$rowIdx}", $diemBV);
            $sheet->setCellValue("I{$rowIdx}", $diemTong);
            $sheet->setCellValue("J{$rowIdx}", $ketQua);

            $rowIdx++;
        }

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'BaoCao_KetQua_DoAn_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
