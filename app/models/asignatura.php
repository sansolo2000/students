<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class asignatura extends Model
{
	protected $table = 'asignaturas';
	protected $primaryKey = 'asg_codigo';
	protected $fillable = ['asg_codigo', 'asg_nombre', 'asg_nivel', 'pro_codigo', 'asg_orden', 'asg_activo', 'updated_at', 'created_at'];
	protected $guarded	= ['asg_codigo'];
}
