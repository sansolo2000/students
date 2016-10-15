<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\models\persona;
use App\models\administrador;
use App\models\alumno;
use App\models\profesor;
use App\models\apoderado;

use App\helpers\util;
use App\helpers\navegador;
use View;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use Session;
use App\models\rol;

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
	public $Privilegio_modulo = 'Usuarios General';
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
				->paginate($this->paginate);
			}
			else{
				$personas = Persona::select()
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
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => '/'.util::obtener_url().'administradores', 'pk' => 'per_rut_adm', 'clase' => 'container col-md-12', 'col' => 7);
			return view('mantenedor.index')
			->with('menu', $menu)
			->with('tablas', $tabla)
			->with('records', $personas)
			->with('entidad', $entidad)
			->with('privilegio', $privilegio);
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
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'modulos', 'pk' => 'rol_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
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
		$modulo = new modulo;
		$input = Input::all();
		$modulo->mod_nombre 		= $input['mod_nombre'];
		$modulo->apl_codigo 		= $input['apl_nombre'];
		$modulo->mod_descripcion 	= $input['mod_descripcion'];
		$modulo->mod_url 			= $input['mod_url'];
		$modulo->mod_orden  		= $input['mod_orden'];
		$modulo->mod_activo  		= isset($input['mod_activo']) ? 1 : 0;
		$modulo->save();
		return redirect()->route('modulos.index');
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
			$this->apl_nombre = aplicacion::where('apl_activo', '=', '1')
			->orderBy('apl_orden', 'ASC')
			->lists('apl_nombre', 'apl_codigo');
			$this->apl_nombre = util::array_indice($this->apl_nombre, -1);
			$this->apl_codigo = 'apl_codigo';
			$tabla = ModuloController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'modulos', 'pk' => 'mod_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			$record = Modulo::find($id);
			return view('mantenedor.edit')
			->with('record',$record)
			->with('menu', $menu)
			->with('entidad', $entidad)
			->with('tablas', $tabla)
			->with('title', 'Ingresar Modulos');
		}
	}

	public function update($id)
	{
		// validate
		// read more on validation at http://laravel.com/docs/validation

		// store
		$modulo = new modulo;
		$modulo = Modulo::find($id);
		$input = Input::all();
		$modulo->mod_nombre = $input['mod_nombre'];
		$modulo->apl_codigo = $input['apl_nombre'];
		$modulo->mod_descripcion = $input['mod_descripcion'];
		$modulo->mod_url = $input['mod_url'];
		$modulo->mod_orden  = $input['mod_orden'];
		$modulo->mod_activo  = isset($input['mod_activo']) ? 1 : 0;
		$modulo->save();
		return redirect()->route('modulos.index');
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
		$tabla[] = array(	'nombre' 		=> 'Password',
							'campo'			=> 'per_password',
							'clase' 		=> 'container col-md-3',
							'validate'		=> '',
							'descripcion'	=> 'Password',
							'value'			=> '',
							'tipo'			=> 'password',
							'select'		=> 0,
							'filter'		=> 3,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Re-Password',
							'campo'			=> 'per_password_re',
							'clase' 		=> 'container col-md-3',
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
				
				});";
		return $validate;
	}
	
}
