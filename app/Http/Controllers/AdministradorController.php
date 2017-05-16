<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\models\persona;
use App\models\administrador;
use App\models\alumno;
use App\models\profesor;
use App\models\apoderado;
use App\models\asignacion;
use App\models\rol;
use App\helpers\util;
use App\helpers\navegador;
use View;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use Illuminate\Support\Facades\Hash;
use Session;
use DB;

class AdministradorController extends Controller
{
	public $per_rut;
	public $per_dv;
	public $per_nombre;
	public $per_apellido_paterno;
	public $per_apellido_materno;
	public $per_password;
	public $per_email;
	public $rol_codigo;
	public $rol_nombre;
	public $remenber_token;
	public $Privilegio_modulo = 'administradores';
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
				$this->per_rut 				= $_POST['per_rut_adm'];
				$this->per_nombre 			= $_POST['per_nombre'];
				$this->per_apellido_paterno = $_POST['per_apellido_paterno'];
				$this->per_apellido_materno = $_POST['per_apellido_materno'];
				$this->per_email 			= $_POST['per_email'];
				Session::put('search.usuario', array(	
						'per_rut_adm' 			=> $this->per_rut,
						'per_nombre' 			=> $this->per_nombre,
						'per_apellido_paterno' 	=> $this->per_apellido_paterno,
						'per_apellido_materno' 	=> $this->per_apellido_materno,
						'per_email' 			=> $this->per_email));
			}
			else{
				if (Session::has('search.usuario')){
					$exist = 1;
					$search = Session::get('search.usuario');
					$this->per_rut 				= $search['per_rut_adm'];
					$this->per_nombre 			= $search['per_nombre'];
					$this->per_apellido_paterno = $search['per_apellido_paterno'];
					$this->per_apellido_materno = $search['per_apellido_materno'];
					$this->per_email 			= $search['per_email'];
				}
			}
				
			$tabla = AdministradorController::arreglo($id);

			if ($exist == 0){
				$personas = Persona::join('asignaciones', 'asignaciones.per_rut', '=', 'personas.per_rut')
				->join('roles', 'roles.rol_codigo', '=', 'asignaciones.rol_codigo')
				->wherein('roles.rol_nombre', array('Administrador', 'Direccion'))
				->orderBy('personas.per_rut', 'ASC')
				->select(DB::raw('personas.per_rut as per_rut_adm, per_dv, per_nombre, per_apellido_materno, per_apellido_paterno, per_email, rol_nombre'))
				->paginate($this->paginate);
			}
			else{
				$personas = Persona::select(DB::raw('personas.per_rut as per_rut_adm, per_dv, per_nombre, per_apellido_materno, per_apellido_paterno, per_email, rol_nombre'))
							->join('asignaciones', 'asignaciones.per_rut', '=', 'personas.per_rut')
							->join('roles', 'roles.rol_codigo', '=', 'asignaciones.rol_codigo')
							->wherein('roles.rol_nombre', array('Administrador', 'Direccion'))
							->orderBy('personas.per_rut', 'ASC');
				
				if ($this->per_rut != ''){
					$personas = $personas->where('personas.per_rut', 'LIKE', '%'.$this->per_rut.'%');
				}
				if ($this->per_nombre != ''){
					$personas = $personas->where('personas.per_nombre', 'LIKE', '%'.$this->per_nombre.'%');
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
			$renderactive = true;
			$entidad = array('Filter' => 1,  'Nombre' => $this->Privilegio_modulo, 'controller' => '/'.util::obtener_url().'administradores', 'pk' => 'per_rut_adm', 'clase' => 'container col-md-12', 'col' => 7);
			return view('mantenedor.index')
			->with('menu', $menu)
			->with('tablas', $tabla)
			->with('records', $personas)
			->with('entidad', $entidad)
			->with('privilegio', $privilegio)
			->with('renderactive', $renderactive);
		}
	}

	public function show(){
		return redirect()->route('administrador.index');
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
			$modulo = Modulo::find($id);
			$modulo->delete();
			return redirect()->route('modulos.index');
		}
	}

	public function create()
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$validate = AdministradorController::validador();
		if ($privilegio->mas_add == 0){
			return redirect()->route('logout');
		}
		else{
			$this->rol_nombre = 	rol::where('rol_activo', '=', '1')
									->wherein('roles.rol_nombre', array('Administrador', 'Direccion'))
									->orderBy('rol_orden', 'ASC')
									->lists('rol_nombre', 'rol_codigo');
			$this->rol_nombre = util::array_indice($this->rol_nombre, -1);
			$tabla = AdministradorController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'administradores', 'pk' => 'per_rut_adm', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			return view('mantenedor.add')
						->with('menu', $menu)
						->with('validate', $validate)
						->with('title', 'Ingresar Usuarios')
						->with('tablas', $tabla)
						->with('entidad', $entidad);
		}
	}

	public function store()
	{
		$input = Input::all();
		$persona = new persona;
		$rut = util::format_rut($input['per_rut_adm']);
		
		$cantidad = Persona::where('per_rut', '=', $rut['numero'])->count();
		if ($cantidad == 0){
			$persona->per_rut = $rut['numero'];
			$persona->per_dv = $rut['dv'];
			$persona->per_nombre = $input['per_nombre'];
			$persona->per_apellido_paterno = $input['per_apellido_paterno'];
			$persona->per_apellido_materno = $input['per_apellido_materno'];
			$persona->per_password = Hash::make($input['per_password']);
			$persona->per_email = $input['per_email'];
			$persona->save();
		}
		else{
			$persona = new persona;
			$persona = Persona::find($rut['numero']);
				
			$persona->per_nombre = $input['per_nombre'];
			$persona->per_apellido_paterno = $input['per_apellido_paterno'];
			$persona->per_apellido_materno = $input['per_apellido_materno'];
			$persona->per_password = Hash::make($input['per_password']);
			$persona->per_email = $input['per_email'];
			$persona->save();
		}
		$cantidad = asignacion::where('per_rut', '=', $rut['numero'])->count();
		
		if ($cantidad == 0){
			$asignacion = new asignacion;
			$asignacion->rol_codigo = $input['rol_nombre'];
			$asignacion->per_rut = $rut['numero'];
			$asignacion->save();
		}
		return redirect()->route('administradores.index');
	}

	public function edit($id)
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		if ($privilegio->mas_edit == 0){
			return redirect()->route('logout');
		}
		else{
			$this->rol_nombre = 	rol::where('rol_activo', '=', '1')
											->wherein('roles.rol_nombre', array('Administrador', 'Direccion'))
											->orderBy('rol_orden', 'ASC')
											->lists('rol_nombre', 'rol_codigo');
			$this->rol_nombre = util::array_indice($this->rol_nombre, -1);
			$this->rol_codigo = 'rol_codigo';
			$tabla = AdministradorController::arreglo();
			$validate = AdministradorController::validador();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'administradores', 'pk' => 'per_rut_adm', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			//$record = Persona::find($id);
			$persona = Persona::join('asignaciones', 'personas.per_rut', '=', 'asignaciones.per_rut')
						->join('roles', 'asignaciones.rol_codigo', '=', 'roles.rol_codigo')
						->select('personas.per_rut', 'personas.per_dv', 'personas.per_nombre', 'personas.per_apellido_paterno', 'personas.per_apellido_materno', 'personas.per_email', 'roles.rol_codigo')
						->where('personas.per_rut', '=', $id)
						->first();
			$rut = util::format_rut($persona->per_rut, $persona->per_dv);
				
			$record = [	'per_rut'			=> $persona->per_rut,
					'per_rut_adm' 			=> $rut['numero'].'-'.$rut['dv'],
					'per_nombre'			=> $persona->per_nombre,
					'per_nombre_segundo'	=> $persona->per_nombre_segundo,
					'per_apellido_paterno'	=> $persona->per_apellido_paterno,
					'per_apellido_materno'	=> $persona->per_apellido_materno,
					'per_email'				=> $persona->per_email,
					'rol_codigo'			=> $persona->rol_codigo,
					'mod_password'			=> 1
			];
						
						
			
			//util::print_a($record, 0);
						//$validate = '';
			return view('mantenedor.edit')
						->with('record', $record)
						->with('menu', $menu)
						->with('validate', $validate)
						->with('entidad', $entidad)
						->with('tablas', $tabla)
						->with('title', 'Ingresar Usuarios Generales');
		}
	}

	public function update($id)
	{
		// validate
		// read more on validation at http://laravel.com/docs/validation

		// store
		$input = Input::all();
		$rut = util::format_rut($id);
		$persona = new persona;
		$persona = Persona::find($rut['numero']);
		$persona->per_nombre = $input['per_nombre'];
		$persona->per_apellido_paterno = $input['per_apellido_paterno'];
		$persona->per_apellido_materno = $input['per_apellido_materno'];
		if (!isset($input['mod_password'])){
			$persona->per_password = Hash::make($input['per_password']);
		}
		$persona->per_email = $input['per_email'];
		$persona->save();
		return redirect()->route('administradores.index');
	}

	public function getModulo(Request $request, $apl_codigo, $rol_codigo){
		if ($request->ajax()){
			$datos = rol::join('modulos_asignados', 'roles.rol_codigo', '=', 'modulos_asignados.rol_codigo')
			->where('roles.rol_codigo', '=', $rol_codigo)
			->select('modulos_asignados.mod_codigo')
			->get();
			$records = Modulo::where('apl_codigo', '=', $apl_codigo)
			->where('mod_nombre', '!=', '**---**')
			->wherenotin('mod_codigo', $datos)
			->select('mod_codigo', 'mod_nombre')
			->get();
			return response()->json($records);
		}
	}
	public function arreglo(){
		$tabla[] = array(	'nombre' 		=> 'Run',
							'campo'			=> 'per_rut_adm',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Run',
							'tipo'			=> 'input',
							'select'		=> 0,
							'value'			=> $this->per_rut,
							'filter'		=> 1,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Nombre',
							'campo'			=> 'per_nombre',
							'clase' 		=> 'container col-md-8',
							'validate'		=> '',
							'descripcion'	=> 'Nombre',
							'value'			=> $this->per_nombre,
							'tipo'			=> 'input',
							'select'		=> 0,
							'filter'		=> 1,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'A. Paterno',
							'campo'			=> 'per_apellido_paterno',
							'clase' 		=> 'container col-md-8',
							'validate'		=> '',
							'descripcion'	=> 'Paterno',
							'value'			=> $this->per_apellido_paterno,
							'tipo'			=> 'input',
							'select'		=> 0,
							'filter'		=> 1,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'A. Materno',
							'campo'			=> 'per_apellido_materno',
							'clase' 		=> 'container col-md-8',
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
		$tabla[] = array(	'nombre' 		=> 'Roles',
							'campo'			=> 'rol_nombre',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Roles',
							'value'			=> $this->rol_codigo,
							'tipo'			=> 'select',
							'select'		=> $this->rol_nombre,
							'filter'		=> 1,
							'enable'		=> true);
		return $tabla;

	}

	public function validador(){
		$validate = "
				$().ready(function () {
					$('#myform').validate({
						rules: {
							'per_rut_adm'				:	{required: true, minlength: 5, maxlength: 50},
							'per_nombre'			:	{required: true, minlength: 2, maxlength: 50},
							'per_apellido_paterno'	:	{required: true, minlength: 2, maxlength: 50},
							'per_apellido_materno'	:	{required: true, minlength: 2, maxlength: 50},
							'per_email'				:	{required: true, minlength: 2, maxlength: 50},
							'per_password'			:	{required: true, minlength: 2, maxlength: 15},
							'per_password_re'		:	{required: true, minlength: 2, maxlength: 15, equalTo : '#per_password'},
							'rol_nombre'			:	{required: true, min:1}
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
				$('#per_rut_adm').change(function(event){
					$.get('../alumno_administrador/'+event.target.value+'', function(response,state){
						console.log(response[0]);
						if (response.length > 0){
							console.log('ll');
							if (response[0].apoderado != null){
								BootstrapDialog.alert({
									title: 'Error',
									message: 'El Rut esta ingresado como Apoderado',
									type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
									closable: true, // <-- Default value is false
									draggable: true, // <-- Default value is false
									buttonLabel: 'Volver', // <-- Default value is 'OK',
								});
								$('#per_rut_adm').val('');			
								$('#per_nombre').val('');			
								$('#per_nombre_segundo').val('');			
								$('#per_apellido_paterno').val('');			
								$('#per_apellido_materno').val('');			
								$('#per_email').val('');
								$('#per_rut_alu').focus();			
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
								$('#per_rut_adm').val('');			
								$('#per_nombre').val('');			
								$('#per_nombre_segundo').val('');			
								$('#per_apellido_paterno').val('');			
								$('#per_apellido_materno').val('');			
								$('#per_email').val('');
								$('#per_rut_alu').focus();			
							}
						}
						else{
							$('#per_nombre').focus();
						}
					});
				});
				$('#per_rut_adm').Rut({
					  on_error: function(){ 
						  BootstrapDialog.alert({
					            title: 'Error',
					            message: 'El RUN ingresado es incorrecto!!',
					            type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
					            closable: true, // <-- Default value is false
					            draggable: true, // <-- Default value is false
					            buttonLabel: 'Volver', // <-- Default value is 'OK',
					            //callback: function(result) {
					                // result will be true if button was click, while it will be false if users close the dialog directly.
					                //alert('Result is: ' + result);
					            //}
					        });
							console.log('prueba');
						}
					});
				
				$('#per_email').change(function(event){
					per_rut = per_rut_adm.value;
					if (per_rut.length == 0){
						console.log(per_rut_pro.value);
						$('#per_email').val('');
						$('#per_rut_adm').focus();
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
			});
								";
		return $validate;
	}
	
}
