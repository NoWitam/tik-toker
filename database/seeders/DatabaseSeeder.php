<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Source;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Hubert Golewski',
            'email' => 'wowek31@gmail.com',
            'password' => Hash::make('bielak2000@')
        ]);

        Tag::create([
            'name' => 'Historia'
        ]);

        Tag::create([
            'name' => 'Starożytność'
        ]);

        Source::create([
            'name' => 'Ciekawostki historyczne - starożytność',
            'url' => 'https://ciekawostkihistoryczne.pl/category/epoka/starozytnosc/',
            'eval_next' => 'kod',
            'eval_knowledge_url' => 'kod',
            'eval_knowledge_name' => 'kod',
            'eval_knowledge_content' => 'kod',
            'isActive' => 'true'
        ])->tags()->sync(Tag::all());
    }
}
