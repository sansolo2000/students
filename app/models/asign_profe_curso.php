<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class asign_profe_curso extends Model
{
    //
	protected $table = 'asign_profe_curso';
	protected $primaryKey = 'apc_codigo';
	protected $fillable = ['apc_codigo', 'cur_codigo', 'asg_codigo', 'pro_codigo', 'updated_at', 'created_at'];
	protected $guarded	= ['apc_codigo'];

}
