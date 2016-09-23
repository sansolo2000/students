<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class profesor extends Model
{
    //
	protected $table = 'profesores';
	protected $primaryKey = 'pro_codigo';
	protected $fillable = ['pro_codigo', 'per_rut', 'pro_activo'];
	protected $guarded	= ['pro_codigo'];
}
