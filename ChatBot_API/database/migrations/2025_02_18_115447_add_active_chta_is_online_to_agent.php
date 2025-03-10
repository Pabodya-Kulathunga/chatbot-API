<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('agent', function (Blueprint $table) {
            $table->integer('active_chats')->default(0)->after('group_code'); // Track active chat count
            $table->boolean('is_online')->default(false)->after('active_chats'); // Online status
        });
    }

    public function down()
    {
        Schema::table('agent', function (Blueprint $table) {
            $table->dropColumn(['active_chats', 'is_online']);
        });
    }
};
