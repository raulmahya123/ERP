<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Site extends Model
{
    use HasUuids;

    protected $fillable = ['code','name'];

    public function configs() {
        return $this->hasMany(SiteConfig::class);
    }
}
