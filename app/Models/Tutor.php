<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Tutor extends Model
{
    use HasFactory;

    protected $table = 'tutors';

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

    public function language(){
        return $this->hasOne('App\Models\SupportLanguage');
    }

    public function types(){
        return $this->hasMany('App\Models\SupportType');
    }
}
