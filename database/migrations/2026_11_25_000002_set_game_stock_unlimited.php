<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->whereIn('platform', ['PS3', 'PS4'])
            ->update(['stock' => 2147483647]);
    }

    public function down(): void
    {
        // Keep unlimited stock enabled; reverting it could hide products after sales.
    }
};
