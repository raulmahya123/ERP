<?php

namespace App\Models\Plant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PlantNotification extends Model
{
    use HasUuids;
    protected $table = 'plant_notifications';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'site_id', 'notification_type', 'title', 'message', 'asset_id',
        'priority', 'is_read', 'read_at', 'recipient_id'
    ];
    protected $casts = [
        'read_at' => 'datetime',
        'is_read' => 'boolean',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class, 'site_id');
    }

    public function asset()
    {
        return $this->belongsTo(\App\Models\Asset::class, 'asset_id');
    }

    public function recipient()
    {
        return $this->belongsTo(\App\Models\User::class, 'recipient_id');
    }
}
