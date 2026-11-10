<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportMedicinesCommand extends Command
{
    protected $signature = 'medicines:import {file=database/data/medicines.csv}';

    protected $aliases = ['app:import-medicines'];

    protected $description = 'Stream & batch import 42,000+ medicines into database';

    public function handle(): int
    {
        $filePath = base_path((string) $this->argument('file'));

        if (! file_exists($filePath)) {
            $this->error("File not found at: {$filePath}");

            return self::FAILURE;
        }

        $this->info("Importing medicines from {$filePath}...");
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            $this->error("Unable to open file at: {$filePath}");

            return self::FAILURE;
        }

        fgetcsv($handle); // Skip header row

        $batch = [];
        $batchSize = 1000;
        $total = 0;

        DB::disableQueryLog();

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $batch[] = [
                'dosage_form' => trim($row[0] ?? ''),
                'brand_name' => trim($row[1] ?? ''),
                'generic_name' => trim($row[2] ?? ''),
                'strength' => trim($row[3] ?? ''),
                'manufacturer' => trim($row[4] ?? ''),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= $batchSize) {
                DB::table('medicines')->upsert(
                    $batch,
                    ['brand_name', 'strength', 'dosage_form'],
                    ['generic_name', 'manufacturer', 'updated_at']
                );
                $total += count($batch);
                $batch = [];
                $this->output->write('.');
            }
        }

        if (count($batch) > 0) {
            DB::table('medicines')->upsert(
                $batch,
                ['brand_name', 'strength', 'dosage_form'],
                ['generic_name', 'manufacturer', 'updated_at']
            );
            $total += count($batch);
        }

        fclose($handle);
        $this->newLine();
        $this->info("Successfully imported {$total} medicines!");

        return self::SUCCESS;
    }
}
