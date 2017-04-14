<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Mail;
use App\helpers\util;
use View;
use App\models\persona;
use Illuminate\Support\Facades\Input;
Use App\models\colegio;
use Illuminate\Support\Facades\Hash;
use Session;


class RecordarPasswordController extends Controller
{
    //
    function RecordarPassword(){
		$url = util::obtener_url_fija();
    	return View::make('recordarpassword', array('url' => $url));
    }
    function EnviarCorreo(){
    	$input = Input::all();
    	$colegios = Colegio::select()
					    	->join('comunas', 'colegios.com_codigo', '=', 'comunas.com_codigo')
					    	->join('regiones', 'regiones.reg_codigo', '=', 'comunas.reg_codigo')
					    	->where('colegios.col_activo', '=', 1)
					    	->first();
    	$password = util::generarCodigo(5);
    	$email = $input['inputEMail'];
    	$persona = persona::where('per_email', '=', $email);
    	$exite = $persona->count();
    	$persona = $persona->first();
    	$rut = util::format_rut($persona->per_rut, $persona->per_dv);
    	$rut_apo = substr ($rut['numero'] , 0, strlen($rut['numero'])-3);
    	$rut_apo = $rut_apo.'***';
    	
    	
    	if ($exite == 1){
    		$persona_udp = new persona();
    		$persona_udp = persona::find($persona->per_rut);
    		$persona_udp->per_password = Hash::make($password);
    		$persona_udp->per_cantidad_intento = 0;
    		$persona_udp->per_activo = 3;
    		$persona_udp->save();
   		//guarda el valor de los campos enviados desde el form en un array
			$data = [	'password'	=> $password,
						'run'		=> $rut_apo,
						'colegio'	=> 'Sistema Students - '.$colegios['col_nombre']
			];
		
			$request = ['email' 	=> $persona->per_email,
						'name'		=> util::quitar_tildes($persona->per_nombre).' '.util::quitar_tildes($persona->per_apellido_paterno).' '.util::quitar_tildes($persona->per_apellido_materno),
						'subject' 	=> 'Restablecimiento de password de la cuenta - '.$colegios['col_nombre']];
		//se envia el array y la vista lo recibe en llaves individuales {{ $email }} , {{ $subject }}...
		//return view('emails.message')->with('password', $password);
		//$view = view('emails.message', compact('password'))->render();
		
		
		
			Mail::send('emails.message', $data, function($message) use ($request)
			{
				//remitente
				$message->from($request['email'], $request['name']);
				//asunto
				$message->subject($request['subject']);
				//receptor
				$message->to('sansolo@gmail.com', 'Héctor Sánchez');
			});
    	}
    	Session::put('error_session', 'email');
		return redirect()->route('/');
		
	}
}
