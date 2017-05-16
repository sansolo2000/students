<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\helpers\util;
use App\helpers\navegador;
use View;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use App\models\rol;
use Session;

class RolController extends Controller
{
	public $rol_nombre;
	public $Privilegio_modulo = 'roles';
	public $paginate = 10;
	
	
	
	public function index($id = NULL)
	{
		// Menu
		
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
	
		//Privilegios
	
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$validate = RolController::validador();
		
		if ($privilegio->mas_read == 0){
			return redirect()->route('logout');
		}
		else{
			// Descripcion de tabla.
			$exist = 0;
			if (!empty($_POST)){
				$exist = 1;
				$this->rol_nombre = $_POST['rol_nombre'];
				Session::put('search.rol', array('rol_nombre' => $this->rol_nombre));
			}
			else {
				if (Session::has('search.rol')){
					$exist = 1;
					$search = Session::get('search.rol');
					$this->rol_nombre = $search['rol_nombre'];
				}
			}			
			$tabla = RolController::arreglo();
		
			if ($exist == 0){
				$roles = Rol::orderBy('rol_orden', 'ASC')
								->paginate($this->paginate);
			}
			else{
				$roles = Rol::select()
							->orderBy('rol_orden', 'ASC');
				
				if ($this->rol_nombre != ''){
					$roles = $roles->where('roles.rol_nombre', 'LIKE', '%'.$this->rol_nombre.'%');
				}
				$roles = $roles->paginate($this->paginate);
			}
			$entidad = array('Filter' => 1, 'Nombre' => $this->Privilegio_modulo, 'controller' => '/'.util::obtener_url().'roles', 'pk' => 'rol_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'col' => 5);
			$renderactive = true;
			return view('mantenedor.index')
			->with('menu', $menu)
			->with('tablas', $tabla)
			->with('records', $roles)
			->with('entidad', $entidad)
			->with('privilegio', $privilegio)
			->with('renderactive', $renderactive);
		}
	}
	
	public function show(){
		return redirect()->route('roles.index');
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
			//util::print_a('excelente',0);
			$rol = Rol::find($id);
			$rol->delete();
			return redirect()->route('roles.index');
		}
	}
	
	public function create()
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$validate = RolController::validador();
		if ($privilegio->mas_add == 0){
			return redirect()->route('logout');
		}
		else{
			$tabla = RolController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'roles', 'pk' => 'rol_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			return view('mantenedor.add')
					->with('menu', $menu)
					->with('validate', $validate)
					->with('title', 'Ingresar Roles')
					->with('tablas', $tabla)
					->with('entidad', $entidad);
		}
	}
	
	public function store()
	{
		$rol = new rol;
		$input = Input::all();
		$rol->rol_nombre 		= $input['rol_nombre'];
		$rol->rol_orden  		= $input['rol_orden'];
		$rol->rol_admin  		= isset($input['rol_admin']) ? 1 : 0;
		$rol->rol_activo  		= isset($input['rol_activo']) ? 1 : 0;
		$rol->save();
		return redirect()->route('roles.index');
	}
	
	public function edit($id)
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$validate = RolController::validador();
		if ($privilegio->mas_edit == 0){
			return redirect()->route('logout');
		}
		else{
			$tabla = RolController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'roles', 'pk' => 'rol_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			$record = Rol::find($id);
			return view('mantenedor.edit')
			->with('record',$record)
			->with('menu', $menu)
			->with('entidad', $entidad)
			->with('tablas', $tabla)
			->with('validate', $validate)
			->with('title', 'Ingresar Roles');
		}
	}
	
	public function update($id)
	{
		// validate
		// read more on validation at http://laravel.com/docs/validation
	
		// store
		$rol = new rol;
		$rol = Rol::find($id);
		$input = Input::all();
		$rol->rol_nombre = $input['rol_nombre'];
		$rol->rol_orden  = $input['rol_orden'];
		$rol->rol_admin  = isset($input['rol_admin']) ? 1 : 0;
		$rol->rol_activo  = isset($input['rol_activo']) ? 1 : 0;
		$rol->save();
		return redirect()->route('roles.index');
	}
	
	public function arreglo(){
		$tabla[] = array(	'nombre' 		=> 'Nombre',
							'campo'			=> 'rol_nombre',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Nombre',
							'tipo'			=> 'input',
							'select'		=> 0,
							'value'			=> $this->rol_nombre,
							'filter'		=> 1,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Orden',
							'campo'			=> 'rol_orden',
							'clase' 		=> 'container col-md-3',
							'validate'		=> '',
							'descripcion'	=> 'Orden',
							'value'			=> '',
							'tipo'			=> 'input',
							'select'		=> 0,
							'filter'		=> 0,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Administrador',
							'campo'			=> 'rol_admin',
							'clase' 		=> 'container col-md-1',
							'validate'		=> '',
							'descripcion'	=> 'Administrador',
							'value'			=> '',
							'tipo'			=> 'check',
							'select'		=> 0,
							'filter'		=> 0,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Estado',
							'campo'			=> 'rol_activo',
							'clase' 		=> 'container col-md-1',
							'validate'		=> '',
							'descripcion'	=> 'Activo',
							'value'			=> '',
							'tipo'			=> 'check',
							'select'		=> 0,
							'filter'		=> 0,
							'enable'		=> true);
		return $tabla;
		
	}
	
	public function validador(){
		$validate = "
				$().ready(function () {
					$('#myform').validate({ 
						rules: {
							'rol_nombre'	:	{required: true, minlength: 5, maxlength: 50},
							'rol_orden'		:	{required: true, number: true},
			  			}
					});
			
				});";
		return $validate;
	}
}
