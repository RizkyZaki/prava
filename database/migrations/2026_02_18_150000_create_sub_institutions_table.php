<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('institution_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        // Migrate existing sub_institution strings to the new table
        $existing = DB::table('projects')
            ->whereNotNull('sub_institution')
            ->where('sub_institution', '!=', '')
            ->select('sub_institution', 'institution_id')
            ->distinct()
            ->get();

        foreach ($existing as $row) {
            DB::table('sub_institutions')->insertOrIgnore([
                'name' => $row->sub_institution,
                'institution_id' => $row->institution_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add sub_institution_id to projects
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('sub_institution_id')->nullable()->constrained('sub_institutions')->nullOnDelete();
        });

        // Map old string values to new IDs
        $subInstitutions = DB::table('sub_institutions')->get();
        foreach ($subInstitutions as $sub) {
            DB::table('projects')
                ->where('sub_institution', $sub->name)
                ->update(['sub_institution_id' => $sub->id]);
        }
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['sub_institution_id']);
            $table->dropColumn('sub_institution_id');
        });

        Schema::dropIfExists('sub_institutions');
    }
};
