<?php
// app/Models/SiteConfig.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SiteConfig extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType   = 'string';
    protected $table     = 'site_configs';
    protected $fillable  = ['site_id','commodity_id','params'];
    protected $casts     = ['params' => 'array'];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function commodity()
    {
        return $this->belongsTo(Commodity::class);
    }

    // Helpers (opsional)
    public function param(string $key, $default = null)
    {
        return data_get($this->params, $key, $default);
    }
}
