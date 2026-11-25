<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            ['platform' => 'PS3', 'price' => 2500000],
            ['platform' => 'PS4', 'price' => 4800000],
        ] as $console) {
            DB::table('products')
                ->where('category', 'Konsol')
                ->where('platform', $console['platform'])
                ->update([
                    'price' => $console['price'],
                    'stock' => 6,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        // Keep the configured catalog prices and stock when rolling back.
    }
};
