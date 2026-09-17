<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\HandlesExcelImport;
use App\Models\LopHocPhan;
use App\Models\MonHoc;
use App\Models\HocKy;
use App\Models\GiangVien;
use App\Models\SinhVien;
use Illuminate\Http\Request;

class LopHocPhanController extends Controller
{
    use HandlesExcelImport;

    public function import(Request $request)
    {
        return $this->runImport($request, 'importLopHocPhan', [], 'Lớp Học Phần');
    }

    public function index(Request $request)
    {
        $query = LopHocPhan::with(['monHoc', 'hocKy', 'giangVien']);

        if ($request->filled('search')) {
            $kw = $request->search;
            $query->where(function($q) use ($kw) {
                $q->where('TenLopHP', 'like', "%{$kw}%")
                  ->orWhere('MaLopHP', 'like', "%{$kw}%");
            });
        }

        if ($request->filled('ma_mon')) {
            $query->where('MaMon', $request->ma_mon);
        }

        if ($request->filled('ma_hoc_ky')) {
            $query->where('MaHocKy', $request->ma_hoc_ky);
        }

        if ($request->filled('ma_gv')) {
            $query->where('MaGV', $request->ma_gv);
        }

        $lopHocPhans = $query->orderBy('_id', 'desc')->paginate(10)->withQueryString();
        $monHocs = MonHoc::all();
        $hocKies = HocKy::orderBy('_id', 'desc')->get();
        $giangViens = GiangVien::all();

        return view('admin.lophocphan.index', compact('lopHocPhans', 'monHocs', 'hocKies', 'giangViens'));
    }

    public function create()
    {
        $monHocs = MonHoc::all();
        $hocKies = HocKy::orderBy('_id', 'desc')->get();
        $giangViens = GiangVien::all();

        return view('admin.lophocphan.create', compact('monHocs', 'hocKies', 'giangViens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenLopHP' => 'required|string|max:100',
            'MaMon' => 'required|string',
            'MaHocKy' => 'required|string',
            'MaGV' => 'required|string',
            'SiSoToiDa' => 'required|integer|min:1|max:200',
            'TrangThai' => 'required|in:Đang mở,Đã đóng',
        ], [
            'TenLopHP.required' => 'Vui lòng nhập tên lớp học phần.',
            'MaMon.required' => 'Vui lòng chọn môn học.',
            'MaHocKy.required' => 'Vui lòng chọn học kỳ.',
            'MaGV.required' => 'Vui lòng chọn giảng viên phụ trách.',
            'SiSoToiDa.required' => 'Vui lòng nhập sĩ số tối đa.',
        ]);

        // Kiểm tra trùng tên lớp học phần
        if (LopHocPhan::where('TenLopHP', $request->TenLopHP)->exists()) {
            return redirect()->back()->withErrors('Tên lớp học phần này đã tồn tại.')->withInput();
        }

        LopHocPhan::create([
            'TenLopHP' => $request->TenLopHP,
            'MaMon' => $request->MaMon,
            'MaHocKy' => $request->MaHocKy,
            'MaGV' => $request->MaGV,
            'SiSoToiDa' => (int) $request->SiSoToiDa,
            'TrangThai' => $request->TrangThai,
            'DanhSachSinhVien' => [],
        ]);

        return redirect()->route('admin.lophocphan.index')
            ->with('success', 'Tạo Lớp Học Phần thành công!');
    }

    private function findLopHocPhan($id)
    {
        return LopHocPhan::where('_id', $id)->orWhere('MaLopHP', $id)->firstOrFail();
    }

    public function show($id)
    {
        $lopHocPhan = $this->findLopHocPhan($id);

        // All students enrolled in this section (from embedded array)
        $enrolledSvIds = $lopHocPhan->getSinhVienIds()->toArray();
        $enrolledStudents = SinhVien::with('lop')->whereIn('_id', $enrolledSvIds)->orWhereIn('MaSV', $enrolledSvIds)->get();

        // Query available students not yet in this section
        $availableStudents = SinhVien::with('lop')
            ->whereNotIn('_id', $enrolledSvIds)
            ->whereNotIn('MaSV', $enrolledSvIds)
            ->orderBy('HoTen')
            ->get();

        return view('admin.lophocphan.show', compact('lopHocPhan', 'enrolledStudents', 'availableStudents'));
    }

