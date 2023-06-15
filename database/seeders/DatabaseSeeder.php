<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\Category::firstOrCreate(['name' => 'Seafood'],[
            'name' => 'Seafood',
            'description' => 'makanan seafood'
        ]);

        $recipe = \App\Models\Recipe::firstOrCreate(['name' => 'nasi 1 piring'],[
            'name' => 'nasi 1 piring',
            'description' => 'nasi 1 piring'
        ]);

        $recipe_2 = \App\Models\Recipe::firstOrCreate(['name' => 'udang 2 sendok'],[
            'name' => 'udang 2 sendok',
            'description' => 'udang 2 sendok'
        ]);

        $recipe_3 = \App\Models\Recipe::firstOrCreate(['name' => 'cumi 2 sendok'],[
            'name' => 'cumi 2 sendok',
            'description' => 'cumi 2 sendok'
        ]);

        $menu = \App\Models\Menu::firstOrCreate(['name' => 'Nasi Goreng Seafood'],[
            'name' => 'Nasi Goreng Seafood',
            'description' => 'Nasi Goreng Seafood',
            'category_id' => 1
        ]);

        $menu->recipes()->sync([$recipe->id, $recipe_2->id, $recipe_3->id]);
    }
}
