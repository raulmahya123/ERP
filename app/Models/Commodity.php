<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Commodity extends Model
{
    use HasUuids;

    protected $table = 'commodities';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['code', 'name'];

    // Daftar enum yang sama persis dengan kolom DB
    public const CODES = ['Batubara','Nikel','Emas'];

    public static function codeOptions(): array
    {
        // ['Batubara'=>'Batubara', ...] untuk select option
        return array_combine(self::CODES, self::CODES);
    }
}
