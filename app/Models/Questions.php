<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Casts\FormatDate;
use App\Casts\FormatImageGet;
use App\Services\Builder\Builder;

class Questions extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'questions';
    protected $primaryKey = "id";
    public $fillable = [
        'content',
        'status',
        'type',
        'rank'
    ];
    protected $casts = [
        'created_at' => FormatDate::class,
        'updated_at' =>  FormatDate::class,
        // 'image' => FormatImageGet::class,
    ];
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }
    public function skills()
    {
        return $this->belongsToMany(Skills::class, 'question_skills', 'question_id', 'skill_id');
    }

    public function answers()
    {
        return $this->hasMany(Answers::class, 'question_id');
    }
}