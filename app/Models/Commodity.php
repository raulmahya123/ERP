<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Commodity extends Model
{
    use HasUuids;

    protected $table = 'commodities';

    // primary key pakai uuid string, non-increment
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
    ];

    protected $casts = [
        'id' => 'string',
    ];
    
    public function configs()
    {
        return $this->hasMany(SiteConfig::class);
    }
}
