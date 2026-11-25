<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->whereIn('platform', ['PS3', 'PS4'])
            ->update(['price' => 45000]);

        DB::table('products')
            ->whereIn('platform', ['PS3', 'PS4'])
            ->where('stock', '<', 1)
            ->update(['stock' => 1]);
    }

    public function down(): void
    {
        // Keep the demo inventory data on rollback; stock changes may have happened since migration.
    }
};
