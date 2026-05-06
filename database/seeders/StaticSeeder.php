<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class StaticSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->seedSqliteLocationData();

            return;
        }

        $sql = File::get(database_path('seeders/data/countries.sql'));
        DB::unprepared($sql);

        $sql = File::get(database_path('seeders/data/cities.sql'));
        DB::unprepared($sql);

        $sql = File::get(database_path('seeders/data/districts.sql'));
        DB::unprepared($sql);
    }

    /**
     * MySQL dump dosyalarındaki yalnızca INSERT ifadelerini SQLite'ta çalıştırır.
     */
    private function seedSqliteLocationData(): void
    {
        if (! Schema::hasTable('countries')) {
            return;
        }

        if (DB::table('countries')->count() > 0) {
            return;
        }

        foreach (['countries.sql', 'cities.sql', 'districts.sql'] as $file) {
            $content = File::get(database_path('seeders/data/'.$file));
            preg_match_all('/INSERT INTO[^;]+;/s', $content, $matches);
            foreach ($matches[0] as $statement) {
                $statement = trim($statement);
                if ($statement !== '') {
                    DB::connection()->getPdo()->exec($statement);
                }
            }
        }
    }
}
