<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->warn('Dự án sử dụng MongoDB.');

        $this->command->comment(
            'Chạy script MongoDB bằng lệnh:'
        );

        $this->command->comment(
            'mongosh mongodb://127.0.0.1:27017/quanly_doan mongodb/run_all.js'
        );

        $this->command->info(
            'Hoặc chạy file batch khởi tạo tự động trên Windows:'
        );

        $this->command->comment(
            '.\\run_mongodb.bat'
        );

        $this->command->info(
            'Sau khi chạy, hãy mở MongoDB Compass và kết nối tới:'
        );

        $this->command->comment(
            'mongodb://127.0.0.1:27017'
        );
    }
}

