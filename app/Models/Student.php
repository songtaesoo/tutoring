<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'students';

    public $timestamps = true;

    public function getCreatedAtAttribute($value){
        $utc = Carbon::parse($value)->timezone('UTC');

        return date_format($utc, 'Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($value){
        $utc = Carbon::parse($value)->timezone('UTC');

        return date_format($utc, 'Y-m-d H:i:s');
    }

    public function user(){
        return $this->belongsTo('App\Models\User');
    }

    public function tutorings(){
        return $this->hasMany('App\Models\Tutoring');
    }

    public function courseTickets(){
        return $this->hasMany('App\Models\CourseTicket');
    }
}
