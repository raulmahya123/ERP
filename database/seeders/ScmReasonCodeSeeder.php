<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ScmReasonCodeSeeder extends Seeder {
  public function run(): void {
    $siteId = session('site_id') ?? DB::table('sites')->value('id'); // sesuaikan
    $rows = [
      ['IDLE','Idle','idle'],
      ['STBY','Standby','standby'],
      ['BD','Breakdown','breakdown'],
      ['NOLOAD','No Load','no_load'],
      ['QUAL','Quality','quality'],
      ['WEATH','Weather','weather'],
      ['QUEUE','Queue','queue'],
    ];
    foreach ($rows as [$code,$name,$cat]) {
      DB::table('scm_reason_codes')->updateOrInsert(
        ['site_id'=>$siteId,'code'=>$code],
        ['id'=>Str::uuid(),'name'=>$name,'category'=>$cat,'is_downtime'=>true,'active'=>true,'created_at'=>now(),'updated_at'=>now()]
      );
    }
  }
}
