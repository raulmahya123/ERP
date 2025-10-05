<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AssetAssignment extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'assigned_at' => 'date',
    ];

    public function asset()      { return $this->belongsTo(Asset::class); }
    public function fromSite()   { return $this->belongsTo(Site::class, 'from_site_id'); }
    public function toSite()     { return $this->belongsTo(Site::class, 'to_site_id'); }
    public function fromUser()   { return $this->belongsTo(User::class, 'from_user_id'); }
    public function toUser()     { return $this->belongsTo(User::class, 'to_user_id'); }
    public function creator()    { return $this->belongsTo(User::class, 'created_by'); }
}

