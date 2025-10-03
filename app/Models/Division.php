<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Division extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'name', 'description'];

    public $incrementing = false;   // karena bukan auto-increment
    protected $keyType = 'string'; // UUID berupa string

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
}
