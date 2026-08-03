<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<int, string> */
    private array $tables = ['tournaments', 'schedules', 'photos', 'news', 'signups'];

    public function up(): void
    {
        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->foreignId('edition_id')->nullable()->after('id')->constrained();
            });
        }

        // Everything that exists today belongs to the running edition.
        $editionId = DB::table('editions')->where('is_active', true)->value('id');

        if ($editionId) {
            foreach ($this->tables as $name) {
                DB::table($name)->whereNull('edition_id')->update(['edition_id' => $editionId]);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropConstrainedForeignId('edition_id');
            });
        }
    }
};
