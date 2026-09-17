{{-- ========================================================
   HUIT Enterprise SaaS Footer Component - VIETNIX Blue Style
   Sử dụng: @include('partials.footer')
   ======================================================== --}}
<footer class="huit-footer mt-auto">
    <div class="footer-top">
        <div class="container-fluid px-4 max-w-1440">
            <div class="row g-4">

                {{-- Cột 1: Thông tin trường & Thương hiệu --}}
                <div class="col-lg-4 col-md-6">
                    <div class="footer-logo-area">
                        <img src="{{ asset('images/logotruong.jpg') }}" alt="Logo HUIT" class="footer-logo">
                        <div>
                            <div class="footer-brand-name">
                                TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG<br>TP. HỒ CHÍ MINH
                            </div>
                            <span class="footer-brand-sub">Ho Chi Minh City University of Industry and Trade</span>
                        </div>
                    </div>

                    <p class="footer-desc">
                        Hệ thống Quản lý Đồ án &amp; Khóa luận Tốt nghiệp trực tuyến — Hỗ trợ Quản lý, Phân công Giảng viên, Đăng ký đề tài &amp; Nộp báo cáo tiến độ chuẩn hóa.
                    </p>

                    <div class="footer-contact-list">
                        <div class="footer-contact-item">
                            <div class="footer-contact-icon"><i class="fa-solid fa-location-dot"></i></div>
                            <span>140 Lê Trọng Tấn, Phường Tây Thạnh, Quận Tân Phú, TP.HCM</span>
                        </div>
                        <div class="footer-contact-item">
                            <div class="footer-contact-icon"><i class="fa-solid fa-phone"></i></div>
                            <span>(028) 38 163 318 &nbsp;|&nbsp; Hotline CTSV: (028) 38 161 673</span>
                        </div>
                        <div class="footer-contact-item">
                            <div class="footer-contact-icon"><i class="fa-solid fa-envelope"></i></div>
                            <span>info@huit.edu.vn &nbsp;•&nbsp; kcntt@huit.edu.vn</span>
                        </div>
                        <div class="footer-contact-item">
                            <div class="footer-contact-icon"><i class="fa-solid fa-globe"></i></div>
                            <span>www.huit.edu.vn</span>
                        </div>
                    </div>
                </div>

                {{-- Cột 2: Đường dẫn hệ thống --}}
                <div class="col-lg-2 col-md-6">
                    <div class="footer-col-title">Hệ Thống QLĐA</div>
                    <ul class="footer-link-list">
                        <li>
                            <a href="{{ route('login') }}">
                                <i class="fa-solid fa-angles-right"></i> Trang Đăng Nhập
                            </a>
                        </li>
                        @auth
                            @if(Auth::user()->VaiTro === 'gv')
                                <li><a href="{{ route('giangvien.detai.index') }}"><i class="fa-solid fa-angles-right"></i> Đề tài của tôi</a></li>
                                <li><a href="{{ route('giangvien.duyet.index') }}"><i class="fa-solid fa-angles-right"></i> Duyệt đăng ký</a></li>
                                <li><a href="{{ route('giangvien.baocao.index') }}"><i class="fa-solid fa-angles-right"></i> Duyệt báo cáo</a></li>
                            @elseif(Auth::user()->VaiTro === 'sv')
                                <li><a href="{{ route('sinhvien.dangky.index') }}"><i class="fa-solid fa-angles-right"></i> Đăng ký đề tài</a></li>
                                <li><a href="{{ route('sinhvien.baocao.index') }}"><i class="fa-solid fa-angles-right"></i> Báo cáo tiến độ</a></li>
                                <li><a href="{{ route('sinhvien.nhom.index') }}"><i class="fa-solid fa-angles-right"></i> Nhóm của tôi</a></li>
                            @elseif(Auth::user()->VaiTro === 'admin')
                                <li><a href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-angles-right"></i> Dashboard Admin</a></li>
                                <li><a href="{{ route('sinhvien.index') }}"><i class="fa-solid fa-angles-right"></i> Quản lý sinh viên</a></li>
                                <li><a href="{{ route('giangvien.index') }}"><i class="fa-solid fa-angles-right"></i> Quản lý giảng viên</a></li>
                            @endif
                            <li>
                                <a href="{{ route('profile.show') }}">
                                    <i class="fa-solid fa-angles-right"></i> Hồ sơ cá nhân
                                </a>
                            </li>
                        @endauth
                    </ul>

                    {{-- Trạng thái hệ thống --}}
                    <div class="system-status-pill mt-3">
                        <span class="status-dot-pulse"></span>
                        <span class="status-text">Hệ thống đang chạy 24/7</span>
                    </div>
                </div>

                {{-- Cột 3: Cổng thông tin các đơn vị --}}
                <div class="col-lg-2 col-md-6">
                    <div class="footer-col-title">Liên Hệ Đơn Vị</div>
                    <ul class="footer-link-list">
                        <li><a href="https://cntt.huit.edu.vn" target="_blank"><i class="fa-solid fa-external-link me-1"></i> Khoa CNTT</a></li>
                        <li><a href="https://daotao.huit.edu.vn" target="_blank"><i class="fa-solid fa-external-link me-1"></i> Phòng Đào Tạo</a></li>
                        <li><a href="https://ctsv.huit.edu.vn" target="_blank"><i class="fa-solid fa-external-link me-1"></i> Phòng CTSV</a></li>
                        <li><a href="https://lib.huit.edu.vn" target="_blank"><i class="fa-solid fa-external-link me-1"></i> Thư Viện HUIT</a></li>
                        <li><a href="https://huit.edu.vn" target="_blank"><i class="fa-solid fa-external-link me-1"></i> Cổng TT Nhà Trường</a></li>
                    </ul>
                </div>

                {{-- Cột 4: Bản đồ vị trí & Mạng xã hội --}}
                <div class="col-lg-4 col-md-6">
                    <div class="footer-col-title">Vị Trí &amp; Mạng Xã Hội</div>
                    <div class="footer-map-frame mb-3">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.0774831940624!2d106.6178!3d10.7985!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752bbd0c53e62b%3A0xa58484d0f7af26b4!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBDw7RuZyBUaHXGoW5nIFRQLkhDTQ!5e0!3m2!1svi!2svn!4v1700000000000"
                            width="100%"
                            height="150"
                            style="border:0; display:block;"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Vị trí Trường ĐH Công Thương TP.HCM">
                        </iframe>
                    </div>

                    {{-- Social Icons --}}
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="footer-social-label">Kênh thông tin HUIT:</span>
                        <div class="social-icons">
                            <a href="https://www.facebook.com/truongdhcttphcm" target="_blank" class="social-icon-btn social-fb" title="Facebook HUIT">
                                <i class="fa-brands fa-facebook-f"></i>
                            </a>
                            <a href="https://www.youtube.com/@truongdhcttphcm" target="_blank" class="social-icon-btn social-yt" title="YouTube HUIT">
                                <i class="fa-brands fa-youtube"></i>
                            </a>
                            <a href="https://www.instagram.com/huit.edu.vn/" target="_blank" class="social-icon-btn social-ig" title="Instagram HUIT">
                                <i class="fa-brands fa-instagram"></i>
                            </a>
                            <a href="https://zalo.me/0283816331" target="_blank" class="social-icon-btn social-zalo" title="Zalo HUIT">
                                <i class="fa-solid fa-comment-dots"></i>
                            </a>
                            <a href="https://www.tiktok.com/@huit.edu.vn" target="_blank" class="social-icon-btn social-tiktok" title="TikTok HUIT">
                                <i class="fa-brands fa-tiktok"></i>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <hr class="footer-divider">

    {{-- Copyright bar --}}
    <div class="footer-copyright">
        <div class="container-fluid px-4 max-w-1440">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <p class="mb-0">
                    &copy; {{ date('Y') }} <span class="text-cyan fw-bold">HUIT</span> – Hệ Thống Quản Lý Đồ Án &amp; Khóa Luận Tốt Nghiệp.
                    Phát triển bởi <span class="text-cyan fw-bold">Khoa Công Nghệ Thông Tin</span>.
                </p>
                <div class="footer-security-note">
                    <i class="fa-solid fa-shield-halved me-1 text-cyan"></i>
                    Bảo mật dữ liệu chuẩn ISO 27001 • ĐH Công Thương TP.HCM
                </div>
            </div>
        </div>
    </div>
</footer>
