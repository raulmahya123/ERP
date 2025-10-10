<?php // app/Models/Shift.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Shift extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'shifts';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'site_id','code','name','start_at','end_at','break_minutes','overnight','meta'
    ];
    protected $casts = [
        'start_at' => 'datetime:H:i',
        'end_at'   => 'datetime:H:i',
        'overnight'=> 'bool',
        'meta'     => 'array',
    ];

    public function site(){ return $this->belongsTo(Site::class); }
}
