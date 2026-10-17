<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class OtpService
{
    protected int $ttl = 300; // 5 phút

    /**
     * Tạo mã OTP ngẫu nhiên 6 chữ số và lưu vào Redis
     */
    public function generateOtp(string $username): string
    {
        $otp = (string) rand(100000, 999999);
        $key = "otp:{$username}";

        try {
            Redis::setex($key, $this->ttl, $otp);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Cache::store('file')->put($key, $otp, $this->ttl);
        }

        return $otp;
    }

    /**
     * Lấy mã OTP hiện tại từ Redis
     */
    public function getOtp(string $username): ?string
    {
        $key = "otp:{$username}";
        try {
            $val = Redis::get($key);
            if ($val !== null) return (string)$val;
        } catch (\Throwable $e) {
        }
        return \Illuminate\Support\Facades\Cache::store('file')->get($key);
    }

    /**
     * Lấy thời gian sống (TTL) còn lại của OTP trong Redis
     */
    public function getTtl(string $username): int
    {
        $key = "otp:{$username}";
        try {
            $ttl = Redis::ttl($key);
            return $ttl > 0 ? $ttl : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Xác thực mã OTP và xóa key sau khi xác thực thành công
     */
    public function verifyOtp(string $username, string $code): bool
    {
        $key = "otp:{$username}";
        $savedOtp = $this->getOtp($username);

        if ($savedOtp && trim((string)$savedOtp) === trim((string)$code)) {
            try {
                Redis::del($key);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Cache::store('file')->forget($key);
            }
            return true;
        }

        return false;
    }
}
