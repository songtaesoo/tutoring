<?php

namespace App\Services;

use App\Models\Tutoring;
use App\Models\AppConfig;
use App\Models\Certification;

use Carbon\Carbon;

class TutoringService
{
    public function checkCourseTicket($courseTicket = null, $user = null)
    {
        //수업시작에 필요한 수강권 확인
        if(is_null($courseTicket) || is_null($user)){
            return ['success' => false, 'message' => '올바른 데이터가 아닙니다.'];
        }

        $now = Carbon::now();

        if(!$courseTicket || $courseTicket->student_id != $user->student->id){
            return ['success' => false, 'message' => '사용 가능한 수강권이 아닙니다.'];
        }

        //학생 수강권 만료 확인
        if($now->greaterThanOrEqualTo($courseTicket->ended_at) || Carbon::parse($courseTicket->started_at)->greaterThanOrEqualTo($now)){
            return ['success' => false, 'message' => '수강 가능 기간이 만료된 수강권입니다.'];
        }

        //잔여 수강횟수 확인
        $history = Tutoring::where([
            'student_id' => $user->id,
            'ticket_id' => $courseTicket->id
        ])
        ->whereIn('status', ['completed', 'reserved'])
        ->get();

        if($courseTicket->course->count <= $history->count()){
            return ['success' => false, 'message' => '수강가능 횟수가 초과되었습니다.'];
        }

        //진행중인 수업이 있는 경우 / 정상적으로 종료되지 않은 수업이 있는 경우
        $pendingTutoring = Tutoring::where([
            'student_id' => $user->id,
            'ticket_id' => $courseTicket->id
        ])
        ->whereIn('status', ['pending'])
        ->get();

        if($pendingTutoring->count()){
            return ['success' => false, 'message' => '진행 중인 수업이 이미 존재합니다.'];
        }

        return ['success' => true];
    }

    public function checkTutor($courseTicket = null, $tutor = null){
        //수업시작 전 튜터 확인
        if(is_null($courseTicket) || is_null($tutor)){
            return ['success' => false, 'message' => '올바른 데이터가 아닙니다.'];
        }

        $course = $courseTicket->course ?? null;
        $tutorLang = $tutor->language ?? null;
        $tutorType = $tutor->type ?? null;

        //수강권의 구성과 튜터 지원하는 수업 일치 확인
        if($tutorLang->code != $course->language->code){
            return ['success' => false, 'message' => '해당 수강권의 수업 언어를 지원하지 않는 튜터입니다.'];
        }

        if($tutorType->value != $course->type->value){
            return ['success' => false, 'message' => '해당 수강권의 수업 방식을 지원하지 않는 튜터입니다.'];
        }

        //튜터 온라인 상태 확인
        if($tutor->status != 'active'){
            return ['success' => false, 'message' => '현재 수업 진행이 가능한 튜터를 선택해주세요.'];
        }

        return ['success' => true];
    }

    public function checkDuplicateTutoring($courseTicket = null, $tutor = null, $user = null){
        //중복가능성 배제
        if(is_null($courseTicket) || is_null($tutor) || is_null($user)){
            return ['success' => false, 'message' => '올바른 데이터가 아닙니다.'];
        }

        //튜터가 이미 다른 수강생의 수업요청을 받고 있거나 진행하고 있는 경우
        $existTutoring = Tutoring::where([
            'tutor_id' => $tutor->id
        ])
        ->whereIn('status', ['pending', 'processing'])  //pending: 수업요청 | processing: 수업진행중 | completed: 수업종료 | disconnected: 연결종료 | cancelled: 수업취소 | reserved: 예약수업
        ->first();

        if($existTutoring){
            return ['success' => false, 'message' => '튜터가 다른 수강생의 수업을 진행하고 있습니다. 튜터를 선택해주세요.'];
        }

        //금일 예약된 수업이 이미 있는지 확인
        $scheduledTutoring = Tutoring::where([
            'student_id' => $user->id,
            'tutor_id' => $tutor->id,
            'ticket_id' => $courseTicket->id,
            'status' => 'reserved'
        ])
        ->whereBetween('started_at', [Carbon::today(), Carbon::today()->addDay()])
        ->first();

        if($scheduledTutoring){
            return ['success' => false, 'message' => '이미 튜터와 예약 수업이 존재합니다.'];
        }

        return ['success' => true];
    }

