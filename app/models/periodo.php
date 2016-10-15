<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class periodo extends Model
{
	protected $table = 'periodos';
	protected $primaryKey = 'pri_codigo';
	protected $fillable = ['pri_codigo', 'pri_nombre', 'pri_orden', 'pri_activo', 'updated_at', 'created_at'];
	protected $guarded	= ['pri_codigo'];
}
