<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kiểm tra dữ liệu không hợp lệ
        $invalid = DB::table('generates')
            ->where(function ($query) {
                $query->whereNotNull('conditions')->whereRaw('JSON_VALID(conditions) = 0')
                    ->orWhereNotNull('success_message')->whereRaw('JSON_VALID(success_message) = 0')
                    ->orWhereNotNull('fail_message')->whereRaw('JSON_VALID(fail_message) = 0');
            })
            ->exists();

        if ($invalid) {
            throw new \Exception('Có dữ liệu không phải JSON trong conditions, success_message hoặc fail_message. Vui lòng kiểm tra trước khi migrate.');
        }

        // Tiếp tục migration
        Schema::table('generates', function (Blueprint $table) {
            $table->json('conditions_temp')->after('conditions');
            $table->json('success_message_temp')->nullable()->after('success_message');
            $table->json('fail_message_temp')->nullable()->after('fail_message');
        });

        DB::statement('UPDATE generates SET conditions_temp = conditions');
        DB::statement('UPDATE generates SET success_message_temp = success_message');
        DB::statement('UPDATE generates SET fail_message_temp = fail_message');

        Schema::table('generates', function (Blueprint $table) {
            $table->dropColumn(['conditions', 'success_message', 'fail_message']);
            $table->renameColumn('conditions_temp', 'conditions');
            $table->renameColumn('success_message_temp', 'success_message');
            $table->renameColumn('fail_message_temp', 'fail_message');
        });
    }

    public function down(): void
    {
        // Bước 1: Thêm các cột tạm kiểu string
        Schema::table('generates', function (Blueprint $table) {
            $table->string('conditions_temp')->after('conditions');
            $table->string('success_message_temp')->nullable()->after('success_message');
            $table->string('fail_message_temp')->nullable()->after('fail_message');
        });

        // Bước 2: Chuyển dữ liệu sang cột tạm
        DB::statement('UPDATE generates SET conditions_temp = conditions');
        DB::statement('UPDATE generates SET success_message_temp = success_message');
        DB::statement('UPDATE generates SET fail_message_temp = fail_message');

        // Bước 3: Xóa cột cũ và đổi tên cột tạm
        Schema::table('generates', function (Blueprint $table) {
            $table->dropColumn(['conditions', 'success_message', 'fail_message']);
            $table->renameColumn('conditions_temp', 'conditions');
            $table->renameColumn('success_message_temp', 'success_message');
            $table->renameColumn('fail_message_temp', 'fail_message');
        });
    }
};
