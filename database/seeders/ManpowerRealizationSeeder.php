<?php // app/Models/ManpowerRealization.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ManpowerRealization extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'manpower_realizations';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'site_id','date','shift_slot','department',
        'actual_headcount','actual_operators','actual_mechanics',
        'actual_helpers','actual_others','production_tonnage',
        'manhours','meta'
    ];
    protected $casts = [
        'date' => 'date:Y-m-d',
        'production_tonnage' => 'decimal:2',
        'manhours' => 'decimal:2',
        'meta' => 'array',
    ];

    public function site(){ return $this->belongsTo(Site::class); }
}
