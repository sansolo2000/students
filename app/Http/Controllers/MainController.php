<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use View;
use App\helpers\navegador;
use Auth;


class MainController extends Controller
{
    //
	public function main()
	{
		$idusuario = Auth::user()->per_rut;		
		$menu = navegador::crear_menu($idusuario);
		return view('main', ['menu' => $menu]);
	}    
	
}
