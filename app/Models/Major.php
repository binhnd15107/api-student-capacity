<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Major extends Model
{
    use HasFactory;
    protected $table = 'majors';
    public function contests()
    {
        return $this->HasMany(Contest::class, "major_id")->with('ResultTeam');
    }

}
