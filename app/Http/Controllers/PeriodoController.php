<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\helpers\util;
use App\helpers\navegador;
use View;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use App\models\Aplicacion;
use Session;
use App\models\periodo;
use DB;

class PeriodoController extends Controller
{
	public $pri_nombre;
	public $Privilegio_modulo = 'Periodos';
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
				$this->pri_nombre = $_POST['pri_nombre'];
				Session::put('search.periodo', array('pri_nombre' => $this->pri_nombre));
			}
			else {
				if (Session::has('search.periodo')){
					$exist = 1;
					$search = Session::get('search.periodo');
					$this->pri_nombre = $search['pri_nombre'];
				}
			}
			$tabla = PeriodoController::arreglo();

			if ($exist == 0){
				$periodos = periodo::orderBy('pri_orden', 'ASC')
				->paginate($this->paginate);
			}
			else{
				$periodos = periodo::select()
				->orderBy('pri_orden', 'ASC');

				if ($this->pri_nombre != ''){
					$periodos = $periodos->where('pri_nombre', 'LIKE', '%'.$this->pri_nombre.'%');
				}
				$periodos = $periodos->paginate($this->paginate);
			}
			$entidad = array('Nombre' => 'Periodos', 'controller' => '/'.util::obtener_url().'periodos', 'pk' => 'pri_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'col' => 4);
			return view('mantenedor.index')
			->with('menu', $menu)
			->with('tablas', $tabla)
			->with('records', $periodos)
			->with('entidad', $entidad)
			->with('privilegio', $privilegio);
		}
	}

	public function show(){
		return redirect()->route('periodos.index');
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
			$periodo = periodo::find($id);
			$periodo->delete();
			$periodo_udp = new periodo();
			$periodo_udp = periodo::select(DB::raw('max(pri_orden) as pri_orden'))->first();
			$periodo = periodo::where('pri_orden', '=', $periodo_udp->pri_orden)->update(['pri_activo' => 1]);
			return redirect()->route('periodos.index');
		}
	}

	public function create()
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$validate = PeriodoController::validador();
		if ($privilegio->mas_add == 0){
			return redirect()->route('logout');
		}
		else{
			$tabla = PeriodoController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'periodos', 'pk' => 'pri_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			return view('mantenedor.add')
			->with('menu', $menu)
			->with('validate', $validate)
			->with('title', 'Ingresar Periodos')
			->with('tablas', $tabla)
			->with('entidad', $entidad);
		}
	}

	public function store()
	{
		$periodo_new = new periodo();
		$periodo = new periodo();
		
		$input = Input::all();
		if (isset($input['pri_activo'])){
			$periodo = periodo::where('pri_activo', '=', 1)->update(['pri_activo' => 0]);
		}
		$periodo_new->pri_nombre 		= $input['pri_nombre'];
		$periodo_new->pri_orden  		= $input['pri_orden'];
		$periodo_new->pri_activo  		= isset($input['pri_activo']) ? 1 : 0;
		$periodo_new->save();
		return redirect()->route('periodos.index');
	}

	public function edit($id)
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$validate = PeriodoController::validador();
		if ($privilegio->mas_edit == 0){
			return redirect()->route('logout');
		}
		else{
			$tabla = PeriodoController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'periodos', 'pk' => 'pri_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			$record = periodo::find($id);
			return view('mantenedor.edit')
			->with('record',$record)
			->with('menu', $menu)
			->with('validate', $validate)
			->with('entidad', $entidad)
			->with('tablas', $tabla)
			->with('title', 'Ingresar Periodos');
		}
	}

	public function update($id)
	{
		// validate
		// read more on validation at http://laravel.com/docs/validation

		// store
		//$periodo = new periodo();
		$periodo_upd = new periodo();
		$periodo_upd = periodo::select();
		$input = Input::all();
		if ($periodo_upd->count()>0 || isset($input['pri_activo'])){
			$periodo = periodo::where('pri_activo', '=', 1)->update(['pri_activo' => 0]);
		}
		$periodo = periodo::find($id);
		$periodo->pri_codigo		= $id;
		$periodo->pri_nombre 		= $input['pri_nombre'];
		$periodo->pri_orden  		= $input['pri_orden'];
		$periodo->pri_activo  		= isset($input['pri_activo']) ? 1 : 0;
		$periodo->save();
		return redirect()->route('periodos.index');
	}

	public function arreglo(){
		$tabla[] = array(	'nombre' 		=> 'Nombre',
				'campo'			=> 'pri_nombre',
				'clase' 		=> 'container col-md-5',
				'validate'		=> '',
				'descripcion'	=> 'Nombre',
				'tipo'			=> 'input',
				'select'		=> 0,
				'value'			=> $this->pri_nombre,
				'filter'		=> 1,
				'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Orden',
				'campo'			=> 'pri_orden',
				'clase' 		=> 'container col-md-3',
				'validate'		=> '',
				'descripcion'	=> 'Orden',
				'value'			=> '',
				'tipo'			=> 'input',
				'select'		=> 0,
				'filter'		=> 0,
				'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Estado',
				'campo'			=> 'pri_activo',
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

							'apl_nombre'		:	{required: true, minlength: 5, maxlength: 50},
							'apl_orden'			:	{required: true, number: true},
							}
					});

				});";
		return $validate;
	}

}
