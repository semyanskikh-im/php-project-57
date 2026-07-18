<?php

namespace Database\Seeders;

use App\Models\Label;
use Illuminate\Database\Seeder;

class LabelSeeder extends Seeder
{
    public function run(): void
    {
        $labels = [
            ['name' => 'вечеринка', 'description' => 'все для праздника'],
            ['name' => 'еда', 'description' => 'выбор еды'],
            ['name' => 'прогулка', 'description' => 'все для активного отдыха'],
        ];

        foreach ($labels as $label) {
            Label::firstOrCreate(['name' => $label['name']], $label);
        }
    }
}
