<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CourseLanguage extends Model
{
    use HasFactory;

    protected $table = 'course_languages';

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
        return $this->hasOne('App\Models\SupportLanguage');
    }
}
