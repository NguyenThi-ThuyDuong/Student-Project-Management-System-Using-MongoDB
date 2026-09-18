<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class NhomDoAn extends Model
{
    protected $table = 'nhom_do_an';
    protected $collection = 'nhom_do_an';

    protected $fillable = [
        'TenNhom', 'MaHocKy', 'MaMon', 'MaLop', 'MaLopHP',
        'TruongNhom', 'TrangThai',
        'ThanhVien', 'DangKyDeTai', 'HuongDan',
        'LoiMoi', 'BaoCaoTienDo', 'SanPham', 'ChamDiem',
    ];

    // MongoDB natively handles BSON arrays and embedded documents
    protected $casts = [];

    public function getMaNhomAttribute($value)
    {
        if (!empty($value)) {
            return (string)$value;
        }
        return isset($this->attributes['_id']) ? (string)$this->attributes['_id'] : '';
    }

    // ======== Relationships (references) ========

    public function monHoc()
    {
        return $this->belongsTo(MonHoc::class, 'MaMon', 'MaMon');
    }

    public function getMonHocAttribute()
    {
        if (array_key_exists('monHoc', $this->relations) && $this->relations['monHoc']) {
            return $this->relations['monHoc'];
        }
        $val = $this->attributes['MaMon'] ?? null;
        if (!$val) return null;
        return MonHoc::where('MaMon', $val)->orWhere('_id', $val)->first();
    }

    public function hocKy()
    {
        return $this->belongsTo(HocKy::class, 'MaHocKy', 'MaHocKy');
    }

    public function getHocKyAttribute()
    {
        if (array_key_exists('hocKy', $this->relations) && $this->relations['hocKy']) {
            return $this->relations['hocKy'];
        }
        $val = $this->attributes['MaHocKy'] ?? null;
        if (!$val) return null;
        return HocKy::where('MaHocKy', $val)->orWhere('MaHK', $val)->orWhere('_id', $val)->first();
    }

    public function lopHocPhan()
    {
        return $this->belongsTo(LopHocPhan::class, 'MaLopHP', '_id');
    }

    public function getLopHocPhanAttribute()
    {
        if (array_key_exists('lopHocPhan', $this->relations) && $this->relations['lopHocPhan']) {
            return $this->relations['lopHocPhan'];
        }
        $val = $this->attributes['MaLopHP'] ?? null;
        if (!$val) return null;
        return LopHocPhan::where('_id', $val)->orWhere('MaLopHP', $val)->first();
    }

    public function isTruongNhom($sinhVien)
    {
        if (!$sinhVien) return false;
        
        $keys = array_values(array_unique(array_filter([
            (string)($sinhVien->_id ?? ''),
            (string)($sinhVien->MaSV ?? ''),
            strtoupper((string)($sinhVien->MaSV ?? '')),
            strtolower((string)($sinhVien->MaSV ?? '')),
        ])));

        $truongNhom = (string) ($this->attributes['TruongNhom'] ?? '');
        if (!empty($truongNhom) && in_array($truongNhom, $keys, true)) {
            return true;
        }

        $thanhVienList = $this->attributes['ThanhVien'] ?? [];
        foreach ($thanhVienList as $tv) {
            $tvMaSV = (string) ($tv['MaSV'] ?? '');
            $vaiTro = $tv['VaiTro'] ?? '';
            if (in_array($vaiTro, ['Trưởng nhóm', 'truong_nhom'], true) && in_array($tvMaSV, $keys, true)) {
                return true;
            }
        }

        return false;
    }

    public function sinhVienTruongNhom()
    {
        return $this->belongsTo(SinhVien::class, 'TruongNhom', '_id');
    }

    // ======== ThanhVien (embedded array) ========

    public function getThanhVienList()
    {
        return collect($this->ThanhVien ?? []);
    }

    public function getThanhVienActive()
    {
        return $this->getThanhVienList()->filter(function ($tv) {
            return ($tv['TrangThai'] ?? '') === 'da_tham_gia';
        });
    }

    public function addThanhVien($maSV, $vaiTro = 'Thành viên', $trangThai = 'da_tham_gia')
    {
        $thanhVien = $this->ThanhVien ?? [];
        $thanhVien[] = [
            'MaSV' => (string) $maSV,
            'VaiTro' => $vaiTro,
            'TrangThai' => $trangThai,
            'created_at' => now()->toDateTimeString(),
        ];
        $this->ThanhVien = $thanhVien;
        $this->save();
    }

    public function hasThanhVien($maSV)
    {
        return $this->getThanhVienList()->contains(function ($tv) use ($maSV) {
            return (string) ($tv['MaSV'] ?? '') === (string) $maSV
                && in_array($tv['TrangThai'] ?? '', ['da_tham_gia', 'da_chap_nhan']);
        });
    }

    public function countThanhVien()
    {
        return $this->getThanhVienList()->count();
    }

    /**
     * Lấy số thành viên tối đa quy định bởi Giảng viên / Lớp Học Phần / Đề Tài
     */
    public function getSoThanhVienToiDa()
    {
        $deTai = $this->getDeTaiDangKy();
        if ($deTai && !empty($deTai->SoThanhVienToiDa)) {
            return (int) $deTai->SoThanhVienToiDa;
        }

        if ($this->lopHocPhan && !empty($this->lopHocPhan->SoThanhVienNhomToiDa)) {
            return (int) $this->lopHocPhan->SoThanhVienNhomToiDa;
        }

        return 5;
    }

    /**
     * Lấy đối tượng SinhVien cho tất cả thành viên
     */
    public function getSinhVienThanhVien()
    {
        $ids = $this->getThanhVienList()->pluck('MaSV')->filter()->values();
        return SinhVien::whereIn('_id', $ids)->orWhereIn('MaSV', $ids)->get();
    }

    public function getThanhVienNhomsAttribute()
    {
        return $this->getSinhVienThanhVien();
    }

    // ======== DangKyDeTai (embedded object) ========

    public function getDangKyDeTai()
    {
        $dk = $this->attributes['DangKyDeTai'] ?? null;
        if (is_object($dk)) return (array) $dk;
        return $dk;
    }

    public function getDangKyDeTaiAttribute()
    {
        $dk = $this->attributes['DangKyDeTai'] ?? null;
        if (!$dk) return null;
        $obj = (object) $dk;
        if (!empty($dk['MaDeTai'])) {
            $maDT = $dk['MaDeTai'];
            $obj->deTai = DeTai::where('_id', $maDT)->orWhere('MaDeTai', $maDT)->orWhere('MaDT', $maDT)->first();
        }
        return $obj;
    }

    public function getTenDeTaiDangKy()
    {
        $dt = $this->getDeTaiDangKy();
        if ($dt && !empty($dt->TenDeTai)) return $dt->TenDeTai;
        $dk = $this->getDangKyDeTai();
        if (is_array($dk) && !empty($dk['TenDeTai'])) return $dk['TenDeTai'];
        if (is_object($this->DangKyDeTai) && !empty($this->DangKyDeTai->TenDeTai)) return $this->DangKyDeTai->TenDeTai;
        return 'Chưa đăng ký';
    }

    public function getTrangThaiDangKy()
    {
        $dk = $this->getDangKyDeTai();
        if (is_array($dk) && isset($dk['TrangThai'])) return $dk['TrangThai'];
        if (is_object($this->DangKyDeTai) && isset($this->DangKyDeTai->TrangThai)) return $this->DangKyDeTai->TrangThai;
        return 'Chưa đăng ký';
    }

    public function setDangKyDeTai($maDeTai, $trangThai = 'Chờ duyệt')
    {
        $this->DangKyDeTai = [
            'MaDeTai' => (string) $maDeTai,
            'NgayDangKy' => now()->toDateString(),
            'TrangThai' => $trangThai,
            'NgayDuyet' => null,
            'LyDoTuChoi' => null,
        ];
        $this->save();
    }

    public function clearDangKyDeTai()
    {
        $this->DangKyDeTai = null;
        $this->save();
    }

    /**
     * Lấy đối tượng DeTai đã đăng ký
     */
    public function getDeTaiDangKy()
    {
        $dk = $this->getDangKyDeTai();
        $maDeTai = is_array($dk) ? ($dk['MaDeTai'] ?? null) : ($this->DangKyDeTai->MaDeTai ?? null);
        if (!$maDeTai) return null;
        return DeTai::where('_id', $maDeTai)->orWhere('MaDeTai', $maDeTai)->orWhere('MaDT', $maDeTai)->first();
    }

    // ======== HuongDan (embedded object) ========

    public function getHuongDan()
    {
        $hd = $this->attributes['HuongDan'] ?? null;
        if (is_object($hd)) return (array) $hd;
        return $hd;
    }

    public function setHuongDan($maGV, $maDeTai = null)
    {
        $this->HuongDan = [
            'MaGV' => (string) $maGV,
            'MaDeTai' => $maDeTai ? (string) $maDeTai : null,
            'NgayPhanCong' => now()->toDateString(),
            'TrangThai' => 'Đang hướng dẫn',
        ];
        $this->save();
    }

    /**
     * Lấy GiangVien hướng dẫn
     */
    public function getGiangVienHuongDan()
    {
        $hd = $this->getHuongDan();
        $maGV = is_array($hd) ? ($hd['MaGV'] ?? null) : ($this->HuongDan->MaGV ?? null);
        if (!$maGV) return null;
        return GiangVien::find($maGV);
    }

    // ======== LoiMoi (embedded array) ========

    public function getLoiMoiList()
    {
        $list = $this->attributes['LoiMoi'] ?? [];
        if (!is_array($list)) return collect([]);
        $updated = false;
        foreach ($list as $index => &$item) {
            if (is_array($item) && empty($item['_id']) && empty($item['id'])) {
                $item['_id'] = (string) new \MongoDB\BSON\ObjectId();
                $updated = true;
            }
        }
        if ($updated) {
            $this->attributes['LoiMoi'] = $list;
            $this->save();
        }
        return collect($list);
    }

    public function addLoiMoi($maSV_Moi, $maSV_DuocMoi)
    {
        $loiMoi = $this->LoiMoi ?? [];
        $newId = (string) new \MongoDB\BSON\ObjectId();
        $loiMoi[] = [
            '_id' => $newId,
            'MaSV_Moi' => (string) $maSV_Moi,
            'MaSV_DuocMoi' => (string) $maSV_DuocMoi,
            'TrangThai' => 'cho_xac_nhan',
            'NgayMoi' => now()->toDateTimeString(),
            'NgayPhanHoi' => null,
        ];
        $this->LoiMoi = $loiMoi;
        $this->save();
        return $newId;
    }

    public function updateLoiMoi($loiMoiId, $data)
    {
        $loiMoi = $this->LoiMoi ?? [];
        foreach ($loiMoi as &$lm) {
            $lmId = (string)($lm['_id'] ?? $lm['id'] ?? '');
            if ($lmId === (string)$loiMoiId) {
                $lm = array_merge($lm, $data);
                break;
            }
        }
        $this->LoiMoi = $loiMoi;
        $this->save();
    }

    public function findLoiMoi($loiMoiId)
    {
        return $this->getLoiMoiList()->first(function($lm) use ($loiMoiId) {
            $lmArr = is_array($lm) ? $lm : (array)$lm;
            $lmId = (string)($lmArr['_id'] ?? $lmArr['id'] ?? '');
            return $lmId === (string)$loiMoiId;
        });
    }

    public function hasPendingLoiMoi($maSV_DuocMoi)
    {
        return $this->getLoiMoiList()->contains(function ($lm) use ($maSV_DuocMoi) {
            return (string) ($lm['MaSV_DuocMoi'] ?? '') === (string) $maSV_DuocMoi
                && ($lm['TrangThai'] ?? '') === 'cho_xac_nhan';
        });
    }

    // ======== BaoCaoTienDo (embedded array with nested NhanXet) ========

    // ======== BaoCaoTienDo (embedded array with nested NhanXet) ========

    public function getBaoCaoList()
    {
        $list = $this->attributes['BaoCaoTienDo'] ?? [];
        if (is_string($list)) {
            $list = json_decode($list, true) ?? [];
        }
        if (!is_array($list)) return collect([]);

        return collect($list)->map(function ($item) {
            $arr = is_array($item) ? $item : (array) $item;
            $idStr = (string) ($arr['id'] ?? $arr['_id'] ?? $arr['MaBaoCao'] ?? '');
            if (empty($idStr)) {
                $idStr = (string) new \MongoDB\BSON\ObjectId();
            }
            $arr['_id'] = $idStr;
            $arr['id'] = $idStr;
            $arr['MaBaoCao'] = $idStr;

            $nxList = collect($arr['NhanXet'] ?? [])->map(fn($nx) => is_array($nx) ? (object)$nx : $nx);
            $arr['nhanXets'] = $nxList;
            return (object) $arr;
        })->sortByDesc('LanBaoCao')->values();
    }

    public function getBaoCaosAttribute()
    {
        return $this->getBaoCaoList();
    }

    public function addBaoCao($lanBaoCao, $noiDung, $fileBaoCao = null)
    {
        $baoCao = $this->attributes['BaoCaoTienDo'] ?? [];
        if (is_string($baoCao)) {
            $baoCao = json_decode($baoCao, true) ?? [];
        }
        if (!is_array($baoCao)) $baoCao = [];

        $newId = (string) new \MongoDB\BSON\ObjectId();
        $baoCao[] = [
            'id' => $newId,
            '_id' => $newId,
            'MaBaoCao' => $newId,
            'LanBaoCao' => $lanBaoCao,
            'NoiDung' => $noiDung,
            'FileBaoCao' => $fileBaoCao,
            'TrangThai' => 'Chờ duyệt',
            'NgayNop' => now()->toDateString(),
            'NhanXet' => [],
        ];
        $this->BaoCaoTienDo = $baoCao;
        $this->save();
        return $newId;
    }

    public function findBaoCao($baoCaoId)
    {
        return $this->getBaoCaoList()->first(function($bc) use ($baoCaoId) {
            $idStr = (string) ($bc->id ?? $bc->_id ?? $bc->MaBaoCao ?? '');
            return $idStr === (string) $baoCaoId;
        });
    }

    public function addNhanXetToBaoCao($baoCaoId, $maGV, $noiDung)
    {
        $baoCao = $this->attributes['BaoCaoTienDo'] ?? [];
        if (is_string($baoCao)) {
            $baoCao = json_decode($baoCao, true) ?? [];
        }
        if (!is_array($baoCao)) $baoCao = [];

        $found = false;
        foreach ($baoCao as &$bc) {
            if (!is_array($bc)) $bc = (array) $bc;
            $idStr = (string) ($bc['id'] ?? $bc['_id'] ?? $bc['MaBaoCao'] ?? '');
            if ($idStr === (string) $baoCaoId) {
                $bc['NhanXet'] = $bc['NhanXet'] ?? [];
                $bc['NhanXet'][] = [
                    'MaGV' => (string) $maGV,
                    'NoiDung' => $noiDung,
                    'NgayNhanXet' => now()->toDateString(),
                ];
                $bc['TrangThai'] = 'Đã duyệt';
                $found = true;
                break;
            }
        }

        // Fallback: nếu id chưa khớp nhưng mảng báo cáo có phần tử -> gán cho phần tử đầu tiên / tương ứng
        if (!$found && !empty($baoCao)) {
            foreach ($baoCao as &$bc) {
                if (!is_array($bc)) $bc = (array) $bc;
                $bc['NhanXet'] = $bc['NhanXet'] ?? [];
                $bc['NhanXet'][] = [
                    'MaGV' => (string) $maGV,
                    'NoiDung' => $noiDung,
                    'NgayNhanXet' => now()->toDateString(),
                ];
                $bc['TrangThai'] = 'Đã duyệt';
                break;
            }
        }

        $this->BaoCaoTienDo = $baoCao;
        $this->save();
    }

    // ======== SanPham (embedded array) ========

    public function getSanPhamList()
    {
        $list = $this->attributes['SanPham'] ?? [];
        if (!is_array($list)) return collect([]);
        return collect($list)->map(function ($item) {
            if (is_array($item)) return (object) $item;
            if (is_object($item)) return $item;
            if (is_string($item)) {
                $decoded = json_decode($item, true);
                if (is_array($decoded)) return (object) $decoded;
            }
            return null;
        })->filter(function ($sp) {
            return $sp && (!empty($sp->TenSanPham) || !empty($sp->LinkFile) || !empty($sp->LinkSourceCode));
        })->values();
    }

    public function getSanPhamsAttribute()
    {
        return $this->getSanPhamList();
    }

    public function addSanPham($tenSanPham, $linkFile = null, $linkSourceCode = null)
    {
        $sanPham = $this->SanPham ?? [];
        $newId = (string) new \MongoDB\BSON\ObjectId();
        $sanPham[] = [
            '_id' => $newId,
            'TenSanPham' => $tenSanPham,
            'LinkFile' => $linkFile,
            'LinkSourceCode' => $linkSourceCode,
            'NgayNop' => now()->toDateString(),
        ];
        $this->SanPham = $sanPham;
        $this->save();
        return $newId;
    }

    public function updateSanPham($sanPhamId, $data)
    {
        $sanPham = $this->SanPham ?? [];
        foreach ($sanPham as &$sp) {
            if (($sp['_id'] ?? '') === $sanPhamId) {
                $sp = array_merge($sp, $data);
                break;
            }
        }
        $this->SanPham = $sanPham;
        $this->save();
    }

    public function findSanPham($sanPhamId)
    {
        return $this->getSanPhamList()->firstWhere('_id', $sanPhamId);
    }

    // ======== ChamDiem (embedded object) ========

    public function getChamDiem()
    {
        return $this->ChamDiem;
    }

    public function getChamDiemAttribute()
    {
        $cd = $this->attributes['ChamDiem'] ?? null;
        return !empty($cd) ? (object) $cd : null;
    }

    public function hasChamDiem()
    {
        return !empty($this->ChamDiem);
    }

    public function setChamDiem($maGV, $diemBaoCao, $diemBaoVe, $diemTong, $nhanXet = null, $loaiCham = 'Hướng dẫn')
    {
        $this->ChamDiem = [
            'MaGV' => (string) $maGV,
            'LoaiCham' => $loaiCham,
            'DiemBaoCao' => (float) $diemBaoCao,
            'DiemBaoVe' => (float) $diemBaoVe,
            'DiemTong' => (float) $diemTong,
            'NhanXet' => $nhanXet,
            'NgayCham' => now()->toDateString(),
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ];
        $this->save();
    }

    // ======== Static query helpers ========

    /**
     * Tìm tất cả nhóm mà sinh viên tham gia
     */
    public static function findByThanhVien($maSV)
    {
        return static::where('ThanhVien.MaSV', (string) $maSV)->get();
    }

    /**
     * Tìm tất cả nhóm mà sinh viên tham gia (đang hoạt động)
     */
    public static function findActiveByThanhVien($maSV)
    {
        return static::where('ThanhVien.MaSV', (string) $maSV)
            ->whereNotIn('TrangThai', ['Đã hoàn thành', 'Đã chấm điểm'])
            ->where(function ($q) {
                $q->whereNull('ChamDiem')
                  ->orWhere('ChamDiem', []);
            })
            ->get();
    }

    /**
     * Tìm nhóm có lời mời đang chờ cho sinh viên
     */
    public static function findPendingLoiMoiForSV($maSV)
    {
        return static::where('LoiMoi.MaSV_DuocMoi', (string) $maSV)
            ->where('LoiMoi.TrangThai', 'cho_xac_nhan')
            ->get();
    }
}