    public function changeTutorStatus($tutor = null, $status = ''){
        //튜터 상태변경
        if(is_null($tutor) || $status == ''){
            return ['success' => false, 'message' => '올바른 데이터가 아닙니다.'];
        }

        if(!in_array($status, ['active', 'deactive', 'inClass'])){
            return ['success' => false, 'message' => '올바른 데이터가 아닙니다.'];
        }

        $tutor->status = $status;

        $tutorStatus = $tutor->save();

        if(!$tutorStatus){
            return ['success' => false, 'message' => '튜터 상태 변경 중 오류가 발생하였습니다.'];
        }

        return ['success' => true];
    }

    public function changeTutoringStatus($tutoring = null, $status = ''){
        //수업 상태변경
        if(is_null($tutoring) || $status == ''){
            return ['success' => false, 'message' => '올바른 데이터가 아닙니다.'];
        }

        if(!in_array($status, ['pending', 'processing', 'completed', 'disconnected', 'cancelled', 'reserved'])){
            return ['success' => false, 'message' => '올바른 데이터가 아닙니다.'];
        }

        $tutoring->status = $status;

        if($status == 'completed'){
            $tutoring->ended_at = Carbon::now();
        }

        $tutorStatus = $tutoring->save();

        if(!$tutorStatus){
            return ['success' => false, 'message' => '수업 상태 변경 중 오류가 발생하였습니다.'];
        }

        return ['success' => true];
    }

    public function sendEmail($user = null, $data = null, $key = ''){
        //메일 전송
        if(is_null($user) || is_null($data) || $key == ''){
            return ['success' => false, 'message' => '올바른 데이터가 아닙니다.'];
        }

        //대상에게 이메일 전송
        $user->sendEmail($key, $data);

        return ['success' => true];
    }

    public function sendTelegram($message = []){
        //텔레그램 전송
        if(count($message)){
            return ['success' => false, 'message' => '올바른 데이터가 아닙니다.'];
        }

        //대상에게 텔레그램 전송
        $notify['text'] = implode( "\n", $message);

        $bot = env('TELEGRAM_ALERT_BOT', '');
        $chatId = env('TELEGRAM_PUSHER_ID', '');

        sendTelegram($bot, $chatId, $notify);

        return ['success' => true];
    }

    public function sendFCM($user = null, $data = null, $key = ''){
        //FCM 전송
        if(is_null($user) || is_null($data) || $key == ''){
            return ['success' => false, 'message' => '올바른 데이터가 아닙니다.'];
        }

        //튜터에게 수업요청알림 PUSH 전송
        $tokenConfig = AppConfig::where([
            'category' => 'certifications',
            'value' => 'aos_device_receive_token',
        ])->first();

        $token = Certification::where([
            'user_id' => $user->id,
            'config_id' => $tokenConfig->id
        ])->orderBy('updated_at', 'desc')->first();

        if($token && false){        //구현코드만 작성
            //FCM PUSH
            fcmSendData([
                'data' => [
                    'action' => 'tutoring-start-request'
                ],
                'token' => $token->value
            ]);

            fcmSendNotification([
                'title' => '수업 요청 알림',
                'content' => '수업요청 이메일을 확인해주시고, 수업을 준비해주세요.',
                'data' => [
                    'action' => 'tutoring-start-request'
                ],
                'token' => $token->value
            ]);
        }

        return ['success' => true];
    }
}