    public function edit($id)
    {
        $lopHocPhan = $this->findLopHocPhan($id);
        $monHocs = MonHoc::all();
        $hocKies = HocKy::orderBy('_id', 'desc')->get();
        $giangViens = GiangVien::all();

        return view('admin.lophocphan.edit', compact('lopHocPhan', 'monHocs', 'hocKies', 'giangViens'));
    }

    public function update(Request $request, $id)
    {
        $lopHocPhan = $this->findLopHocPhan($id);

        $request->validate([
            'TenLopHP' => 'required|string|max:100',
            'MaMon' => 'required|string',
            'MaHocKy' => 'required|string',
            'MaGV' => 'required|string',
            'SiSoToiDa' => 'required|integer|min:1|max:200',
            'TrangThai' => 'required|in:Đang mở,Đã đóng',
        ]);

        $lopHocPhan->update([
            'TenLopHP' => $request->TenLopHP,
            'MaMon' => $request->MaMon,
            'MaHocKy' => $request->MaHocKy,
            'MaGV' => $request->MaGV,
            'SiSoToiDa' => (int) $request->SiSoToiDa,
            'TrangThai' => $request->TrangThai,
        ]);

        return redirect()->route('admin.lophocphan.index')
            ->with('success', 'Cập nhật Lớp Học Phần thành công!');
    }

    public function destroy($id)
    {
        $lopHocPhan = $this->findLopHocPhan($id);
        $lopHocPhan->delete();

        return redirect()->route('admin.lophocphan.index')
            ->with('success', 'Đã xóa Lớp Học Phần thành công!');
    }

    public function addStudent(Request $request, $id)
    {
        $lopHocPhan = $this->findLopHocPhan($id);

        $request->validate([
            'MaSV' => 'required|string',
        ], [
            'MaSV.required' => 'Vui lòng chọn sinh viên.',
        ]);

        $maSV = $request->MaSV;

        // Kiểm tra sinh viên tồn tại
        $sv = SinhVien::where('MaSV', $maSV)->orWhere('_id', $maSV)->first();
        if (!$sv) {
            return back()->with('error', 'Không tìm thấy sinh viên!');
        }

        // Check if student is already in this section
        if ($lopHocPhan->hasSinhVien($maSV)) {
            return back()->with('error', 'Sinh viên này đã thuộc Lớp Học Phần này rồi!');
        }

        // Check if student is already in ANY class section for this Subject & Semester
        $otherLhp = LopHocPhan::where('MaMon', $lopHocPhan->MaMon)
            ->where('MaHocKy', $lopHocPhan->MaHocKy)
            ->where('_id', '!=', (string) $lopHocPhan->_id)
            ->where('DanhSachSinhVien.MaSV', (string) $maSV)
            ->first();

        if ($otherLhp) {
            return back()->with('error', "Sinh viên này đã thuộc Lớp Học Phần '{$otherLhp->TenLopHP}' của môn này trong cùng học kỳ!");
        }

        // Check class capacity limit
        $currentCount = count($lopHocPhan->DanhSachSinhVien ?? []);
        if ($currentCount >= $lopHocPhan->SiSoToiDa) {
            return back()->with('error', "Lớp Học Phần đã đủ sĩ số tối đa ({$lopHocPhan->SiSoToiDa} sinh viên)!");
        }

        // Add student via embedded array
        $lopHocPhan->addSinhVien($maSV);

        return back()->with('success', 'Thêm sinh viên vào Lớp Học Phần thành công!');
    }

    public function removeStudent($id, $maSV)
    {
        $lopHocPhan = $this->findLopHocPhan($id);

        // Remove student via embedded array
        $lopHocPhan->removeSinhVien($maSV);

        return back()->with('success', 'Đã xóa sinh viên khỏi Lớp Học Phần!');
    }

    public function importStudents(Request $request, $id)
    {
        $lopHocPhan = $this->findLopHocPhan($id);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ], [
            'file.required' => 'Vui lòng chọn tệp Excel để import.',
            'file.mimes' => 'Tệp phải có định dạng .xlsx, .xls hoặc .csv.',
        ]);

        try {
            $importService = new \App\Services\ExcelImportService();
            $result = $importService->importSinhVienLopHocPhan($request->file('file'), (string) $lopHocPhan->_id);

            $msg = "Đã import thành công {$result['success_count']} sinh viên vào Lớp Học Phần!";
            if (!empty($result['errors'])) {
                $errMsgs = array_map(fn($e) => $e['reason'] ?? '', $result['errors']);
                return redirect()->back()->with('success', $msg)->withErrors($errMsgs);
            }
            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Lỗi import: ' . $e->getMessage());
        }
    }
}

