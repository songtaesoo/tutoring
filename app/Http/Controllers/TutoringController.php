<?php

namespace App\Http\Controllers;

use App\Models\Tutoring;
use App\Models\Tutor;
use App\Models\CourseTicket;
use App\Models\TutoringMaterial;
use App\Models\AppConfig;
use App\Models\Certification;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use DB;
use Auth;
use Carbon\Carbon;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Symfony\Component\Console\Input\Input;

class TutoringController extends Controller
{
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

            $now = Carbon::now();

            //학생 수강권 보유 확인
            $courseTicket = CourseTicket::find($inputs['ticket_id']);

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

            //튜터 확인
            $tutor = Tutor::with('language', 'type')->where('id', $inputs['tutor_id'])->first();

            $course = $courseTicket->course ?? null;
            $tutorLang = $tutor->language ?? null;
            $tutorType = $tutor->type ?? null;

            //수강권의 구성과 튜터 지원하는 수업 일치 확인
            if($tutorLang->code != $course->language->code){
                throw new \Exception('해당 수강권의 수업 언어를 지원하지 않는 튜터입니다. ');
            }

            if($tutorType->value != $course->type->value){
                throw new \Exception('해당 수강권의 수업 방식을 지원하지 않는 튜터입니다.');
            }

            //튜터 온라인 상태 확인
            if($tutor->status != 'active'){
                throw new \Exception('현재 수업 진행이 가능한 튜터를 선택해주세요.');
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

            $tutor->status = 'inClass';

            $tutorStatus = $tutor->save();

            if(!$tutorStatus){
                throw new \Exception('튜터 상태 변경 중 오류가 발생하였습니다.');
            }

            $tutoringData = Tutoring::find($tutoring->id);

            //수업시작 이메일 전송
            $user->sendEmail('tutoring-start-result', $tutoringData);

            //튜터에게 이메일 전송
            $tutorUser = $tutor->user;
            $tutorUser->sendEmail('tutoring-start-request', $tutoringData);

            //튜터에게 수업요청알림 PUSH 전송
            $tokenConfig = AppConfig::where([
                'category' => 'certifications',
                'value' => 'aos_device_receive_token',
            ])->first();

            $token = Certification::where([
                'user_id' => $tutorUser->id,
                'config_id' => $tokenConfig->id
            ])->orderBy('updated_at', 'desc')->first();

            if($token){        //구현코드만 작성
                //FCM PUSH
                fcmSendData([
                    'data' => [
                        'action' => 'tutoring-start-request'
                    ],
                    'token' => $token->value
                ]);

                fcmSendNotification([
                    'title' => '수업 요청 알림',
                    'content' => '['.$course->name.'] 수업을 준비해주세요.',
                    'data' => [
                        'action' => 'tutoring-start-request'
                    ],
                    'token' => $token->value
                ]);
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

            $tutoring->status = 'completed';    //수업종료
            $tutoring->ended_at = Carbon::now();

            $result = $tutoring->save();

            if(!$result){
                throw new \Exception('수업 종료 중 오류가 발생하였습니다.');
            }

            //튜터 상태 변경
            $tutor = Tutor::find($tutor->id);

            $tutor->status = 'active';

            $tutorStatusResult = $tutor->save();

            if(!$tutorStatusResult){
                throw new \Exception('튜터 상태 변경 중 오류가 발생하였습니다.');
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

                $studentUser->sendEmail('tutoring-send-materials', $data);
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

                $notify['text'] = implode( "\n", $message);

                $bot = env('TELEGRAM_ALERT_BOT', '');
                $chatId = env('TELEGRAM_PUSHER_ID', '');

                sendTelegram($bot, $chatId, $notify);
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
