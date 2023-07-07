<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AppConfigSeeder extends Seeder
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
            'category' => 'certifications',
            'value' => 'aos_device_receive_token',
            'text' => '디바이스 메세지 수신용 토큰',
        ]];

        foreach($items as $item){
            \App\Models\AppConfig::updateOrInsert(['id' => $item['id']], $item);
        }
    }
}
