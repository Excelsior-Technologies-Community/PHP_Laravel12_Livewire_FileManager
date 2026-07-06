<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('media', function (Blueprint $table) {
            if (!Schema::hasColumn('media', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
            if (!Schema::hasColumn('media', 'share_token')) {
                $table->string('share_token')->nullable()->unique()->after('deleted_at');
            }
            if (!Schema::hasColumn('media', 'share_password')) {
                $table->string('share_password')->nullable()->after('share_token');
            }
            if (!Schema::hasColumn('media', 'share_expires_at')) {
                $table->timestamp('share_expires_at')->nullable()->after('share_password');
            }
            if (!Schema::hasColumn('media', 'folder_id')) {
                $table->foreignId('folder_id')->nullable()->constrained('folders')->onDelete('cascade')->after('id');
            }
        });

        Schema::table('folders', function (Blueprint $table) {
            if (!Schema::hasColumn('folders', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
            if (!Schema::hasColumn('folders', 'share_token')) {
                $table->string('share_token')->nullable()->unique()->after('deleted_at');
            }
            if (!Schema::hasColumn('folders', 'share_password')) {
                $table->string('share_password')->nullable()->after('share_token');
            }
            if (!Schema::hasColumn('folders', 'share_expires_at')) {
                $table->timestamp('share_expires_at')->nullable()->after('share_password');
            }
        });
    }

    public function down()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['share_token', 'share_password', 'share_expires_at', 'folder_id']);
        });
        Schema::table('folders', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['share_token', 'share_password', 'share_expires_at']);
        });
    }
};