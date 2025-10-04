<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Commodity extends Model
{
    use HasUuids;

    protected $fillable = ['code','name'];

    public function siteConfigs() {
        return $this->hasMany(SiteConfig::class);
    }
}
