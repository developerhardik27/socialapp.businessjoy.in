<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Transfer existing free-text `family_person.shakh` values into the new
     * `shakh` master table and map each family person to its shakh_id.
     *
     * - Names are trimmed and compared case-insensitively so duplicate
     *   variants (" test ", "Test", "TEST") resolve to a single record.
     * - Existing values that cannot be mapped keep their free-text value in
     *   `family_person.shakh` for manual review and are never deleted.
     */
    public function up(): void
    {
        if (!Schema::hasTable('family_person') || !Schema::hasTable('shakh')) {
            return;
        }
        if (!Schema::hasColumn('family_person', 'shakh_id') || !Schema::hasColumn('family_person', 'shakh')) {
            return;
        }

        // Distinct non-empty shakh values across family persons.
        $values = DB::table('family_person')
            ->whereNotNull('shakh')
            ->whereRaw("TRIM(shakh) <> ''")
            ->pluck('shakh')
            ->map(fn($v) => trim($v))
            ->filter()
            ->unique(fn($v) => mb_strtolower($v))
            ->values();

        // Existing master names (non-deleted) for case-insensitive matching.
        $existing = DB::table('shakh')
            ->where('is_deleted', 0)
            ->pluck('name', 'id')
            ->mapWithKeys(fn($name, $id) => [mb_strtolower(trim($name)) => $id])
            ->all();

        // Insert each distinct value that is not already in the master list.
        foreach ($values as $name) {
            $key = mb_strtolower($name);
            if (isset($existing[$key])) {
                continue;
            }
            $id = DB::table('shakh')->insertGetId([
                'name'       => $name,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'is_active'  => 1,
                'is_deleted' => 0,
            ]);
            $existing[$key] = $id;
        }

        // Map family persons to their shakh_id where the free text matches.
        $rows = DB::table('family_person')
            ->whereNotNull('shakh')
            ->whereRaw("TRIM(shakh) <> ''")
            ->get(['id', 'shakh']);

        $mapped = 0;
        foreach ($rows as $row) {
            $id = $existing[mb_strtolower(trim($row->shakh))] ?? null;
            if ($id) {
                DB::table('family_person')->where('id', $row->id)->update(['shakh_id' => $id]);
                $mapped++;
            }
        }

        Log::info("shakh migration: {$values->count()} master values ensured, {$mapped} family persons mapped");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left empty: clearing family_person.shakh_id and
        // deleting master shakh rows would destroy data that may have been
        // created after the migration ran.
    }
};
