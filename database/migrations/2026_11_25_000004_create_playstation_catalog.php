<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            ['name' => 'PlayStation 3', 'platform' => 'PS3', 'price' => 2500000],
            ['name' => 'PlayStation 4', 'platform' => 'PS4', 'price' => 4800000],
        ] as $console) {
            DB::table('products')->updateOrInsert(
                [
                    'name' => $console['name'],
                    'platform' => $console['platform'],
                    'category' => 'Konsol',
                ],
                [
                    'description' => 'Konsol PlayStation untuk katalog stok Rexel Games.',
                    'price' => $console['price'],
                    'stock' => 6,
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }
    }

    public function down(): void
    {
        DB::table('products')
            ->where('category', 'Konsol')
            ->whereIn('platform', ['PS3', 'PS4'])
            ->whereIn('name', ['PlayStation 3', 'PlayStation 4'])
            ->delete();
    }
};
