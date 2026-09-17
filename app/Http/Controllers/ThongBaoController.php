<?php

namespace App\Http\Controllers;

use App\Models\ThongBao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThongBaoController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = $user->VaiTro ?? '';
        $lops = collect();
        $lopHocPhans = collect();

        if ($role === 'Admin') {
            $layout = 'layouts.admin';
            $lops = \App\Models\Lop::orderBy('TenLop', 'asc')->get();
            $lopHocPhans = \App\Models\LopHocPhan::with(['monHoc', 'hocKy'])->orderBy('_id', 'desc')->get();
            $thongbaos = ThongBao::where('MaTK', (string) $user->_id)->orderBy('_id', 'desc')->get();
        } elseif ($role === 'Giảng viên') {
            $layout = 'layouts.giangvien';
            $gv = \App\Models\GiangVien::where('MaTK', (string) $user->_id)->first();
            $gvId = $gv ? (string) $gv->_id : null;

            // Lớp Hành chính
            $assignedLopIds = \App\Models\PhanCongHuongDanLop::where('MaGV', $gvId)->pluck('MaLop')->toArray();
            $lops = \App\Models\Lop::whereIn('MaLop', $assignedLopIds)->get();

            // Lớp Học Phần (Lớp Tín Chỉ)
            $lopHocPhans = \App\Models\LopHocPhan::with(['monHoc', 'hocKy'])->where('MaGV', $gvId)->get();
            $assignedLhpIds = $lopHocPhans->pluck('MaLopHP')->toArray();

            $adminIds = \App\Models\TaiKhoan::where('VaiTro', 'Admin')->get()->map(fn($item) => (string) $item->_id)->toArray();
            $allowedIds = array_merge([(string) $user->_id], $adminIds);
            
            $thongbaos = ThongBao::whereIn('MaTK', $allowedIds)
                                ->orWhereIn('MaLop', $assignedLopIds)
                                ->orWhereIn('MaLopHP', $assignedLhpIds)
                                ->with(['lop', 'lopHocPhan', 'taiKhoan'])
                                ->orderBy('NgayTao', 'desc')
                                ->get();
        } else {
            abort(403);
        }

        return view('thongbao.index', compact('layout', 'thongbaos', 'lops', 'lopHocPhans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TieuDe' => 'required|max:255',
            'NoiDung' => 'required',
            'file' => 'nullable|file|max:10240', // Max 10MB
        ], [
            'TieuDe.required' => 'Vui lòng nhập tiêu đề thông báo.',
            'TieuDe.max' => 'Tiêu đề không được vượt quá 255 ký tự.',
            'NoiDung.required' => 'Vui lòng nhập nội dung thông báo.',
            'file.max' => 'File đính kèm không được vượt quá 10MB.'
        ]);

        $maLop = null;
        $maLopHP = null;

        if ($request->filled('Target')) {
            $val = $request->Target;
            if (str_starts_with($val, 'lhp_')) {
                $maLopHP = (int) str_replace('lhp_', '', $val);
            } elseif (str_starts_with($val, 'lh_')) {
                $maLop = (int) str_replace('lh_', '', $val);
            }
        } elseif ($request->filled('MaLop')) {
            $maLop = $request->MaLop;
        } elseif ($request->filled('MaLopHP')) {
            $maLopHP = $request->MaLopHP;
        }

        $fileDinhKem = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/thongbao'), $fileName);
            $fileDinhKem = 'uploads/thongbao/' . $fileName;
        }

        ThongBao::create([
            'MaTK' => (string) Auth::user()->_id,
            'MaLop' => $maLop,
            'MaLopHP' => $maLopHP,
            'TieuDe' => $request->TieuDe,
            'NoiDung' => $request->NoiDung,
            'DuongDan' => $request->DuongDan,
            'FileDinhKem' => $fileDinhKem,
            'NgayTao' => date('Y-m-d')
        ]);

        return redirect()->back()->with('success', 'Tạo thông báo thành công!');
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $request->validate([
            'TieuDe' => 'required|max:255',
            'NoiDung' => 'required',
            'file' => 'nullable|file|max:10240',
            'DuongDan' => 'nullable|url',
        ], [
            'TieuDe.required' => 'Vui lòng nhập tiêu đề thông báo.',
            'TieuDe.max' => 'Tiêu đề không được vượt quá 255 ký tự.',
            'NoiDung.required' => 'Vui lòng nhập nội dung thông báo.',
            'file.max' => 'File đính kèm không được vượt quá 10MB.',
            'DuongDan.url' => 'Đường dẫn liên kết không đúng định dạng URL (vd: https://...)'
        ]);

        $tb = ThongBao::where('_id', $id)->firstOrFail();

        // Check ownership (Admin can update their notifications, Giang Vien theirs)
        if ($user->VaiTro !== 'Admin' && (string)$tb->MaTK !== (string)$user->_id) {
            abort(403, 'Bạn không có quyền chỉnh sửa thông báo này.');
        }

        $maLop = null;
        $maLopHP = null;

        if ($request->filled('Target')) {
            $val = $request->Target;
            if (str_starts_with($val, 'lhp_')) {
                $maLopHP = (int) str_replace('lhp_', '', $val);
            } elseif (str_starts_with($val, 'lh_')) {
                $maLop = (int) str_replace('lh_', '', $val);
            }
        } elseif ($request->filled('MaLop')) {
            $maLop = $request->MaLop;
        } elseif ($request->filled('MaLopHP')) {
            $maLopHP = $request->MaLopHP;
        }

        $fileDinhKem = $tb->FileDinhKem;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/thongbao'), $fileName);
            $fileDinhKem = 'uploads/thongbao/' . $fileName;
        } elseif ($request->boolean('delete_file')) {
            $fileDinhKem = null;
        }

        $tb->update([
            'TieuDe' => $request->TieuDe,
            'NoiDung' => $request->NoiDung,
            'DuongDan' => $request->DuongDan,
            'MaLop' => $maLop,
            'MaLopHP' => $maLopHP,
            'FileDinhKem' => $fileDinhKem,
        ]);

        return redirect()->back()->with('success', 'Cập nhật thông báo thành công!');
    }

    public function destroy($id)
    {
        $tb = ThongBao::where('_id', $id)->where('MaTK', (string) Auth::user()->_id)->firstOrFail();
        $tb->delete();

        return redirect()->back()->with('success', 'Đã xóa thông báo!');
    }
}
