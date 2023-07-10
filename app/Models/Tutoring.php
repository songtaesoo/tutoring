<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Tutoring extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tutorings';

    public $timestamps = true;

    public function getCreatedAtAttribute($value){
        $utc = Carbon::parse($value)->timezone('UTC');

        return date_format($utc, 'Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($value){
        $utc = Carbon::parse($value)->timezone('UTC');

        return date_format($utc, 'Y-m-d H:i:s');
    }

    public function student(){
        return $this->belongsTo('App\Models\Student');
    }

    public function tutor(){
        return $this->belongsTo('App\Models\Tutor');
    }

    public function ticket(){
        return $this->belongsTo('App\Models\CourseTicket');
    }
}
