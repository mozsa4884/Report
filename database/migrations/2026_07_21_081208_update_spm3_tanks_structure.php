<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Delete old SPM3 tanks with main_hole: (D+B)/2, DEPAN, and BELAKANG
        DB::table('tanks')
            ->where('code', 'SPM3')
            ->whereIn('main_hole', ['(D+B)/2', 'DEPAN', 'BELAKANG'])
            ->delete();
        
        // Create new SPM3 tank with proper naming
        DB::table('tanks')->insert([
            'code' => 'SPM3',
            'main_hole' => '(DEPAN + BELAKANG) / 2',
            'capacity' => 0,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete the new tank
        DB::table('tanks')
            ->where('code', 'SPM3')
            ->where('main_hole', '(DEPAN + BELAKANG) / 2')
            ->delete();
        
        // Restore old tanks (if needed for rollback)
        DB::table('tanks')->insert([
            [
                'code' => 'SPM3',
                'main_hole' => '(D+B)/2',
                'capacity' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'SPM3',
                'main_hole' => 'DEPAN',
                'capacity' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'SPM3',
                'main_hole' => 'BELAKANG',
                'capacity' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
};
