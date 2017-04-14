<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\models\persona;
use Auth;
use App\helpers\navegador;
use App\helpers\util;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;



class CambioPasswordController extends Controller
{
    //
	public function index()
	{
		//$idusuario = Auth::user()->per_rut;
		//$persona = persona::where('per_rut', '=', $idusuario)->first();
		//$menu = navegador::crear_menu($idusuario);
		
		$idusuario = Auth::user()->per_rut;
		$persona = persona::where('per_rut', '=', $idusuario)->first();
		if ($persona->per_activo == 3){
			$per_activo = false;
		}
		else{
			$per_activo = true;
		}
		$menu = navegador::crear_menu($idusuario, $per_activo);
		$url = '/students/public/main';
		$validate = $this->validador($persona->email, $persona->per_rut);
		return view('cambiopassword', ['menu' => $menu, 'url' => $url, 'email' => $persona->per_email, 'validate' => $validate]);
		
	}
	
	public function save(){
		$input = Input::all();
		$idusuario = Auth::user()->per_rut;
		$persona = new persona();
		$persona = persona::find($idusuario);
		$persona->per_password = Hash::make($input['inputPassword']);
		$persona->per_activo = 1;
		$persona->per_cantidad_intento = 1;
		$persona->save();
		return redirect()->route('main');
	}
	
	public function validador($email, $per_rut){
		if (empty($email)){
			$validate = "
				$().ready(function () {
					$('#form_login').validate({
						rules: {
							'inputEMail'			:	{required: true, email: true,  minlength: 6, maxlength: 50},
							'inputPassword'			:	{required: true, minlength: 5, maxlength: 15},
							'inputRePassword'		:	{required: true, minlength: 5, maxlength: 15, equalTo : '#inputPassword'}
						}
					});
				$('#inputEMail').change(function(event){	
					$.get('/".util::obtener_url()."validar_email/'+event.target.value+'/".$per_rut."', function(response,state){
						console.log(response);
						if (response > 0){
							console.log(response[0]);
							BootstrapDialog.alert({
								title: 'Error',
								message: 'El E-Mail esta ingresado por otro usuario',
								type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
								closable: true, // <-- Default value is false
								draggable: true, // <-- Default value is false
								buttonLabel: 'Volver', // <-- Default value is 'OK',
							});
							$('#inputEMail').val('');			
						}
					});
				});
		});";
		}
		else{
			$validate = "
				$().ready(function () {
					$('#form_login').validate({
					rules: {
					'inputPassword'			:	{required: true, minlength: 5, maxlength: 15},
					'inputRePassword'		:	{required: true, minlength: 5, maxlength: 15, equalTo : '#inputPassword'}
				}
		});";
		}
		return $validate;
	}
}
