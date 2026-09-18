<?php

namespace App\Http\Controllers;

use App\Models\HocKy;
use App\Http\Traits\HandlesExcelImport;
use Illuminate\Http\Request;

class HocKyController extends Controller
{
    use HandlesExcelImport;

    public function index(Request $request)
    {
        $query = HocKy::query();

        if ($request->filled('search')) {
            $kw = $request->search;
            $query->where(function($q) use ($kw) {
                $q->where('TenHocKy', 'like', "%{$kw}%")
                  ->orWhere('NamHoc', 'like', "%{$kw}%")
                  ->orWhere('MaHocKy', 'like', "%{$kw}%");
            });
        }

        if ($request->filled('NamHoc')) {
            $query->where('NamHoc', $request->NamHoc);
        }

        $hockys = $query->orderBy('_id', 'desc')->paginate(5)->withQueryString();
        return view('admin.hocky.index', compact('hockys'));
    }

    public function create()
    {
        return view('admin.hocky.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'MaHocKy' => 'nullable|string|max:50',
            'TenHocKy' => 'required|string|max:50',
            'NamHoc' => 'required|string|max:20',
            'NgayBatDau' => 'nullable|date',
            'NgayKetThuc' => 'nullable|date|after_or_equal:NgayBatDau'
        ], [
            'TenHocKy.required' => 'Vui lòng nhập tên học kỳ.',
            'NamHoc.required' => 'Vui lòng nhập năm học.',
            'NgayKetThuc.after_or_equal' => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.'
        ]);

        $data = $request->only(['MaHocKy', 'TenHocKy', 'NamHoc', 'NgayBatDau', 'NgayKetThuc']);
        if (empty($data['MaHocKy'])) {
            $cleanTen = preg_replace('/[^a-zA-Z0-9]/', '', $data['TenHocKy']);
            $cleanNam = preg_replace('/[^a-zA-Z0-9]/', '', $data['NamHoc']);
            $data['MaHocKy'] = strtoupper($cleanTen . '_' . $cleanNam);
        }

        HocKy::create($data);
        return redirect()->route('hocky.index')->with('success', 'Thêm học kỳ thành công!');
    }

    private function findHocKy($id)
    {
        return HocKy::where('_id', $id)->orWhere('MaHocKy', $id)->orWhere('MaHK', $id)->firstOrFail();
    }

    public function show($id)
    {
        return $this->edit($id);
    }

    public function edit($id)
    {
        $hocky = $this->findHocKy($id);
        return view('admin.hocky.edit', compact('hocky'));
    }

    public function update(Request $request, $id)
    {
        $hocky = $this->findHocKy($id);

        $request->validate([
            'MaHocKy' => 'nullable|string|max:50',
            'TenHocKy' => 'required|string|max:50',
            'NamHoc' => 'required|string|max:20',
            'NgayBatDau' => 'nullable|date',
            'NgayKetThuc' => 'nullable|date|after_or_equal:NgayBatDau'
        ], [
            'TenHocKy.required' => 'Vui lòng nhập tên học kỳ.',
            'NamHoc.required' => 'Vui lòng nhập năm học.',
            'NgayKetThuc.after_or_equal' => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.'
        ]);

        $data = $request->only(['MaHocKy', 'TenHocKy', 'NamHoc', 'NgayBatDau', 'NgayKetThuc']);
        if (empty($data['MaHocKy'])) {
            $cleanTen = preg_replace('/[^a-zA-Z0-9]/', '', $data['TenHocKy']);
            $cleanNam = preg_replace('/[^a-zA-Z0-9]/', '', $data['NamHoc']);
            $data['MaHocKy'] = strtoupper($cleanTen . '_' . $cleanNam);
        }

        $hocky->update($data);
        return redirect()->route('hocky.index')->with('success', 'Cập nhật học kỳ thành công!');
    }

    public function destroy($id)
    {
        try {
            $hocky = $this->findHocKy($id);
            $hocky->delete();
            return redirect()->route('hocky.index')->with('success', 'Xóa học kỳ thành công!');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Xóa Học kỳ lỗi: ' . $e->getMessage());
            return redirect()->back()->withErrors("Không thể xóa học kỳ: " . $e->getMessage());
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importHocKy', [], 'Học Kỳ');
    }
}
