<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

class NoSqlMonitorController extends Controller
{
    public function index()
    {
        // 1. MongoDB Information
        $mongoDbName = config('database.connections.mongodb.database', 'quanly_doan');
        $mongoStatus = 'Connected';
        $mongoCollections = [];
        $totalCollectionsCount = 0;

        try {
            /** @var \MongoDB\Database $db */
            $db = DB::connection('mongodb')->getMongoDB();
            $collections = $db->listCollections();

            foreach ($collections as $colInfo) {
                $colName = $colInfo->getName();
                $count = $db->selectCollection($colName)->countDocuments();
                $mongoCollections[] = [
                    'name' => $colName,
                    'count' => $count,
                ];
            }
            usort($mongoCollections, fn($a, $b) => strcmp($a['name'], $b['name']));
            $totalCollectionsCount = count($mongoCollections);
        } catch (\Throwable $e) {
            $mongoStatus = 'Error: ' . $e->getMessage();
        }

        // 2. Redis Information
        $redisStatus = 'Disconnected';
        $redisKeysInfo = [];

        try {
            $redis = Redis::connection();
            $ping = $redis->ping();
            if ($ping) {
                $redisStatus = 'Connected (Active)';
            }

            // Fetch keys matching patterns for demo
            $keys = $redis->keys('*');
            if (!empty($keys)) {
                foreach ($keys as $fullKey) {
                    // Normalize prefix if present
                    $rawKey = $fullKey;
                    if (str_contains($fullKey, 'database-')) {
                        $parts = explode('database-', $fullKey, 2);
                        $rawKey = $parts[1] ?? $fullKey;
                    }

                    $type = (string) $redis->type($fullKey);
                    $ttl = $redis->ttl($fullKey);
                    $valStr = '';

                    if ($type === 'string' || $type === '1') {
                        $val = $redis->get($fullKey);
                        $valStr = is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : (string)$val;
                    } else {
                        $valStr = '[Complex Data Structure]';
                    }

                    // Truncate long value
                    if (mb_strlen($valStr) > 80) {
                        $valStr = mb_substr($valStr, 0, 80) . '...';
                    }

                    // Purpose estimation
                    $purpose = 'General Cache';
                    if (str_starts_with($rawKey, 'otp:')) {
                        $purpose = 'Mã OTP Quên Mật Khẩu (TTL 5m)';
                    } elseif (str_starts_with($rawKey, 'cache:de_tai:')) {
                        $purpose = 'Cache Danh Sách Đề Tài (TTL 1h)';
                    } elseif (str_contains($rawKey, 'dashboard:admin')) {
                        $purpose = 'Cache Statistics Dashboard Admin (TTL 10m)';
                    }

                    $redisKeysInfo[] = [
                        'key' => $rawKey,
                        'full_key' => $fullKey,
                        'type' => strtoupper($type === '1' ? 'String' : $type),
                        'ttl' => $ttl > 0 ? $ttl . 's' : ($ttl === -1 ? 'Không hết hạn' : 'Hết hạn'),
                        'value' => $valStr,
                        'purpose' => $purpose,
                    ];
                }
            }
        } catch (\Throwable $e) {
            $redisStatus = 'Error: ' . $e->getMessage();
        }

        return view('admin.nosql.index', compact(
            'mongoDbName', 'mongoStatus', 'mongoCollections', 'totalCollectionsCount',
            'redisStatus', 'redisKeysInfo'
        ));
    }
}
