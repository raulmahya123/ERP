<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasUuid;

class Location extends Model
{
    use HasUuid;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'longitude',
        'latitude',
        'years_of_collab',
        'created_by'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function permissions()
    {
        return $this->hasMany(LocationPermission::class);
    }
}
