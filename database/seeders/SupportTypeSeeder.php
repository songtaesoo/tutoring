<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SupportTypeSeeder extends Seeder
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
            'name' => '음성',
            'type' => 'voice',
            'description' => '',
        ],[
            'id' => 2,
            'name' => '화상',
            'type' => 'video',
            'description' => '',
        ],[
            'id' => 3,
            'name' => '채팅',
            'type' => 'chat',
            'description' => '',
        ]];

        foreach($items as $item){
            \App\Models\SupportType::updateOrInsert(['id' => $item['id']], $item);
        }
    }
}
