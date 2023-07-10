<?php

namespace App\Http\Controllers;

use App\Models\Tutoring;
use App\Models\Tutor;
use App\Models\CourseTicket;
use App\Models\TutoringMaterial;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\TutoringService;

use DB;
use Auth;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class TutoringController extends Controller
{
    protected $tutoringService;

    public function __construct(TutoringService $tutoringService)
    {
        $this->tutoringService = $tutoringService;
    }

    public function tutoringStart(){
        //수업 시작
        //보유한 수강권으로 튜터를 선택하여 수업 요청
        //튜터 수락 시 수업 시작
        $request = request();
        $inputs = $request->input();
        $user = Auth::guard('api')->user();

        $validator = Validator::make($inputs, [
            'ticket_id' => ['required', 'exists:course_tickets,id'],
            'tutor_id' => ['required', 'exists:tutors,id']
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
            $courseTicket = CourseTicket::find($inputs['ticket_id']);

            $checkResult = $this->tutoringService->checkCourseTicket($courseTicket, $user);

            if(!$checkResult['success']){
                return $checkResult;
            }

            //튜터 확인
            $tutor = Tutor::with('language', 'type')->where('id', $inputs['tutor_id'])->first();

            $checkResult = $this->tutoringService->checkTutor($courseTicket, $tutor);

            if(!$checkResult['success']){
                return $checkResult;
            }

            //중복 가능성 확인
            $checkResult = $this->tutoringService->checkDuplicateTutoring($courseTicket, $tutor, $user);

            if(!$checkResult['success']){
                return $checkResult;
            }

            //수업 시작
            $tutoring = new Tutoring();

            $tutoring->student_id = $user->id;
            $tutoring->tutor_id = $tutor->id;
            $tutoring->ticket_id = $courseTicket->id;
            $tutoring->status = 'pending';      //수업 요청 상태: 튜터가 수락하면 processing
            $tutoring->started_at = null;       //튜터가 수업 수락 시 시작시간 update
            $tutoring->ended_at = null;         //수업 종료 시 종료시간 update

            $result = $tutoring->save();

            if(!$result){
                throw new \Exception('수업 시작 중 오류가 발생하였습니다.');
            }

            //튜터 상태변경
            $updateResult = $this->tutoringService->changeTutorStatus($tutor, 'inClass');

            if(!$updateResult['success']){
                throw new \Exception($updateResult['message']);
            }

            $tutoringData = Tutoring::find($tutoring->id);

            //학생에게 수업시작 전 이메일 전송
            $transferResult = $this->tutoringService->sendEmail($user, $tutoringData, 'tutoring-start-result');

            if(!$transferResult['success']){
                throw new \Exception($transferResult['message']);
            }

            //튜터에게 수업시작 준비 이메일 전송
            $transferResult = $this->tutoringService->sendEmail($tutor->user, $tutoringData, 'tutoring-start-request');

            if(!$transferResult['success']){
                throw new \Exception($transferResult['message']);
            }

            //튜터에게 PUSH 앱 알림
            $transferResult = $this->tutoringService->sendFCM($tutor->user, $tutoringData, 'tutoring-start-request');

            if(!$transferResult['success']){
                throw new \Exception($transferResult['message']);
            }

            $result = ['success' => true];

            DB::commit();

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
        //튜터가 수업종료
        //수업종료되면 수업 상태 변경 / 튜터 상태 변경
        $request = request();
        $inputs = $request->input();
        $tutor = Auth::guard('api')->user();

        $validator = Validator::make($inputs, [
            'tutoring_id' => ['required', 'exists:tutorings,id'],
            'video' => ['nullable', 'mimes:mp4', 'max:10240'],
            'voice' => ['nullable', 'mimes:mp3,wav', 'max:10240'],
            'chat' => ['nullable', 'mimes:txt', 'max:10240']
        ], [
            'tutoring_id' => '올바른 수업 정보가 아닙니다.',
            'video' => '녹화 파일의 형식이 맞지 않습니다.',
            'voice' => '녹음 파일의 형식이 맞지 않습니다.',
            'chat' => '채팅내역 파일의 형식이 맞지 않습니다.'
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
                throw new \Exception('올바른 수업이 아닙니다.');
            }

            //수업 상태변경
            $updateResult = $this->tutoringService->changeTutoringStatus($tutoring, 'completed');

            if(!$updateResult['success']){
                throw new \Exception($updateResult['message']);
            }

            //튜터 상태변경
            $updateResult = $this->tutoringService->changeTutorStatus($tutor, 'active');

            if(!$updateResult['success']){
                throw new \Exception($updateResult['message']);
            }

            //수업 종료 후 각 수업종류에서 생성된 파일 이메일 전송
            $studentUser = $tutoring->student->user;
            $course = $tutoring->ticket->course;

            $uploadResult = null;

            if ($request->hasFile('video')) {
                $uploadResult = Cloudinary::uploadFile($request->file('video')->getRealPath());
            }

            if ($request->hasFile('voice')) {
                $uploadResult = Cloudinary::uploadFile($request->file('voice')->getRealPath());
            }

            if ($request->hasFile('chat')) {
                $uploadResult = Cloudinary::uploadFile($request->file('chat')->getRealPath());
            }

            $material = null;

            if(!is_null($uploadResult)){
                //수업 내용 저장
                $material = new TutoringMaterial();

                $material->tutoring_id = $tutoring->id;
                $material->url = $uploadResult->getSecurePath();

                $result = $material->save();

                if(!$result){
                    throw new \Exception('수업 내용 저장 중 오류가 발생하였습니다.');
                }
            }

            if(!is_null($material)){
                //수업결과 이메일 전송
                $data = [
                    'tutoring' => $tutoring,
                    'material' => $material
                ];

                //학생에게 수업결과 이메일 전송
                $transferResult = $this->tutoringService->sendEmail($studentUser, $data, 'tutoring-send-materials');

                if(!$transferResult['success']){
                    throw new \Exception($transferResult['message']);
                }
            }else{
                //수업결과가 이메일로 전달되지 않았을 경우 담당부서에게 메세지 전달
                $message = [
                    '[TUTORING 알림]',
                    '수업 결과 미전송 알림',
                    '',
                    '회원정보: '.$tutoring->student->name.'(ID:'.$studentUser->id.')',
                    '강의정보: '.$course->name.'(ID:'.$course->id.')',
                    '강의구분: '.$course->type->value,
                    '',
                    'via '.env('APP_NAME', 'TUTORING').'_'.env('APP_ENV', 'unknown')
                ];

                //학생에게 수업결과 이메일 전송
                $transferResult = $this->tutoringService->sendTelegram($message);

                if(!$transferResult['success']){
                    throw new \Exception($transferResult['message']);
                }
            }

            DB::commit();

            $result = ['success' => true];

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
