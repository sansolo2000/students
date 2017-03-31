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
    	
    	
   
    	if ($_POST['formulario']=='/'){
    		Session::put('origen', '1');
    	}
    	if ($_POST['formulario']=='admin'){
    		Session::put('origen', '2');
    	}
    	 
    	$registros = persona::join('asignaciones', 'personas.per_rut', '=', 'asignaciones.per_rut')
				    	->join('roles', 'roles.rol_codigo', '=', 'asignaciones.rol_codigo')
				    	->where('personas.per_rut', '=', $rut['numero']);
		$existe= $registros->count();		    	
		if ($existe!=0) {
			$existe = $registros->where('roles.rol_codigo', '=', $_POST['selectRol'])->count();
			if ($existe!=0) {
				$registro = $registros->first();
				if($registro->per_activo == 1){				
		  	    	if (Hash::check($_POST['inputPassword'], $registro->per_password))
			    	{
			    		Auth::loginUsingId($rut['numero'], true);
			    		$persona = persona::find($rut['numero']);
			    		$persona->per_cantidad_intento = 1;
			    		$persona->save();
			    		 return redirect()->route('main');
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
			    		if ($_POST['formulario']=='/'){
			    			return redirect()->route('/');
			    		}
			    		if ($_POST['formulario']=='admin'){
			    			return redirect()->route('admin');
			    		}
			    	}
				}
				else{
					Session::put('error_session', 'Intentos');
					if ($_POST['formulario']=='/'){
						return redirect()->route('/');
					}
					if ($_POST['formulario']=='admin'){
						return redirect()->route('admin');
					}
				}
			}
			else {
				Session::put('error_session', 'Rol');
				if ($_POST['formulario']=='/'){
					return redirect()->route('/');
				}
				if ($_POST['formulario']=='admin'){
					return redirect()->route('admin');
				}
			}
		}
    	else {
    		Session::put('error_session', 'NoExiste');
    		if ($_POST['formulario']=='/'){
				return redirect()->route('/');
			}
			if ($_POST['formulario']=='admin'){
				return redirect()->route('admin');
			}
    	}
    }    

    public function getLogout()
    {
    	Auth::logout();
    	$value = Session::get('origen');
        if ($value == 1){
    		return redirect()->route('/');
    	}
        if ($value == 2){
    		return redirect()->route('admin');
    	}
    }    
    
}
