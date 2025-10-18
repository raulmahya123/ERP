<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payroal extends Model
{
    use HasFactory;

    protected $table = 'payroal';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id','user_id',
        'photo','employee_code','full_name',
        'nik','npwp','bpjs_ketenagakerjaan','bpjs_kesehatan',
        'gender','marital_status','birth_place','birth_date','religion','phone',
        'address_ktp_line1','address_ktp_line2','address_ktp_city','address_ktp_province','address_ktp_postal',
        'address_dom_line1','address_dom_line2','address_dom_city','address_dom_province','address_dom_postal',
        'emergency_name','emergency_relation','emergency_phone',
        'bank_name','bank_branch','bank_account_no','bank_account_name','tax_method','ptkp_code',
        'hire_date','resign_date','employment_status','job_title','grade','level','department','division',
        'site_id','shift_group',
        'base_salary','allowance_meal','allowance_transport','allowance_position','allowance_other',
        'overtime_eligible','payroll_cycle','currency',
        'hired_at','meta',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'hire_date'  => 'date',
        'resign_date'=> 'date',
        'hired_at'   => 'datetime',
        'base_salary'         => 'decimal:2',
        'allowance_meal'      => 'decimal:2',
        'allowance_transport' => 'decimal:2',
        'allowance_position'  => 'decimal:2',
        'allowance_other'     => 'decimal:2',
        'overtime_eligible'   => 'boolean',
        'meta'                => 'array',
    ];

    // Relasi
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }
}
