<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use View;
use App\helpers\navegador;
use Auth;
use App\helpers\util;


class MainController extends Controller
{
    //
	public function main()
	{
		util::limpiar_cookies();
		$idusuario = Auth::user()->per_rut;		
		$menu = navegador::crear_menu($idusuario);
		return view('main', ['menu' => $menu]);
	}    
	
}
