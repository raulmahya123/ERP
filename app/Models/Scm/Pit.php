<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Pit extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pits';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['site_id', 'code', 'name', 'active', 'extra'];

    protected $casts = [
        'active' => 'boolean',
        'extra'  => 'array',
    ];

    // Relations
    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class);
    }

    // Normalisasi CODE → UPPERCASE tanpa spasi pinggir
    public function setCodeAttribute($val)
    {
        $this->attributes['code'] = strtoupper(trim((string) $val));
    }
}
