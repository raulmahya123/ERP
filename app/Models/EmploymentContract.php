<?php // app/Models/EmploymentContract.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EmploymentContract extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employment_contracts';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'site_id','user_id','type','vendor_name','position',
        'base_salary','start_date','end_date','meta'
    ];
    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date'   => 'date:Y-m-d',
        'base_salary'=> 'decimal:2',
        'meta'       => 'array',
    ];

    public function site(){ return $this->belongsTo(Site::class); }
    public function user(){ return $this->belongsTo(User::class); }
}
