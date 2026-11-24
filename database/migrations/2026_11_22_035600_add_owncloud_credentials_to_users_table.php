<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('owncloud_username')
                ->nullable()
                ->unique();

            $table->text('owncloud_app_password')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique([
                'owncloud_username',
            ]);

            $table->dropColumn([
                'owncloud_username',
                'owncloud_app_password',
            ]);
        });
    }
};
