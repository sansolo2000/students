<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use DB;

class asignatura extends Model
{
	protected $table = 'asignaturas';
	protected $primaryKey = 'asg_codigo';
	protected $fillable = ['asg_codigo', 'asg_nombre', 'cur_codigo', 'pro_codigo', 'asg_orden', 'asg_activo'];
	protected $guarded	= ['asg_codigo'];
}
