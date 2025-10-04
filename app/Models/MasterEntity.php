<?php

// app/Models/MasterEntity.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MasterEntity extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'master_entities';
    protected $fillable = ['id','key','label','enabled','sort','schema','icon','color_from','color_to'];
    protected $casts = ['schema'=>'array','enabled'=>'boolean','sort'=>'integer'];

    protected static function booted() {
        static::creating(function($m){
            if (empty($m->id)) $m->id = (string) \Illuminate\Support\Str::uuid();
            $m->key = Str::slug($m->key, '_'); // pakai underscore
        });
    }
}

