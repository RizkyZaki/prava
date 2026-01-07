<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Create VIEW attendances from presences table
     * Logic: Remove duplicate presences by taking MIN time for check-in (type='0')
     * and MAX time for check-out (type='1') per fingerprint per day
     */
    public function up(): void
    {
        DB::statement("
    CREATE VIEW attendances AS
    SELECT
        p.id,
        p.enhancer,
        p.company AS opd,
        p.position,
        p.fingerprint,
        p.time,
        p.piece,
        p.price,
        p.late,
        p.earlier,
        p.type,
        p.category,
        p.coordinate,
        p.biometric,
        p.created_at
    FROM (
        SELECT fingerprint, MAX(time) AS time
        FROM presences
        WHERE type = '1' AND deleted_at IS NULL
        GROUP BY fingerprint, DATE(time)

        UNION ALL

        SELECT fingerprint, MIN(time) AS time
        FROM presences
        WHERE type = '0' AND deleted_at IS NULL
        GROUP BY fingerprint, DATE(time)
    ) combined
    JOIN presences p
        ON p.fingerprint = combined.fingerprint
       AND p.time = combined.time
    WHERE p.deleted_at IS NULL
    ORDER BY p.time DESC
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS attendances');
    }
};
