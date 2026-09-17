<?php

namespace App\Http\Controllers;

use App\Models\Lop;
use App\Models\Nganh;
use App\Models\HocKy;
use App\Models\SinhVien;
use App\Models\PhanCongHuongDanLop;
use App\Models\NhomDoAn;
use App\Http\Traits\HandlesExcelImport;
use Illuminate\Http\Request;

class LopController extends Controller
{
    use HandlesExcelImport;

    public function index(Request $request)
    {
        $query = Lop::with(['nganh', 'hocKy']);

        if ($request->filled('search')) {
            $kw = $request->search;
            $query->where(function($q) use ($kw) {
                $q->where('TenLop', 'like', "%{$kw}%")
                  ->orWhere('MaLop', 'like', "%{$kw}%")
                  ->orWhere('KhoaHoc', 'like', "%{$kw}%");
            });
        }

        if ($request->filled('MaNganh')) {
            $query->where('MaNganh', $request->MaNganh);
        }

        $lops = $query->orderBy('_id', 'desc')->paginate(10)->withQueryString();
        return view('admin.lop.index', compact('lops'));
    }

    public function create()
    {
        $nganhs = Nganh::all();
        $hocKies = HocKy::orderBy('_id', 'desc')->get();
        return view('admin.lop.create', compact('nganhs', 'hocKies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenLop' => 'required|string|max:50|unique:lop,TenLop',
            'MaNganh' => 'required',
            'MaHocKy' => 'nullable',
            'KhoaHoc' => 'required|string|max:20'
        ], [
            'TenLop.required' => 'Vui lòng nhập tên lớp.',
            'TenLop.unique' => 'Tên lớp này đã tồn tại.',
            'MaNganh.required' => 'Vui lòng chọn ngành.',
            'KhoaHoc.required' => 'Vui lòng nhập khóa học.'
        ]);

        $data = $request->only(['TenLop', 'MaNganh', 'MaHocKy', 'KhoaHoc']);
        $data['MaLop'] = 'LOP' . str_pad((Lop::count() + 1), 2, '0', STR_PAD_LEFT);
        Lop::create($data);
        return redirect()->route('lop.index')->with('success', 'Thêm lớp thành công!');
    }

    private function findLop($id)
    {
        return Lop::where('_id', $id)->orWhere('MaLop', $id)->firstOrFail();
    }

    public function show($id)
    {
        $lop = $this->findLop($id);
        
        // 1. Sinh viên thuộc lớp
        $sinhViens = SinhVien::where('MaLop', $lop->MaLop)->orWhere('MaLop', $lop->_id)->get();
        
        // 2. Phân công giảng viên phụ trách Lớp
        $phanCongs = PhanCongHuongDanLop::where('MaLop', $lop->MaLop)->orWhere('MaLop', $lop->_id)->get();
        
        // 3. Nhóm đồ án & tiến độ của lớp
        $svIds = $sinhViens->pluck('MaSV')->toArray();
        $nhoms = NhomDoAn::where(function($q) use ($svIds) {
            foreach($svIds as $svId) {
                $q->orWhere('ThanhVien.MaSV', $svId);
            }
        })->get();

        return view('admin.lop.show', compact('lop', 'sinhViens', 'phanCongs', 'nhoms'));
    }

    public function edit($id)
    {
        $lop = $this->findLop($id);
        $nganhs = Nganh::all();
        $hocKies = HocKy::orderBy('_id', 'desc')->get();
        return view('admin.lop.edit', compact('lop', 'nganhs', 'hocKies'));
    }

    public function update(Request $request, $id)
    {
        $lop = $this->findLop($id);

        $request->validate([
            'TenLop' => 'required|string|max:50',
            'MaNganh' => 'required',
            'MaHocKy' => 'nullable',
            'KhoaHoc' => 'required|string|max:50'
        ], [
            'TenLop.required' => 'Vui lòng nhập tên lớp.',
            'MaNganh.required' => 'Vui lòng chọn ngành.',
            'KhoaHoc.required' => 'Vui lòng nhập hoặc chọn khóa học/năm học.'
        ]);

        $lop->update($request->only(['TenLop', 'MaNganh', 'MaHocKy', 'KhoaHoc']));
        return redirect()->route('lop.index')->with('success', 'Cập nhật thông tin lớp thành công!');
    }

    public function destroy($id)
    {
        try {
            $lop = $this->findLop($id);
            $lop->delete();
            return redirect()->route('lop.index')->with('success', 'Xóa lớp thành công!');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Xóa Lớp lỗi: ' . $e->getMessage());
            return redirect()->back()->withErrors("Không thể xóa lớp: " . $e->getMessage());
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importLop', [], 'Lớp');
    }
}