<?php

namespace App\Http\Controllers;

use App\Models\MonHoc;
use App\Models\BoMon;
use App\Http\Traits\HandlesExcelImport;
use Illuminate\Http\Request;

class MonHocController extends Controller
{
    use HandlesExcelImport;

    public function index(Request $request)
    {
        $query = MonHoc::with('boMon');

        if ($request->filled('search')) {
            $kw = $request->search;
            $query->where(function($q) use ($kw) {
                $q->where('TenMon', 'like', "%{$kw}%")
                  ->orWhere('MaMon', 'like', "%{$kw}%");
            });
        }

        if ($request->filled('MaBoMon')) {
            $query->where('MaBoMon', $request->MaBoMon);
        }

        $monhocs = $query->orderBy('_id', 'desc')->paginate(10)->withQueryString();
        return view('admin.monhoc.index', compact('monhocs'));
    }

    public function create()
    {
        $bomons = BoMon::all();
        return view('admin.monhoc.create', compact('bomons'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenMon' => 'required|string|max:100|unique:mon_hoc,TenMon',
            'MaBoMon' => 'required|exists:bo_mon,MaBoMon',
            'SoTinChi' => 'required|integer|min:1|max:10'
        ], [
            'TenMon.required' => 'Vui lòng nhập tên môn học.',
            'TenMon.unique' => 'Tên môn học này đã tồn tại.',
            'MaBoMon.required' => 'Vui lòng chọn bộ môn.',
            'SoTinChi.required' => 'Vui lòng nhập số tín chỉ.'
        ]);

        $data = $request->only(['TenMon', 'MaBoMon', 'SoTinChi']);
        $data['MaMon'] = 'MH' . str_pad((MonHoc::count() + 1), 2, '0', STR_PAD_LEFT);
        MonHoc::create($data);
        return redirect()->route('monhoc.index')->with('success', 'Thêm môn học thành công!');
    }

    private function findMonHoc($id)
    {
        return MonHoc::where('_id', $id)->orWhere('MaMon', $id)->firstOrFail();
    }

    public function show($id)
    {
        return $this->edit($id);
    }

    public function edit($id)
    {
        $monhoc = $this->findMonHoc($id);
        $bomons = BoMon::all();
        return view('admin.monhoc.edit', compact('monhoc', 'bomons'));
    }

    public function update(Request $request, $id)
    {
        $monhoc = $this->findMonHoc($id);

        $request->validate([
            'TenMon' => 'required|string|max:100',
            'MaBoMon' => 'required',
            'SoTinChi' => 'required|integer|min:1|max:10'
        ], [
            'TenMon.required' => 'Vui lòng nhập tên môn học.',
            'MaBoMon.required' => 'Vui lòng chọn bộ môn.',
            'SoTinChi.required' => 'Vui lòng nhập số tín chỉ.'
        ]);

        $monhoc->update($request->only(['TenMon', 'MaBoMon', 'SoTinChi']));
        return redirect()->route('monhoc.index')->with('success', 'Cập nhật môn học thành công!');
    }

    public function destroy($id)
    {
        try {
            $monhoc = $this->findMonHoc($id);
            $monhoc->delete();
            return redirect()->route('monhoc.index')->with('success', 'Xóa môn học thành công!');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Xóa Môn học lỗi: ' . $e->getMessage());
            return redirect()->back()->withErrors("Không thể xóa môn học: " . $e->getMessage());
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importMonHoc', [], 'Môn Học');
    }
}
