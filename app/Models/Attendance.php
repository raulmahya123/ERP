<?php
// app/Models/Attendance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Attendance extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'attendances';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        // Context
        'site_id','user_id','work_date','shift_id','source',

        // Waktu
        'check_in_at','check_out_at',

        // Lokasi referensi (locations.id)
        'location_in_id','location_out_id',

        // Koordinat aktual
        'gps_in_lat','gps_in_lng','gps_out_lat','gps_out_lng',

        // Geofence & jarak
        'distance_in_m','distance_out_m',
        'outside_geofence_in','outside_geofence_out',

        // Device
        'device_id',

        // Durasi / performa
        'late_minutes','early_leave_minutes','overtime_minutes','work_minutes',

        // Status & flags
        'status','flags',
    ];

    protected $casts = [
        'work_date'             => 'date:Y-m-d',
        'check_in_at'           => 'datetime',
        'check_out_at'          => 'datetime',

        // GPS 7 desimal
        'gps_in_lat'            => 'decimal:7',
        'gps_in_lng'            => 'decimal:7',
        'gps_out_lat'           => 'decimal:7',
        'gps_out_lng'           => 'decimal:7',

        // Geofence
        'distance_in_m'         => 'integer',
        'distance_out_m'        => 'integer',
        'outside_geofence_in'   => 'boolean',
        'outside_geofence_out'  => 'boolean',

        'late_minutes'          => 'integer',
        'early_leave_minutes'   => 'integer',
        'overtime_minutes'      => 'integer',
        'work_minutes'          => 'integer',

        'flags'                 => 'array',
    ];

    // === RELATIONS ===
    public function site()        { return $this->belongsTo(Site::class); }
    public function user()        { return $this->belongsTo(User::class); }
    public function shift()       { return $this->belongsTo(Shift::class); }

    // Relasi lokasi (untuk eager-load locationIn/locationOut di controller)
    public function locationIn()  { return $this->belongsTo(Location::class, 'location_in_id'); }
    public function locationOut() { return $this->belongsTo(Location::class, 'location_out_id'); }

    // Timesheet yang tersinkron (kolom attendance_id di tabel timesheets)
    public function timesheet()   { return $this->hasOne(Timesheet::class, 'attendance_id'); }

    // === ACCESSORS (opsional) ===
    public function getWorkHoursAttribute(): float
    {
        return round(($this->work_minutes ?? 0) / 60, 2);
    }
}
