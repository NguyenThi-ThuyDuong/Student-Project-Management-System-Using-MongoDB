<?php

namespace App\Http\Controllers;

use App\Models\BoMon;
use App\Http\Traits\HandlesExcelImport;
use Illuminate\Http\Request;

class BoMonController extends Controller
{
    use HandlesExcelImport;
    public function index(Request $request)
    {
        $query = BoMon::query();

        if ($request->filled('search')) {
            $kw = $request->search;
            $query->where(function($q) use ($kw) {
                $q->where('TenBoMon', 'like', "%{$kw}%")
                  ->orWhere('MaBoMon', 'like', "%{$kw}%")
                  ->orWhere('MoTa', 'like', "%{$kw}%");
            });
        }

        $bomons = $query->orderBy('_id', 'desc')->paginate(5)->withQueryString();
        return view('admin.bomon.index', compact('bomons'));
    }

    public function create()
    {
        return view('admin.bomon.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenBoMon' => 'required|string|max:100|unique:bo_mons,TenBoMon',
            'MoTa' => 'nullable|string|max:500'
        ], [
            'TenBoMon.required' => 'Vui lòng nhập tên bộ môn.',
            'TenBoMon.unique' => 'Tên bộ môn này đã tồn tại trong hệ thống.',
            'TenBoMon.max' => 'Tên bộ môn không được vượt quá 100 ký tự.'
        ]);

        BoMon::create($request->only(['TenBoMon', 'MoTa']));
        return redirect()->route('bomon.index')->with('success', 'Thêm bộ môn thành công!');
    }

    private function findBoMon($id)
    {
        return BoMon::where('_id', $id)->orWhere('MaBoMon', $id)->firstOrFail();
    }

    public function show($id)
    {
        return $this->edit($id);
    }

    public function edit($id)
    {
        $bomon = $this->findBoMon($id);
        return view('admin.bomon.edit', compact('bomon'));
    }

    public function update(Request $request, $id)
    {
        $bomon = $this->findBoMon($id);

        $request->validate([
            'TenBoMon' => 'required|string|max:100',
            'MoTa' => 'nullable|string|max:500'
        ], [
            'TenBoMon.required' => 'Vui lòng nhập tên bộ môn.',
            'TenBoMon.max' => 'Tên bộ môn không được vượt quá 100 ký tự.'
        ]);

        $bomon->update($request->only(['TenBoMon', 'MoTa']));
        return redirect()->route('bomon.index')->with('success', 'Cập nhật bộ môn thành công!');
    }

    public function destroy($id)
    {
        try {
            $bomon = $this->findBoMon($id);
            $bomon->delete();
            return redirect()->route('bomon.index')->with('success', 'Xóa thành công!');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Xóa Bộ môn lỗi: ' . $e->getMessage());
            return redirect()->back()->withErrors('Không thể xóa bộ môn: ' . $e->getMessage());
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importBoMon', [], 'Bộ Môn');
    }
}
