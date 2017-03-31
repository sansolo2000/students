<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Session;

use App\Http\Requests;
use App\helpers\util;
use App\models\rol;
use Auth;
use App\Http\Controllers\Controller;
use View;

class IndexController extends Controller
{
	function Root() {
		$error = '';
		$value = Session::get('error_session');
		Session::forget('error_session');
		$error = '<script type="text/javascript">';
		$error .= 'BootstrapDialog.alert({';
		switch ($value){
			case 'Password':
				$error .= '		title: "Error",';
				$error .= '    	message: "El usuario y password proporcionado no son correctos",';
				break;
			case 'Rol':
				$error .= '		title: "Error",';
				$error .= '    	message: "El usuario no tiene el rol seleccionado",';
				break;
			case 'NoExiste':
				$error .= '		title: "Error",';
				$error .= '    	message: "El usuario no tiene el rol seleccionado",';
				break;
			case 'Intentos':
				$error .= '		title: "Error",';
				$error .= '    	message: "El usuario esta bloqueado, super&oacute; la cantidad de intentos",';
				break;
			case 'email':
				$error .= '		title: "Informaci&oacute;n",';
				$error .= '    	message: "Su nueva contrase&ntilde;a ha sido enviada al correo registrado",';
				break;
			default:
				$error = '';
				break;
		}
		if ($error != ''){
			$error .= '		type: BootstrapDialog.TYPE_WARNING,';
			$error .= '		closable: true,';
			$error .= '		draggable: true,';
			$error .= '		buttonLabel: "Volver"';
			$error .= '});';
			$error .= '</script>';
		}
		Auth::logout();
		$arrRol = 	rol::where('rol_admin', '<>', '1')
					->where('rol_activo', '=', '1')
					->orderBy('rol_orden', 'ASC')
					->lists('rol_nombre', 'rol_codigo');
		$arrRol = util::array_indice($arrRol, -1);
		return View::make('index', array('formulario' => '/', 'varRol' => $arrRol, 'Error' => $error));
	}

	function Admin() {
		$error = '';
		$value = Session::get('error_session');
		Session::forget('error_session');
		$error = '<script type="text/javascript">';
		$error .= 'BootstrapDialog.alert({';
		$error .= '		title: "Error",';
		switch ($value){
			case 'Password':
				$error .= '		title: "Error",';
				$error .= '    	message: "El usuario y password proporcionado no son correctos",';
				break;
			case 'Rol':
				$error .= '		title: "Error",';
				$error .= '    	message: "El usuario no tiene el rol seleccionado",';
				break;
			case 'NoExiste':
				$error .= '		title: "Error",';
				$error .= '    	message: "El usuario no tiene el rol seleccionado",';
				break;
			case 'Intentos':
				$error .= '		title: "Error",';
				$error .= '    	message: "El usuario esta bloqueado, super&oacute; la cantidad de intentos",';
				break;
			case 'email':
				$error .= '		title: "Informaci&oacute;n",';
				$error .= '    	message: "Su nueva contrase&ntilde;a ha sido enviada al correo registrado",';
				break;
			default:
				$error = '';
				break;
		}
		if ($error != ''){
			$error .= '		type: BootstrapDialog.TYPE_WARNING,';
			$error .= '		closable: true,';
			$error .= '		draggable: true,';
			$error .= '		buttonLabel: "Volver"';
			$error .= '});';
			$error .= '</script>';
		}
		Auth::logout();
		$arrRol = 	rol::where('rol_activo', '=', '1')
					->orderBy('rol_orden', 'ASC')
					->lists('rol_nombre', 'rol_codigo');
		$arrRol = util::array_indice($arrRol,-1);
		return View::make('index', array('formulario' => 'admin', 'varRol' => $arrRol, 'Error' => $error));
	}
	
	function Error_login() {
		$error = '';
		$value = Session::get('error_session');
		Session::forget('error_session');
		$error = '<script type="text/javascript">';
		$error .= 'BootstrapDialog.alert({';
		$error .= '		title: "Error",';
		switch ($value){
			case 'Password':
				$error .= '		title: "Error",';
				$error .= '    	message: "El usuario y password proporcionado no son correctos",';
				break;
			case 'Rol':
				$error .= '		title: "Error",';
				$error .= '    	message: "El usuario no tiene el rol seleccionado",';
				break;
			case 'NoExiste':
				$error .= '		title: "Error",';
				$error .= '    	message: "El usuario no tiene el rol seleccionado",';
				break;
			case 'Intentos':
				$error .= '		title: "Error",';
				$error .= '    	message: "El usuario esta bloqueado, super&oacute; la cantidad de intentos",';
				break;
			case 'email':
				$error .= '		title: "Informaci&oacute;n",';
				$error .= '    	message: "Su nueva contrase&ntilde;a ha sido enviada al correo registrado",';
				break;
			default:
				$error = '';
				break;
		}
		if ($error != ''){
			$error .= '		type: BootstrapDialog.TYPE_WARNING,';
			$error .= '		closable: true,';
			$error .= '		draggable: true,';
			$error .= '		buttonLabel: "Volver"';
			$error .= '});';
			$error .= '</script>';
		}
		Auth::logout();
		$arrRol = 	rol::where('rol_admin', '<>', '1')
					->where('rol_activo', '=', '1')
					->orderBy('rol_orden', 'ASC')
					->lists('rol_nombre', 'rol_codigo');
		$arrRol = util::array_indice($arrRol,-1);
		
		return View::make('index', array('formulario' => '/', 'varRol' => $arrRol, 'Error' => $error));
	}
}
