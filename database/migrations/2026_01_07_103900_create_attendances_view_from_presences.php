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
            (
                SELECT
                    presences.id,
                    presences.enhancer,
                    presences.company AS opd,
                    presences.position,
                    presences.fingerprint,
                    presences.time,
                    presences.piece,
                    presences.price,
                    presences.late,
                    presences.earlier,
                    presences.type,
                    presences.category,
                    presences.coordinate,
                    presences.biometric,
                    presences.created_at
                FROM (
                    SELECT DISTINCT fingerprint, MAX(time) AS time
                    FROM presences
                    WHERE type = '1' AND deleted_at IS NULL
                    GROUP BY fingerprint, DATE(time)
                    UNION ALL
                    SELECT DISTINCT fingerprint, MIN(time) AS time
                    FROM presences
                    WHERE type = '0' AND deleted_at IS NULL
                    GROUP BY fingerprint, DATE(time)
                ) AS combined
                JOIN presences ON combined.fingerprint = presences.fingerprint
                    AND combined.time = presences.time
                WHERE presences.deleted_at IS NULL
                GROUP BY combined.fingerprint, combined.time
            )
            ORDER BY time DESC
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
