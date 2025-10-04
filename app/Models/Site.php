<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Site extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'sites';
    protected $fillable = ['code', 'name'];

    // Laravel akan pakai UUID
    public $incrementing = false;
    protected $keyType = 'string';

    public function configs()
    {
        return $this->hasMany(SiteConfig::class);
    }

    // Relasi ke user default_site_id
    public function users()
    {
        return $this->hasMany(User::class, 'default_site_id');
    }
}
