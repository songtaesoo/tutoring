<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'courses';

    public $timestamps = true;

    public function getCreatedAtAttribute($value){
        $utc = Carbon::parse($value)->timezone('UTC');

        return date_format($utc, 'Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($value){
        $utc = Carbon::parse($value)->timezone('UTC');

        return date_format($utc, 'Y-m-d H:i:s');
    }

    public function language(){
        return $this->belongsTo('App\Models\SupportLanguage');
    }

    public function type(){
        return $this->belongsTo('App\Models\SupportType');
    }

    public function tickets(){
        return $this->hasMany('App\Models\CourseTicket');
    }

    public function payments(){
        return $this->hasMany('App\Models\CoursePayment');
    }
}
