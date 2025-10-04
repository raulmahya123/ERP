<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SiteConfig extends Model
{
    use HasUuids;

    protected $fillable = ['site_id','commodity_id','params'];

    protected $casts = [
        'params' => 'array',
    ];

    public function site() {
        return $this->belongsTo(Site::class);
    }

    public function commodity() {
        return $this->belongsTo(Commodity::class);
    }
}
