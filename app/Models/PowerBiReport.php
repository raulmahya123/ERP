<?php // app/Models/PowerBiReport.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class PowerBiReport extends Model
{
    use HasUuids, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'show_filter_pane'      => 'bool',
        'show_nav_pane'         => 'bool',
        'show_toolbar'          => 'bool',
        'allow_client_download' => 'bool',
    ];

    // siapa pembuat record
    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    // akses per-user
    public function users() {
        return $this->belongsToMany(User::class, 'powerbi_report_user', 'report_id', 'user_id')
                    ->withTimestamps();
    }

    // akses per-division
    public function divisions() {
        return $this->belongsToMany(Division::class, 'powerbi_report_division', 'report_id', 'division_id')
                    ->withTimestamps();
    }

    // scope: visible untuk user (direct OR via division)
    public function scopeVisibleTo($q, User|string|null $user) {
        if (!$user) return $q->whereRaw('1=0');
        $user = $user instanceof User ? $user : User::find($user);
        if (!$user) return $q->whereRaw('1=0');

        return $q->where(function($w) use ($user) {
            $w->whereHas('users', fn($wu) => $wu->where('users.id', $user->id));
            if ($user->division_id) {
                $w->orWhereHas('divisions', fn($wd) => $wd->where('divisions.id', $user->division_id));
            }
        });
    }

    // helper: build embed url dengan UI flags
    public function embedUrlWithUI(array $ui = []): string {
        $base = $this->embed_url;
        $defaults = [
            'filterPaneEnabled'     => $this->show_filter_pane ? 'true':'false',
            'navContentPaneEnabled' => $this->show_nav_pane ? 'true':'false',
            'toolbarEnabled'        => $this->show_toolbar ? 'true':'false',
            'autoAuth'              => 'true',
        ];
        $params = array_merge($defaults, $this->normalizeBool($ui));
        $sep = str_contains($base,'?') ? '&' : '?';
        return $base.$sep.http_build_query($params);
    }

    protected function normalizeBool(array $a): array {
        return collect($a)->map(fn($v) =>
            (in_array($v,[1,'1',true,'true'],true)) ? 'true' :
            ((in_array($v,[0,'0',false,'false'],true)) ? 'false' : $v)
        )->all();
    }
}
