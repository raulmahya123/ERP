<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasUuid;

class Role extends Model
{
    use HasUuid;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key','name','description'];

    // Users in this role
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
