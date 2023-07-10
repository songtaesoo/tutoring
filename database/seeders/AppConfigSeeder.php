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
        ],[
            'id' => 2,
            'category' => 'policy',
            'value' => 'true',
            'text' => '선택 이용약관 동의',
        ],[
            'id' => 3,
            'category' => 'notifications',
            'value' => 'always',
            'text' => '마케팅 정보 수신 설정',
        ],[
            'id' => 4,
            'category' => 'notifications',
            'value' => 'day',
            'text' => '할인 쿠폰 등 서비스 혜택 정보 수신 동의',
        ]];

        foreach($items as $item){
            \App\Models\AppConfig::updateOrInsert(['id' => $item['id']], $item);
        }
    }
}
