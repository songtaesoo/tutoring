<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    public function index(){
        //수강과정 목록 API
        $request = request();
        $inputs = $request->input();

        $validator = Validator::make($inputs, [
            'start' => ['numeric', 'min:0'],
            'limit' => ['numeric', 'min:0'],
            'languages' => ['array', 'nullable'],
            'types' => ['array', 'nullable']
        ], [
            'start' => '올바른 페이지를 조회해주세요',
            'limit' => '올바른 페이지를 조회해주세요',
            'languages' => '올바른 수업 언어를 선택해주세요.',
            'types' => '올바른 수업 종류를 선택해주세요.'
        ], []);

        if($validator->fails()){
            $result = ['success' => false, 'message' => $validator->errors()->first()];

            return $result;
        }

        $langCodes = $inputs['languages'] ?? [];
        $types = $inputs['types'] ?? [];

        //데이터 조회
        $courses = Course::with(['type' => function ($q){
                $q->select('id', 'name', 'value');
            }])
            ->with(['language' => function ($q){
                $q->select('id', 'name', 'code');
            }])
            ->where(['is_sale' =>  true])
            ->where('sale_started_at', '<=', Carbon::now()->format('Y-m-d H:i:s'))
            ->where('sale_ended_at', '>=', Carbon::now()->format('Y-m-d H:i:s'));

        if(count($types)){
            $courses = $courses->whereHas('type', function ($query) use ($types){
                $query->whereIn('value', $types);
            });
        }

        if(count($langCodes)){
            $courses = $courses->whereHas('language', function ($query) use ($langCodes){
                $query->whereIn('code', $langCodes);
            });
        }

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
        ->orderBy('sort', 'desc')
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
