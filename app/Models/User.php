<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function student(){
        return $this->hasOne('App\Models\Student');
    }

    public function tutor(){
        return $this->hasOne('App\Models\Tutor');
    }

    public function certifications(){
        return $this->hasMany('App\Models\Certification');
    }

    public function sendEmailNotification($type, $data){
        switch($type){
            //각각 메일 템플릿을 통해 전송
            case 'tutoring-start-result':
                break;
            case 'tutoring-send-result-chat':
                break;
            case 'tutoring-send-result-file':
                break;
            case 'tutoring-start-request':
                break;
        }

        return true;
    }
}
