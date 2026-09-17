<?php

namespace App\Http\Controllers;

use App\Models\Nganh;
use App\Http\Traits\HandlesExcelImport;
use Illuminate\Http\Request;

class NganhController extends Controller
{
    use HandlesExcelImport;
    public function index(Request $request)
    {
        $query = Nganh::with('boMon');

        if ($request->filled('search')) {
            $kw = $request->search;
            $query->where(function($q) use ($kw) {
                $q->where('TenNganh', 'like', "%{$kw}%")
                  ->orWhere('MaNganh', 'like', "%{$kw}%")
                  ->orWhere('MoTa', 'like', "%{$kw}%");
            });
        }

        if ($request->filled('MaBoMon')) {
            $query->where('MaBoMon', $request->MaBoMon);
        }

        $nganhs = $query->orderBy('_id', 'desc')->paginate(10)->withQueryString();
        return view('admin.nganh.index', compact('nganhs'));
    }

    public function create()
    {
        return view('admin.nganh.create');
    }

    public function store(Request $request)
    {
        Nganh::create($request->all());
        return redirect()->route('nganh.index')->with('success', 'Thêm thành công!');
    }

    private function findNganh($id)
    {
        return Nganh::where('_id', $id)->orWhere('MaNganh', $id)->firstOrFail();
    }

    public function show($id)
    {
        return $this->edit($id);
    }

    public function edit($id)
    {
        $nganh = $this->findNganh($id);
        return view('admin.nganh.edit', compact('nganh'));
    }

    public function update(Request $request, $id)
    {
        $nganh = $this->findNganh($id);
        $nganh->update($request->all());
        return redirect()->route('nganh.index')->with('success', 'Cập nhật thành công!');
    }

    public function destroy($id)
    {
        $nganh = $this->findNganh($id);
        $nganh->delete();
        return redirect()->route('nganh.index')->with('success', 'Xóa thành công!');
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importNganh', [], 'Ngành');
    }
}