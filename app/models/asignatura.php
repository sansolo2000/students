<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class asignatura extends Model
{
	protected $table = 'asignaturas';
	protected $primaryKey = 'asg_codigo';
	protected $fillable = ['asg_codigo', 'asg_nombre', 'cur_codigo', 'pro_codigo', 'updated_at', 'created_at'];
	protected $guarded	= ['asi_codigo'];
}
