<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected ?string $apiKey;
    protected string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY') ?: config('services.gemini.key');
    }

    /**
     * Tự động tóm tắt báo cáo tiến độ bằng Google Gemini API
     */
    public function generateReportSummary(string $noiDungBaoCao, int $giaiDoan = 1): array
    {
        $defaultSummary = [
            'cong_viec_hoan_thanh' => ['Nộp báo cáo tiến độ giai đoạn ' . $giaiDoan],
            'kho_khan' => ['Chưa ghi nhận khó khăn lớn'],
            'ke_hoach_tuan_toi' => ['Tiếp tục thực hiện giai đoạn tiếp theo'],
            'tom_tat' => 'Báo cáo tiến độ giai đoạn ' . $giaiDoan . ': ' . mb_substr($noiDungBaoCao, 0, 150) . '...',
            'ai_generated' => false,
        ];

        if (empty($this->apiKey)) {
            Log::warning('GEMINI_API_KEY không được cấu hình trong .env. Sử dụng tóm tắt mặc định.');
            return $defaultSummary;
        }

        $prompt = "Bạn là AI Assistant hỗ trợ Giảng viên đánh giá đồ án sinh viên. Hãy phân tích nội dung báo cáo tiến độ Giai đoạn {$giaiDoan} dưới đây và trích xuất thông tin ngắn gọn dạng JSON với cấu trúc chính xác sau (không thêm markdown formatting ngoài JSON text):
{
  \"cong_viec_hoan_thanh\": [\"việc 1\", \"việc 2\"],
  \"kho_khan\": [\"vấn đề 1\"],
  \"ke_hoach_tuan_toi\": [\"kế hoạch 1\"],
  \"tom_tat\": \"Tóm tắt tổng quan trong 2-3 câu.\"
}

Nội dung báo cáo:
\"{$noiDungBaoCao}\"";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'maxOutputTokens' => 500,
                ]
            ]);

            if ($response->successful()) {
                $body = $response->json();
                $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';
                
                // Làm sạch JSON output
                $cleanJson = preg_replace('/^```json\s*|\s*```$/m', '', trim($text));
                $parsed = json_decode($cleanJson, true);

                if (is_array($parsed) && isset($parsed['tom_tat'])) {
                    $parsed['ai_generated'] = true;
                    return $parsed;
                }
            } else {
                Log::error('Gemini API Error Response: ' . $response->body());
            }
        } catch (\Throwable $e) {
            Log::error('Lỗi khi kết nối Gemini API: ' . $e->getMessage());
        }

        return $defaultSummary;
    }
}
