<?php

namespace App\Models\Hcm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeBenefit extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'employee_benefits';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'site_id', 'benefit_code', 'benefit_name', 'benefit_type',
        'amount', 'description', 'is_active'
    ];
    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class, 'site_id');
    }
}
