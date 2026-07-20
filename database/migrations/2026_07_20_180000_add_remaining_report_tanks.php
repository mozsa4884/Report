<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Restore the original SPM3 measurement points for deployments whose
     * database was seeded before these rows existed.
     */
    public function up(): void
    {
        foreach (['DEPAN', 'BELAKANG'] as $mainHole) {
            $exists = DB::table('tanks')
                ->where('code', 'SPM3')
                ->where('main_hole', $mainHole)
                ->exists();

            if (! $exists) {
                DB::table('tanks')->insert([
                    'code' => 'SPM3',
                    'main_hole' => $mainHole,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Operational master data is intentionally retained on rollback.
    }
};
