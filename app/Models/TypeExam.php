<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeExam extends Model
{
    use HasFactory;
    protected $table = "type_exams";
    protected $primaryKey = "id";
    public $fillable = [
        'name','description'
    ];
}
