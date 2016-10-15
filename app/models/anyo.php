<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class anyo extends Model
{
	protected $table = 'anyos';
	protected $primaryKey = 'any_codigo';
	protected $fillable = ['any_codigo', 'any_numero', 'any_activo', 'updated_at', 'created_at'];
	protected $guarded	= ['any_codigo'];
}
