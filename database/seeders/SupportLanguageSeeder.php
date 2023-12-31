<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SupportLanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [[
            'id' => 1,
            'name' => '영어',
            'code' => 'en',
            'description' => '',
        ],[
            'id' => 2,
            'name' => '중국어',
            'code' => 'cn',
            'description' => '',
        ]];

        foreach($items as $item){
            \App\Models\SupportLanguage::updateOrInsert(['id' => $item['id']], $item);
        }
    }
}
