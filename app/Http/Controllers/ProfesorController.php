<?php

namespace App\Http\Controllers;

use Request;
use Auth;
use App\models\persona;
use App\models\rol;
use App\models\asignacion;

use App\models\administrador;
use App\models\alumno;
use App\models\profesor;
use App\models\apoderado;

use App\helpers\util;
use App\helpers\navegador;
use View;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use Session;
use DB;

class ProfesorController extends Controller
{
	public $per_rut_pro;
	public $per_dv;
	public $per_nombre;
	public $per_nombre_segundo;
	public $per_apellido_paterno;
	public $per_apellido_materno;
	public $per_password;
	public $per_email;
	public $rol_codigo;
	public $rol_nombre;
	public $remenber_token;
	public $Privilegio_modulo = 'Profesores';
	public $paginate = 10;

	public function index($id = NULL)
	{
		// Menu

		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);

		//Privilegios

		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		if ($privilegio->mas_read == 0){
			return redirect()->route('logout');
		}
		else{
			// Descripcion de tabla.
			$exist = 0;
			if (!empty($_POST)){
				$exist = 1;
				$this->per_rut_pro 			= $_POST['per_rut_pro'];
				$this->per_nombre 			= $_POST['per_nombre'];
				$this->per_nombre_segundo	= $_POST['per_nombre_segundo'];
				$this->per_apellido_paterno = $_POST['per_apellido_paterno'];
				$this->per_apellido_materno = $_POST['per_apellido_materno'];
				$this->per_email 			= $_POST['per_email'];
				Session::put('search.profesor', array(
						'per_rut_pro' 			=> $this->per_rut_pro,
						'per_nombre' 			=> $this->per_nombre,
						'per_nombre_segundo' 	=> $this->per_nombre_segundo,
						'per_apellido_paterno' 	=> $this->per_apellido_paterno,
						'per_apellido_materno' 	=> $this->per_apellido_materno,
						'per_email' 			=> $this->per_email));
			}
			else{
				if (Session::has('search.profesor')){
					$exist = 1;
					$search = Session::get('search.profesor');
					$this->per_rut_pro 			= $search['per_rut_pro'];
					$this->per_nombre 			= $search['per_nombre'];
					$this->per_nombre_segundo	= $search['per_nombre_segundo'];
					$this->per_apellido_paterno = $search['per_apellido_paterno'];
					$this->per_apellido_materno = $search['per_apellido_materno'];
					$this->per_email 			= $search['per_email'];
				}
			}

			$tabla = ProfesorController::arreglo();

			if ($exist == 0){
				$personas = Persona::join('asignaciones', 'asignaciones.per_rut', '=', 'personas.per_rut')
				->select(DB::raw('personas.per_rut as per_rut_pro, personas.*, profesores.*'))
				->join('roles', 'roles.rol_codigo', '=', 'asignaciones.rol_codigo')
				->join('profesores', 'profesores.per_rut', '=', 'personas.per_rut')
				->where('roles.rol_nombre', '=', 'Profesor')
				->orderBy('personas.per_rut', 'ASC')
				->paginate($this->paginate);
			}
			else{
				$personas = Persona::select(DB::raw('personas.per_rut as per_rut_pro, personas.*, profesores.*'))
				->join('asignaciones', 'asignaciones.per_rut', '=', 'personas.per_rut')
				->join('roles', 'roles.rol_codigo', '=', 'asignaciones.rol_codigo')
				->join('profesores', 'profesores.per_rut', '=', 'personas.per_rut')
				->where('roles.rol_nombre', '=', 'Profesor')
				->orderBy('personas.per_rut', 'ASC');

				if ($this->per_rut_pro != ''){
					$personas = $personas->where('personas.per_rut', 'LIKE', '%'.$this->per_rut_pro.'%');
				}
				if ($this->per_nombre != ''){
					$personas = $personas->where('personas.per_nombre', 'LIKE', '%'.$this->per_nombre.'%');
				}
				if ($this->per_nombre_segundo != ''){
					$personas = $personas->where('personas.per_nombre_segundo', 'LIKE', '%'.$this->per_nombre_segundo.'%');
				}
				if ($this->per_apellido_paterno != ''){
					$personas = $personas->where('personas.per_apellido_paterno', 'LIKE', '%'.$this->per_apellido_paterno.'%');
				}
				if ($this->per_apellido_materno != ''){
					$personas = $personas->where('personas.per_apellido_materno', 'LIKE', '%'.$this->per_apellido_materno.'%');
				}
				if ($this->per_email != ''){
					$personas = $personas->where('personas.per_email', 'LIKE', '%'.$this->per_email.'%');
				}

				$personas = $personas->paginate($this->paginate);
			}
			//util::print_a($personas,0);
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => '/'.util::obtener_url().'profesores', 'pk' => 'per_rut_pro', 'clase' => 'container col-md-12', 'col' => 8);
			return view('mantenedor.index')
			->with('menu', $menu)
			->with('tablas', $tabla)
			->with('records', $personas)
			->with('entidad', $entidad)
			->with('privilegio', $privilegio);
		}
	}

	public function show(){
		return redirect()->route('profesores.index');
	}

	public function destroy($id)
	{
		$idusuario = Auth::user()->per_rut;
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		if ($privilegio->mas_delete == 0){
			return redirect()->route('logout');
		}
		else{
			$profesor = Profesor::where('per_rut', '=', $id);
			$profesor->delete();
			$asignacion = Asignacion::where('per_rut', '=', $id);
			$asignacion->delete();
			$persona = Persona::find($id);
			$persona->delete();
			return redirect()->route('profesores.index');
		}
	}

	public function create()
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$validate = ProfesorController::validador();
		if ($privilegio->mas_add == 0){
			return redirect()->route('logout');
		}
		else{
			$tabla = ProfesorController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'profesores', 'pk' => 'per_rut_pro', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			return view('mantenedor.add')
						->with('menu', $menu)
						->with('title', 'Ingresar Profesor')
						->with('validate', $validate)
						->with('tablas', $tabla)
						->with('entidad', $entidad);
		}
	}

	public function store()
	{
		$input = Input::all();
		$mime = Input::file('pro_logo');
		$persona = new persona;
		$rut = util::format_rut($input['per_rut_pro']);
		
		$cantidad = Persona::where('per_rut', '=', $rut['numero'])->count();
		if ($cantidad == 0){
			$persona->per_rut = $rut['numero'];
			$persona->per_dv = $rut['dv'];
			$persona->per_nombre = $input['per_nombre'];
			$persona->per_nombre_segundo = $input['per_nombre_segundo'];
			$persona->per_apellido_paterno = $input['per_apellido_paterno'];
			$persona->per_apellido_materno = $input['per_apellido_materno'];
			$persona->per_password = Hash::make($input['per_password']);
			$persona->per_email = $input['per_email'];
			$persona->save();
		}
		
		$rol = new rol;
		$rol = Rol::where('rol_nombre', '=', 'Profesor')->first();
		$asignacion = new asignacion;
		$asignacion->rol_codigo = $rol->rol_codigo;
		$asignacion->per_rut = $rut['numero'];
		$asignacion->save();
		
		if (isset($mime)){
			$mime = Input::file('pro_logo')->getMimeType();
			$extension = strtolower(Input::file('pro_logo')->getClientOriginalExtension());
			$fileName = uniqid().'.'.$extension;
			$path = "files_uploaded/firmas";
		
			switch ($mime)
			{
				case "image/jpeg":
				case "image/png":
				case "image/gif":
					if (Request::file('pro_logo')->isValid())
					{
						Request::file('pro_logo')->move($path, $fileName);
						
						$profesor = new profesor;
						$profesor->per_rut 		= $rut['numero'];
						$profesor->pro_activo  	= isset($input['pro_activo']) ? 1 : 0;
						$profesor->pro_horario 	= $input['pro_horario'];
						$profesor->pro_logo		= $fileName;
						$profesor->save();
						}
				default:
					return redirect()->route('profesores.index');
			}
		}
		else{
			$profesor = new profesor;
			$profesor->per_rut 		= $rut['numero'];
			$profesor->pro_activo  	= isset($input['pro_activo']) ? 1 : 0;
			$profesor->pro_horario 	= $input['pro_horario'];
			$profesor->save();
		}
		

		
		return redirect()->route('profesores.index');
	}

	public function edit($id)
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$validate = ProfesorController::validador();
		if ($privilegio->mas_edit == 0){
			return redirect()->route('logout');
		}
		else{
			$tabla = ProfesorController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'profesores', 'pk' => 'per_rut', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			$persona = Persona::select('personas.per_rut', 'personas.per_dv', 'personas.per_nombre', 'per_nombre_segundo', 'personas.per_apellido_paterno', 'personas.per_apellido_materno', 'personas.per_email', DB::raw('"1" as mod_password'), 'profesores.pro_activo', 'profesores.pro_horario')
								->join('asignaciones', 'asignaciones.per_rut', '=', 'personas.per_rut')
								->join('roles', 'roles.rol_codigo', '=', 'asignaciones.rol_codigo')
								->join('profesores', 'profesores.per_rut', '=', 'personas.per_rut')
								->where('roles.rol_nombre', '=', 'Profesor')
								->find($id);
			$rut = util::format_rut($persona->per_rut, $persona->per_dv);
			
			$record = [	'per_rut'				=> $persona->per_rut,
						'per_rut_pro' 			=> $rut['numero'].'-'.$rut['dv'],
						'per_nombre'			=> $persona->per_nombre,
						'per_nombre_segundo'	=> $persona->per_nombre_segundo,
						'per_apellido_paterno'	=> $persona->per_apellido_paterno,
						'per_apellido_materno'	=> $persona->per_apellido_materno,
						'per_email'				=> $persona->per_email,
						'mod_password'			=> 1,
						'pro_activo'			=> $persona->pro_activo,
						'pro_horario'			=> $persona->pro_horario					
			];

			return view('mantenedor.edit')
						->with('record',$record)
						->with('menu', $menu)
						->with('validate', $validate)
						->with('entidad', $entidad)
						->with('tablas', $tabla)
						->with('title', 'Ingresar Profesores');
		}
	}

	public function update($id)
	{
		$input = Input::all();
		$mime = Input::file('pro_logo');
		if (!isset($mime)){
			$persona = new persona;
			$persona = Persona::find($id);
			
			$persona->per_nombre = $input['per_nombre'];
			$persona->per_nombre_segundo = $input['per_nombre_segundo'];
			$persona->per_apellido_paterno = $input['per_apellido_paterno'];
			$persona->per_apellido_materno = $input['per_apellido_materno'];
			if (!isset($input['mod_password'])){
				$persona->per_password = Hash::make($input['per_password']);
			}
			$persona->per_email = $input['per_email'];
			$persona->save();
	
			$profesor = new profesor();
			$profesor = Profesor::where('profesores.per_rut', '=', $id)->first();
			$profesor->pro_activo  	= isset($input['pro_activo']) ? 1 : 0;
			$profesor->pro_horario 	= $input['pro_horario'];
			$profesor->save();
		}
		else{
			$mime = Input::file('pro_logo')->getMimeType();
			$extension = strtolower(Input::file('pro_logo')->getClientOriginalExtension());
			$fileName = uniqid().'.'.$extension;
			$path = "files_uploaded/firmas";
				
			switch ($mime)
			{
				case "image/jpeg":
				case "image/png":
				case "image/gif":
					if (Request::file('pro_logo')->isValid())
					{
						Request::file('pro_logo')->move($path, $fileName);
						$persona = new persona;
						$persona = Persona::find($id);
						$persona->per_nombre = $input['per_nombre'];
						$persona->per_nombre_segundo = $input['per_nombre_segundo'];
						$persona->per_apellido_paterno = $input['per_apellido_paterno'];
						$persona->per_apellido_materno = $input['per_apellido_materno'];
						if (!isset($input['mod_password'])){
							$persona->per_password = Hash::make($input['per_password']);
						}
						$persona->per_email = $input['per_email'];
						$persona->save();
						
						$profesor = new profesor();
						$profesor = Profesor::where('profesores.per_rut', '=', $id)->first();
						$profesor->pro_activo  	= isset($input['pro_activo']) ? 1 : 0;
						$profesor->pro_horario 	= $input['pro_horario'];
						$profesor->pro_logo		= $fileName;
						$profesor->save();
					}
				default:
					return redirect()->route('profesores.index');
			}
		}
		return redirect()->route('profesores.index');
	}
	
	public function arreglo(){
		$rut = util::format_rut($this->per_rut_pro, $this->per_dv);
		$tabla[] = array(	'nombre' 		=> 'Run',
							'campo'			=> 'per_rut_pro',
							'clase' 		=> 'container col-md-5 required',
							'validate'		=> '',
							'descripcion'	=> 'Run',
							'tipo'			=> 'input',
							'select'		=> 0,
							'value'			=> $this->per_rut_pro,
							'filter'		=> 1,
							'enable'		=> false);
		$tabla[] = array(	'nombre' 		=> 'P. Nombre',
							'campo'			=> 'per_nombre',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Nombre',
							'value'			=> $this->per_nombre,
							'tipo'			=> 'input',
							'select'		=> 0,
							'filter'		=> 1,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'S. Nombre',
							'campo'			=> 'per_nombre_segundo',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Segundo',
							'value'			=> $this->per_nombre_segundo,
							'tipo'			=> 'input',
							'select'		=> 0,
							'filter'		=> 3,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'A. Paterno',
							'campo'			=> 'per_apellido_paterno',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Paterno',
							'value'			=> $this->per_apellido_paterno,
							'tipo'			=> 'input',
							'select'		=> 0,
							'filter'		=> 1,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'A. Materno',
							'campo'			=> 'per_apellido_materno',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Materno',
							'value'			=> $this->per_apellido_materno,
							'tipo'			=> 'input',
							'select'		=> 0,
							'filter'		=> 1,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'E-Mail',
							'campo'			=> 'per_email',
							'clase' 		=> 'container col-md-8',
							'validate'		=> '',
							'descripcion'	=> 'E-Mail',
							'value'			=> $this->per_email,
							'tipo'			=> 'input',
							'select'		=> 0,
							'filter'		=> 1,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Estado',
							'campo'			=> 'pro_activo',
							'clase' 		=> 'container col-md-1',
							'validate'		=> '',
							'descripcion'	=> 'Activo',
							'value'			=> '',
							'tipo'			=> 'check',
							'select'		=> 0,
							'filter'		=> 0,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Horario',
							'campo'			=> 'pro_horario',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Horario',
							'value'			=> '',
							'tipo'			=> 'input',
							'select'		=> 0,
							'filter'		=> 3,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Firma',
							'campo'			=> 'pro_logo',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Firma',
							'tipo'			=> 'file',
							'select'		=> 0,
							'value'			=> 0,
							'filter'		=> 0,
							'enable'		=> false);
		$tabla[] = array(	'nombre' 		=> 'Modificar Password',
							'campo'			=> 'mod_password',
							'clase' 		=> 'container col-md-1',
							'validate'		=> '',
							'descripcion'	=> 'Activo',
							'value'			=> 1,
							'tipo'			=> 'check',
							'select'		=> 0,
							'filter'		=> 3,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Password',
							'campo'			=> 'per_password',
							'clase' 		=> 'container col-md-4',
							'validate'		=> '',
							'descripcion'	=> 'Password',
							'value'			=> '',
							'tipo'			=> 'password',
							'select'		=> 0,
							'filter'		=> 3,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Re-Password',
							'campo'			=> 'per_password_re',
							'clase' 		=> 'container col-md-4',
							'validate'		=> '',
							'descripcion'	=> 'Re-password',
							'value'			=> '',
							'tipo'			=> 'password',
							'select'		=> 0,
							'filter'		=> 3,
							'enable'		=> true);
		return $tabla;
	}

	public function validador(){
		$validate = "
				
				$().ready(function () {
					$('#myform').validate({
						rules: {
							'per_rut_pro'				:	{required: true, minlength: 5, maxlength: 50},
							'per_nombre'			:	{required: true, minlength: 2, maxlength: 50},
							'per_apellido_paterno'	:	{required: true, minlength: 2, maxlength: 50},
							'per_apellido_materno'	:	{required: true, minlength: 2, maxlength: 50},
							'per_email'				:	{required: true, email: true,  minlength: 2, maxlength: 50},
							'pro_logo'				:	{extension: 'jpg|png'},
							'per_password'			:	{required: true, minlength: 2, maxlength: 15},
							'per_password_re'		:	{required: true, minlength: 2, maxlength: 15, equalTo : '#per_password'}
						}
					});
					if ($('#mod_password').is(':checked')){
						$('#per_password').prop('disabled', true);
						$('#per_password_re').prop('disabled', true);
					}
					$('#mod_password').change(function(event){
						if ($('#mod_password').is(':checked')){
							$('#per_password').prop('disabled', true);
							$('#per_password_re').prop('disabled', true);
						}
						else {
							$('#per_password').prop('disabled', false);
							$('#per_password_re').prop('disabled', false);
						}
					});
				
				$('#per_email').change(function(event){
					per_rut = per_rut_pro.value;
					if (per_rut.length == 0){
						console.log(per_rut_pro.value);
						$('#per_email').val('');
						$('#per_rut_pro').focus();
						BootstrapDialog.alert({
							title: 'Error',
							message: 'El Run debe ser ingresado primero',
							type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
							closable: true, // <-- Default value is false
							draggable: true, // <-- Default value is false
							buttonLabel: 'Volver', // <-- Default value is 'OK',
						});
					}
					else{
						$.get('/".util::obtener_url()."validar_email/'+event.target.value+'/'+per_rut, function(response,state){
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
								$('#per_email').val('');			
							}
						});
					}
				});
				
				$('#per_rut_pro').change(function(event){
					$.get('../alumno_matriculado/'+event.target.value+'', function(response,state){
						console.log(response[0]);
						if (response.length > 0){
							console.log('ll');
							if (response[0].profesor != null){
								BootstrapDialog.alert({
									title: 'Error',
									message: 'El Rut esta ingresado como Profesor',
									type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
									closable: true, // <-- Default value is false
									draggable: true, // <-- Default value is false
									buttonLabel: 'Volver', // <-- Default value is 'OK',
								});
								$('#per_rut_pro').val('');			
								$('#per_nombre').val('');			
								$('#per_nombre_segundo').val('');			
								$('#per_apellido_paterno').val('');			
								$('#per_apellido_materno').val('');			
								$('#per_email').val('');
								$('#per_rut_pro').focus();			
							}	
							if (response[0].apoderado != null){
								BootstrapDialog.alert({
									title: 'Error',
									message: 'El Rut esta ingresado como Apoderado',
									type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
									closable: true, // <-- Default value is false
									draggable: true, // <-- Default value is false
									buttonLabel: 'Volver', // <-- Default value is 'OK',
								});
								$('#per_rut_pro').val('');			
								$('#per_nombre').val('');			
								$('#per_nombre_segundo').val('');			
								$('#per_apellido_paterno').val('');			
								$('#per_apellido_materno').val('');			
								$('#per_email').val('');
								$('#per_rut_pro').focus();			
							}	
							if (response[0].alumno != null){
								BootstrapDialog.alert({
									title: 'Error',
									message: 'Alumno esta matriculado en el curso '+response[0].curso,
									type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
									closable: true, // <-- Default value is false
									draggable: true, // <-- Default value is false
									buttonLabel: 'Volver', // <-- Default value is 'OK',
								});
								$('#per_rut_pro').val('');			
								$('#per_nombre').val('');			
								$('#per_nombre_segundo').val('');			
								$('#per_apellido_paterno').val('');			
								$('#per_apellido_materno').val('');			
								$('#per_email').val('');
								$('#per_rut_pro').focus();			
							}
						}
						else{
							$('#per_nombre').focus();
						}
				});
			});
				});
				";
		return $validate;
	}
	
	
}
