<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
Use App\models\nivel;
use App\helpers\util;
use App\helpers\navegador;
use View;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use Session;

class NivelController extends Controller
{
	public $niv_nombre;
	public $Privilegio_modulo = 'Niveles';
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
				$this->niv_nombre = $_POST['niv_nombre'];
				Session::put('search.nivel', array('niv_nombre' => $this->niv_nombre));
			}
			else {
				if (Session::has('search.nivel')){
					$exist = 1;
					$search = Session::get('search.nivel');
					$this->niv_nombre = $search['niv_nombre'];
				}
			}
				
			$tabla = NivelController::arreglo();

			if ($exist == 0){
				$niveles = Nivel::orderBy('niv_orden', 'ASC')
				->paginate($this->paginate);
			}
			else{
				$niveles = Nivel::select()
				->orderBy('niv_orden', 'ASC');

				if ($this->niv_nombre != ''){
					$niveles = $niveles->where('niveles.niv_nombre', 'LIKE', '%'.$this->niv_nombre.'%');
				}

				$niveles = $niveles->paginate($this->paginate);
			}
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => '/'.util::obtener_url().'niveles', 'pk' => 'niv_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'col' => 4);
			return view('mantenedor.index')
			->with('menu', $menu)
			->with('tablas', $tabla)
			->with('records', $niveles)
			->with('entidad', $entidad)
			->with('privilegio', $privilegio);
		}
	}

	public function show(){
		return redirect()->route('niveles.index');
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
			$nivel = Nivel::find($id);
			$nivel->delete();
			return redirect()->route('niveles.index');
		}
	}

	public function create()
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$validate = NivelController::validador();
		if ($privilegio->mas_add == 0){
			return redirect()->route('logout');
		}
		else{
			$tabla = NivelController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'niveles', 'pk' => 'niv_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			return view('mantenedor.add')
						->with('menu', $menu)
						->with('validate', $validate)
						->with('title', 'Ingresar Niveles')
						->with('tablas', $tabla)
						->with('entidad', $entidad);
		}
	}

	public function store()
	{
		$nivel = new nivel;
		$input = Input::all();
		$nivel->niv_nombre 		= $input['niv_nombre'];
		$nivel->niv_orden  		= $input['niv_orden'];
		$nivel->niv_activo  	= isset($input['niv_activo']) ? 1 : 0;
		$nivel->save();
		return redirect()->route('niveles.index');
	}

	public function edit($id)
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$validate = NivelController::validador();
		if ($privilegio->mas_edit == 0){
			return redirect()->route('logout');
		}
		else{
			$tabla = NivelController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'niveles', 'pk' => 'niv_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			$record = Nivel::find($id);
			return view('mantenedor.edit')
						->with('record',$record)
						->with('validate', $validate)
						->with('menu', $menu)
						->with('entidad', $entidad)
						->with('tablas', $tabla)
						->with('title', 'Ingresar Regiones');
		}
	}

	public function update($id)
	{
		// validate
		// read more on validation at http://laravel.com/docs/validation

		// store
		$nivel = new nivel;
		$nivel = Nivel::find($id);
		$input = Input::all();
		$nivel->niv_nombre = $input['niv_nombre'];
		$nivel->niv_orden  = $input['niv_orden'];
		$nivel->niv_activo  = isset($input['niv_activo']) ? 1 : 0;
		$nivel->save();
		return redirect()->route('niveles.index');
	}

	public function arreglo(){
		$tabla[] = array(	'nombre' 		=> 'Nombre',
							'campo'			=> 'niv_nombre',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Nombre',
							'tipo'			=> 'input',
							'select'		=> 0,
							'value'			=> $this->niv_nombre,
							'filter'		=> 1,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Orden',
							'campo'			=> 'niv_orden',
							'clase' 		=> 'container col-md-3',
							'validate'		=> '',
							'descripcion'	=> 'Orden',
							'value'			=> '',
							'tipo'			=> 'input',
							'select'		=> 0,
							'filter'		=> 0,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Estado',
							'campo'			=> 'niv_activo',
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
							'niv_nombre'			:	{required: true, minlength: 5, maxlength: 50},
							'niv_orden'				:	{required: true, number: true}
						},
					});
	
				});";
		return $validate;
	}
	
}

