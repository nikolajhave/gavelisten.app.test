<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('legacy_id')->nullable()->unique()->after('id');
        });

        Schema::table('wishes', function (Blueprint $table) {
            $table->unsignedBigInteger('legacy_id')->nullable()->index()->after('wishlist_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wishes', function (Blueprint $table) {
            $table->dropColumn('legacy_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('legacy_id');
        });
    }
};
