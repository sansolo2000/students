<?php

namespace App\Http\Controllers\Auth;

use App\models\persona;
use App\models\roles;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Contracts\Auth\Authenticatable;
use Session;
use App\helpers\util;
use DB;
use Illuminate\Support\Facades\Hash;
use Auth;
use App\models\rol;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/main';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware($this->guestMiddleware(), ['except' => 'logout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }
    
    /**
     * Show the application login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLogin(){
    	return view('auth.login');
    }
    

    public function authenticate()
    {
    	$rut = util::format_rut($_POST['inputRut']);
    	$registros = persona::join('asignaciones', 'personas.per_rut', '=', 'asignaciones.per_rut')
				    	->join('roles', 'roles.rol_codigo', '=', 'asignaciones.rol_codigo')
				    	->where('personas.per_rut', '=', $rut['numero']);
		$existe= $registros->count();		    	
		if ($existe!=0) {
			$registro = $registros->first();
  	    	if (Hash::check($_POST['inputPassword'], $registro->per_password))
	    	{
	    		if($registro->per_activo == 1){
	    			$persona = persona::find($rut['numero']);
	    			$rol = rol::join('asignaciones', 'roles.rol_codigo', '=', 'asignaciones.rol_codigo')
	    						->where('asignaciones.per_rut', '=', $persona->per_rut)
	    						->first();
					Auth::loginUsingId($rut['numero'], true);
					$persona->per_cantidad_intento = 1;
					$persona->save();
	    			if ($rol->rol_nombre == 'Apoderado'){
	    				return redirect()->route('ver_notas.index');
	    			}
	    			else {
	    				return redirect()->route('main');
	    			}
	    		}
	   			if ($registro->per_activo == 3){
	   				Auth::loginUsingId($rut['numero'], true);
	   					return redirect()->route('cambiopassword');
	   			}
	   			if ($registro->per_activo == 0){
	   				 Session::put('error_session', 'Intentos');
   					return redirect()->route('/');
	   			}
	    	}
	    	else{
	    		$persona = persona::find($rut['numero']);
	    		if ($persona->per_cantidad_intento < util::intentos()){
		    		$persona->per_cantidad_intento = $persona->per_cantidad_intento + 1;
		    		$persona->save();
	    		}
	    		else {
	    			$persona->per_cantidad_intento = $persona->per_cantidad_intento + 1;
	    			$persona->per_activo = 0;
	    			$persona->save();
	    		}
	    		Session::put('error_session', 'Password');
    			return redirect()->route('/');
			}
		}
    	else {
    		Session::put('error_session', 'NoExiste');
				return redirect()->route('/');
    	}
    }    

    public function getLogout()
    {
    	Auth::logout();
   		return redirect()->route('/');
    }    
    
}
