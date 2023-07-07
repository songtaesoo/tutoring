<?php

namespace App\Http\Controllers;

use App\Models\Tutoring;
use App\Models\Tutor;
use App\Models\CourseTicket;

use Illuminate\Http\Request;

use DB;
use Auth;
use Exception;
use Validator;
use Carbon\Carbon;

class TutoringController extends Controller
{
    public function tutoringStart(){
        //수업 시작
        $request = request();
        $inputs = $request->inputs();
        $user = Auth::guard('api')->user();

        $validator = Validator::make($inputs, [
            'ticket_no' => ['required', 'exist:course_tickets,ticket_no'],
            'tutor_id' => ['required', 'exist:tutors,id']
        ], [
            'ticket_no' => '올바른 수강권이 아닙니다.',
            'tutor_id' => '올바른 튜터가 아닙니다.'
        ], []);

        if($validator->fails()){
            return ['success' => false, 'message' => $validator->errors()->first()];
        }

        try {
            DB::beginTransaction();

            //학생 수강권 보유 확인
            $courseTicket = CourseTicket::where([
                'ticket_no' => $inputs['ticket_no'],
                'student_id' => $user->id,
                'is_sale' => true
            ])->first();

            if(!$courseTicket){
                return ['success' => false, 'message' => '사용 가능한 수강권이 아닙니다.'];
            }

            //학생 수강권 만료 확인
            $now = Carbon::now();

            if($now->greaterThanOrEqualTo($courseTicket->ended_at) || Carbon::parse($courseTicket->started_at)->greaterThanOrEqualTo($now)){
                return ['success' => false, 'message' => '사용할 수 없는 수강권입니다.'];
            }

            //튜터 확인
            $tutor = Tutor::with('language', 'types')->where('id', $inputs['tutor_id'])->first();

            $course = $courseTicket->course ?? null;
            $tutorLang = $tutor->language ?? null;
            $tutorLessonTypes = $tutor->types ?? [];

            //데이터 참조 무결 확인
            if(is_null($course) || is_null($tutorLang) || !$tutorLessonTypes->count()){
                throw new \Exception('오류가 발생하였습니다.');
            }

            //수강권의 구성과 튜터 지원하는 수업 일치 확인
            if($course->language->code != $tutorLang->code){
                throw new \Exception('해당 수강권의 수업 언어를 지원하지 않는 튜터입니다. ');
            }

            if(!$tutorLessonTypes->contains('type', $course->type->type)){
                throw new \Exception('해당 수강권의 수업 방식을 지원하지 않는 튜터입니다.');
            }

            //튜터가 이미 다른 수강생의 수업요청을 받고 있거나 진행하고 있는 경우
            $existTutoring = Tutoring::where([
                'tutor_id' => $tutor->id
            ])
            ->whereIn('status', ['pending', 'processing'])  //pending: 수업요청 | processing: 수업진행중 | completed: 수업종료 | disconnected: 연결종료 | cancelled: 수업취소 | reserved: 예약됨
            ->first();

            if($existTutoring){
                return ['success' => false, 'message' => '튜터가 다른 수강생의 수업을 진행하고 있습니다. 튜터를 선택해주세요.'];
            }

            //금일 예약된 수업이 이미 있는지 확인
            $scheduledTutoring = Tutoring::where([
                'student_id' => $user->id,
                'tutor_id' => $tutor->id,
                'status' => 'reserved'
            ])
            ->whereBetween('started_at', [Carbon::today(), Carbon::today()->addDay()])
            ->first();

            if($scheduledTutoring){
                return ['success' => false, 'message' => '이미 튜터와 예약 수업이 존재합니다.'];
            }

            //수업 시작
            $tutoring = new Tutoring();

            $tutoring->student_id = $user->id;
            $tutoring->tutor_id = $tutor->id;
            $tutoring->course_id = $course->id;
            $tutoring->status = 'pending';      //수업 요청 상태: 튜터가 수락하면 processing
            $tutoring->started_at = null;       //튜터가 수업 수락 시 시작시간 update
            $tutoring->ended_at = null;         //수업 종료 시 종료시간 update

            $result = $tutoring->save();

            if(!$result){
                throw new \Exception('수업 시작 중 오류가 발생하였습니다.');
            }

            $result = ['success' => true];

            DB::commit();

            $tutoringData = Tutoring::find($tutoring->id);

            //수업시작 이메일 전송
            $user->sendEmailNotification($tutoringData);

            return $result;
        } catch (\Throwable $th) {
            $category = __METHOD__;

            logger('['.$category.']: '.date('Y-m-d H:i:s').' - '.$th->getFile().' '.$th->getLine());
            logger('['.$category.']: '.date('Y-m-d H:i:s').' - '.$th->getMessage());
            logger('['.$category.']: '.date('Y-m-d H:i:s').' - '.$th->getTraceAsString());

            $result = ['success' => false, 'message' => '오류가 발생하였습니다. 고객센터로 문의해주세요.'];

            if(env('APP_ENV', 'production') == 'local'){
                $result['debug'] = [
                    'error' => 'throwable',
                    'messasge' => $th->getMessage(),
                    'trace' => $th->getTraceAsString()
                ];
            }

            DB::rollback();

            return $result;
        }
    }

    public function tutoringEnd(){
        //수업 종료
        $request = request();
        $inputs = $request->inputs();
        $tutor = Auth::guard('api')->user();

        $validator = Validator::make($inputs, [
            'tutoring_id' => ['required', 'exist:tutoring,id'],
        ], [
            'tutoring_id' => '올바른 수업 정보가 아닙니다.'
        ], []);

        if($validator->fails()){
            return ['success' => false, 'message' => $validator->errors()->first()];
        }

        try {
            DB::beginTransaction();

            //수업 종료
            $tutoring = Tutoring::where([
                'tutor_id' => $tutor->id,
                'id' => $inputs['tutoring_id']
            ])->first();

            if(!$tutoring || $tutoring->status != 'processing'){
                throw new \Exception('오류가 발생하였습니다.');
            }

            $tutoring->status = 'completed';
            $tutoring->ended_at = Carbon::now();

            $result = $tutoring->save();

            if(!$result){
                throw new \Exception('수업 종료 중 오류가 발생하였습니다.');
            }

            $result = ['success' => true];

            DB::commit();

            //이메일 발송 TODO
            switch($tutoring->course){

            }

            $data = [];

            $user->sendEmailNotification($data);

            return $result;
        } catch (\Throwable $th) {
            $category = __METHOD__;

            logger('['.$category.']: '.date('Y-m-d H:i:s').' - '.$th->getFile().' '.$th->getLine());
            logger('['.$category.']: '.date('Y-m-d H:i:s').' - '.$th->getMessage());
            logger('['.$category.']: '.date('Y-m-d H:i:s').' - '.$th->getTraceAsString());

            $result = ['success' => false, 'message' => '오류가 발생하였습니다. 고객센터로 문의해주세요.'];

            if(env('APP_ENV', 'production') == 'local'){
                $result['debug'] = [
                    'error' => 'throwable',
                    'messasge' => $th->getMessage(),
                    'trace' => $th->getTraceAsString()
                ];
            }

            DB::rollback();

            return $result;
        }
    }
}
