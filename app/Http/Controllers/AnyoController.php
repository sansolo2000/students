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
use App\models\anyo;
use App\models\curso;
use App\models\asignatura;
use App\models\nivel;

class AnyoController extends Controller
{
	public $any_numero;
	public $estado;
	public $Privilegio_modulo = 'anyos';
	public $paginate = 10;
	public $errores;


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
			$tabla = AnyoController::arreglo();

			if ($exist == 0){
				$periodos = anyo::orderBy('any_numero', 'ASC')
				->paginate($this->paginate);
			}
			$entidad = array('Filter' => 0,  'Nombre' => 'A&ntilde;os Estudiantil', 'controller' => '/'.util::obtener_url().'anyos', 'pk' => 'any_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'col' => 3);
			$renderactive = true;
			return view('mantenedor.index')
							->with('menu', $menu)
							->with('tablas', $tabla)
							->with('records', $periodos)
							->with('entidad', $entidad)
							->with('errores', $this->errores)
							->with('privilegio', $privilegio)
							->with('renderactive', $renderactive);
		}
	}

	public function show(){
		return redirect()->route('anyos.index');
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
			$curso = new curso();
			$curso = curso::where('any_codigo', '=', $id)->count();
			if ($curso > 0){
				$this->errores = 'No se pudo borrar el año, debido a que, existen cursos cargados';	
			}
			else {
				$asignaturas = asignatura::where('any_codigo', '=', $id);
				$asignaturas->delete();
				
				$periodos = periodo::where('any_codigo', '=', $id);
				$periodos->delete();
				
				$niveles = nivel::where('any_codigo', '=', $id);
				$niveles->delete();
				
				$anyo = anyo::find($id);
				$anyo->delete();
				
				$anyo_udp = new anyo();
				$anyo_udp = anyo::select(DB::raw('max(any_numero) as any_numero'))->first();
				$anyo = anyo::where('any_numero', '=', $anyo_udp->any_numero)->update(['any_activo' => 1]);
			}
			return redirect()->route('anyos.index');
		}
	}

	public function create()
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		if ($privilegio->mas_add == 0){
			return redirect()->route('logout');
		}
		else{
			$validate = AnyoController::validador();
			$this->estado = true;
			$tabla = AnyoController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'anyos', 'pk' => 'any_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			return view('mantenedor.add')
						->with('menu', $menu)
						->with('validate', $validate)
						->with('title', 'Ingresar Año estudiantil')
						->with('tablas', $tabla)
						->with('entidad', $entidad);
		}
	}

	public function store()
	{
		$input = Input::all();
		$anyo = new anyo();
		$anyo_upd = new anyo();
		
		$estado = isset($input['any_activo']) ? 1 : 0;
		
		if ($estado == 1){
			$anyo_upd->where('any_activo', '=', 1)->update(['any_activo' => 0]);
		}
		$anyo->any_numero 		= $input['any_numero'];
		$anyo->any_activo  		= $estado;
		$anyo->save();
		return redirect()->route('anyos.index');
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
			$record = Anyo::find($id);
			if ($record->any_activo == 1){
				$this->estado = false;
			}
			else{
				$this->estado = true;
			}
			$validate = AnyoController::validador();
			$tabla = AnyoController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'anyos', 'pk' => 'any_codigo', 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			return view('mantenedor.edit')
						->with('record',$record)
						->with('menu', $menu)
						->with('validate', $validate)
						->with('entidad', $entidad)
						->with('tablas', $tabla)
						->with('title', 'Ingresar A&ntilde;o estudiantil');
		}
	}

	public function update($id)
	{
		$anyos = new anyo();
		$input = Input::all();
		$estado = isset($input['any_activo']) ? 1 : 0;
		
		if ($estado == 1){
			$anyos->where('any_activo', '=', 1)->update(['any_activo' => 0]);
		}
		$anyos = anyo::find($id);
		$anyos->any_numero 		= $input['any_numero'];
		$anyos->any_activo  	= $estado;
		$anyos->save();
		return redirect()->route('anyos.index');
	}

	public function arreglo(){
		$tabla[] = array(	'nombre' 		=> 'Numero',
							'campo'			=> 'any_numero',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Numero',
							'tipo'			=> 'input',
							'select'		=> 0,
							'value'			=> $this->any_numero,
							'filter'		=> 0,
							'enable'		=> true);
		$tabla[] = array(	'nombre' 		=> 'Estado',
							'campo'			=> 'any_activo',
							'clase' 		=> 'container col-md-1',
							'validate'		=> '',
							'descripcion'	=> 'Activo',
							'value'			=> '',
							'tipo'			=> 'check',
							'select'		=> 0,
							'filter'		=> 0,
							'enable'		=> $this->estado);
		return $tabla;

	}
	public function validador(){
		$minyear = date("Y", time())-2;
		$maxyear = date("Y", time())+2;
		$validate = "
			$().ready(function () {
				$('#myform').validate({
					rules: {
						'any_numero'		:	{
							required: true, 
						    range: [".$minyear.", ".$maxyear."]}
						}
				});
				
				
				$('#any_numero').change(function(event){		    		
					$.get('/".util::obtener_url()."anyos_encontrar/'+event.target.value, function(response,state){
						console.log(response);
						if (response == 1){
							BootstrapDialog.alert({
								title: 'Error',
								message: 'El a&ntilde;o ya existe',
								type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
								closable: true, // <-- Default value is false
								draggable: true, // <-- Default value is false
								buttonLabel: 'Volver', // <-- Default value is 'OK',
							});
						}
					});					
				});					
			});";
		return $validate;
	}
	
	public function anyos_encontrar(Request $request, $any_numero){
		$anyo = new anyo();
		$records = anyo::where('any_numero', '=', $any_numero)
							->count();
		if ($request->ajax()){
			return response()->json($records);
		}
		else{
			util::print_a($records,0);
		}
	}

}

		