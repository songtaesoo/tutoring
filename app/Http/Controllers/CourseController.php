<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;

use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    public function index(){
        //수강과정 목록 API
        $request = request();
        $inputs = $request->inputs();

        $validator = Validator::make($inputs, [
            'start' => ['numeric', 'min:0'],
            'limit' => ['numeric', 'min:0'],
            'language' => ['array', 'nullable'],
            'type' => ['array', 'nullable']
        ], [
            'start' => '올바른 페이지를 조회해주세요',
            'limit' => '올바른 페이지를 조회해주세요',
            'language' => '올바른 수업 언어를 선택해주세요.',
            'type' => '올바른 수업 종류를 선택해주세요.'
        ], []);

        if($validator->fails()){
            $result = ['success' => false, 'message' => $validator->errors()->first()];

            return $result;
        }

        $langCodes = $inputs['language'] ?? [];
        $types = $inputs['type'] ?? [];

        //데이터 조회
        $courses = Course::with(['course_types' => function ($query) use ($types){
                $query->with(['type' => function ($q) use ($types){
                    if(count($types)){
                        $q->whereIn('type', $types);
                    }

                    $q->select('name', 'type');
                }]);
            }])->with(['course_languages' => function ($query) use ($langCodes){
                $query->with(['type' => function ($q) use ($langCodes){
                    if(count($langCodes)){
                        $q->whereIn('code', $langCodes);
                    }

                    $q->select('name', 'code');
                }]);
            }])
            ->where(['is_sale' =>  true])
            ->where('sale_started_at', '<=', Carbon::now()->format('Y-m-d H:i:s'))
            ->where('sale_ended_at', '>=', Carbon::now()->format('Y-m-d H:i:s'));

        $total = $courses->count();

        //페이징
        $startPage = $inputs['start'] ?? null;
        $pageLength = $inputs['limit'] ?? null;

        $courses = $courses->when(!is_null($startPage) && !is_null($pageLength), function ($query) use ($startPage, $pageLength){
            if($startPage > 1){
                $skip = ($startPage - 1) * $pageLength;
            }else{
                $skip = 0;
            }

            $query->skip($skip)->take($pageLength);
        })
        ->orderBy('id', 'desc')
        ->orderBy('created_at', 'desc')
        ->get();
        // ->makeHidden([]);

        $data = [
            'list' => $courses,
            'total' => $total
        ];

        $result = ['success' => true, 'data' => $data];

        return $result;
    }
}
