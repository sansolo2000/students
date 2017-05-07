<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\models\persona;
use App\models\rol;
use App\models\asignacion;
use App\models\curso;

use App\models\administrador;
use App\models\alumno;
use App\models\profesor;
use App\models\apoderado;
use App\models\region;
use Maatwebsite\Excel\Facades\Excel;

use App\helpers\util;
use App\helpers\navegador;
use View;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use Illuminate\Support\Facades\Hash;
use Session;
use DB;
use Redirect;
use App\models\apoderado_alumno;
use App\models\calificacion;


class AlumnoController extends Controller
{
	public $alu_numero;
	public $per_rut;
	public $per_dv;
	public $per_nombre;
	public $per_apellido_paterno;
	public $per_apellido_materno;
	public $per_password;
	public $per_email;
	public $cur_codigo;
	public $cur_nombre;
	public $remenber_token;
	public $Privilegio_modulo = 'Alumnos';
	public $paginate = 10;
	public $errores;

	public function index($id = NULL)
	{
		// Menu

		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);

		//Privilegios

		$errores = '';
		if (Session::has('search.alumno_errores')){
			$search = Session::get('search.alumno_errores');
			$this->errores = $search['errores'];
			Session::forget('search.alumno_errores');
		}
		
		
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$this->cur_codigo = -1;
		if ($privilegio->mas_read == 0){
			return redirect()->route('logout');
		}
		else{
			// Descripcion de tabla.
			$exist = 0;
			if (!empty($_POST)){
				if (!(empty($_POST['alu_numero']) && empty($_POST['per_rutbak']) && empty($_POST['per_nombre']) 
						&& empty($_POST['per_apellido_paterno']) && empty($_POST['per_apellido_materno'])
						 && empty($_POST['per_email']))){
					$this->alu_numero 			= $_POST['alu_numero'];
					$this->per_rut 				= $_POST['per_rutbak'];
					$this->per_nombre 			= $_POST['per_nombre'];
					$this->per_apellido_paterno = $_POST['per_apellido_paterno'];
					$this->per_apellido_materno = $_POST['per_apellido_materno'];
					$this->per_email 			= $_POST['per_email'];
					Session::put('search.alumno_persona', array(
						'alu_numero' 			=> $this->alu_numero,
						'per_rut' 				=> $this->per_rut,
						'per_nombre' 			=> $this->per_nombre,
						'per_apellido_paterno' 	=> $this->per_apellido_paterno,
						'per_apellido_materno' 	=> $this->per_apellido_materno,
						'per_email' 			=> $this->per_email));
					if (Session::has('search.alumno_curso')){
						$exist = 1;
						$search = Session::get('search.alumno_curso');
						$this->cur_codigo = $search['cur_codigo'];
					}
					$exist = 2;
				}
				else {
					if (Session::has('search.alumno_persona')){
						$exist = 2;
						$search = Session::get('search.alumno_persona');
						$this->per_rut 				= $search['per_rut'];
						$this->per_nombre 			= $search['per_nombre'];
						$this->per_apellido_paterno = $search['per_apellido_paterno'];
						$this->per_apellido_materno = $search['per_apellido_materno'];
						$this->per_email 			= $search['per_email'];
					}
					if (Session::has('search.alumno_curso')){
						$exist = 1;
						$search = Session::get('search.alumno_curso');
						$this->cur_codigo			= $search['cur_codigo'];
					}
				}				
				if (!empty($_POST['cur_nombre'])){
					$this->cur_codigo = $_POST['cur_nombre'];
					Session::put('search.alumno_curso', array(
						'cur_codigo' 			=> $this->cur_codigo));
					$exist = 1;
					if (Session::has('search.alumno_persona')){
						$exist = 2;
						$search = Session::get('search.alumno_persona');
						$this->per_rut 				= $search['per_rut'];
						$this->per_nombre 			= $search['per_nombre'];
						$this->per_apellido_paterno = $search['per_apellido_paterno'];
						$this->per_apellido_materno = $search['per_apellido_materno'];
						$this->per_email 			= $search['per_email'];
					}
				}
			}
			else{
				if (Session::has('search.alumno_persona')){
					$exist = 2;
					$search = Session::get('search.alumno_persona');
					$this->per_rut 				= $search['per_rut'];
					$this->per_nombre 			= $search['per_nombre'];
					$this->per_apellido_paterno = $search['per_apellido_paterno'];
					$this->per_apellido_materno = $search['per_apellido_materno'];
					$this->per_email 			= $search['per_email'];
				}
				if (Session::has('search.alumno_curso')){
					$exist = 1;
					$search = Session::get('search.alumno_curso');
					$this->cur_codigo			= $search['cur_codigo'];
				}
			}

			$tabla = AlumnoController::arreglo();
			if ($exist == 0){
				$personas = Persona::join('asignaciones', 'asignaciones.per_rut', '=', 'personas.per_rut')
									->join('roles', 'roles.rol_codigo', '=', 'asignaciones.rol_codigo')
									->join('alumnos', 'alumnos.per_rut', '=', 'personas.per_rut')
									->where('roles.rol_nombre', '=', 'Alumno')
									->where('alumnos.cur_codigo', '=', -1)
									->where('alumnos.alu_activo', '=', 1)
									->orderBy('alumnos.alu_numero', 'ASC')
									->orderBy('personas.per_apellido_paterno', 'ASC')
									->select()
									->paginate($this->paginate);
				
			}
			if ($exist == 1){
				$personas = Persona::join('asignaciones', 'asignaciones.per_rut', '=', 'personas.per_rut')
									->join('roles', 'roles.rol_codigo', '=', 'asignaciones.rol_codigo')
									->join('alumnos', 'alumnos.per_rut', '=', 'personas.per_rut')
									->where('roles.rol_nombre', '=', 'Alumno')
									->where('alumnos.cur_codigo', '=', $this->cur_codigo)
									->where('alumnos.alu_activo', '=', 1)
									->orderBy('alumnos.alu_numero', 'ASC')
									->orderBy('personas.per_apellido_paterno', 'ASC')
									->select()
									->paginate($this->paginate);
			}
			if ($exist == 2){
				$personas = Persona::join('asignaciones', 'asignaciones.per_rut', '=', 'personas.per_rut')
									->join('roles', 'roles.rol_codigo', '=', 'asignaciones.rol_codigo')
									->join('alumnos', 'alumnos.per_rut', '=', 'personas.per_rut')
									->where('roles.rol_nombre', '=', 'Alumno')
									->where('alumnos.cur_codigo', '=', $this->cur_codigo)
									->where('alumnos.alu_activo', '=', 1)
									->orderBy('alumnos.alu_numero', 'ASC')
									->orderBy('personas.per_apellido_paterno', 'ASC')
									->select();
				if ($this->per_rut != ''){
					$personas = $personas->where('personas.per_rut', 'LIKE', '%'.$this->per_rut.'%');
				}
				if ($this->alu_numero != ''){
					$personas = $personas->where('alumnos.alu_numero', 'LIKE', '%'.$this->alu_numero.'%');
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
			$cargar = "";
			if ($this->cur_codigo != -1){
				$cantidad = curso::join('alumnos', 'cursos.cur_codigo', '=', 'alumnos.cur_codigo')
									->join('calificaciones', 'alumnos.alu_codigo', '=', 'calificaciones.alu_codigo')
									->where('alumnos.cur_codigo', '=', $this->cur_codigo)
									->where('alumnos.alu_activo', '=', 1)
									->count();
				if ($cantidad > 0){
					$cargar = "disabled='disabled'";
				}
			}					
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => '/'.util::obtener_url().'alumnos', 'pk' => 'per_rut', 'clase' => 'container col-md-12', 'col' => 7);
			return view('mantenedor.index_alumno')
						->with('menu', $menu)
						->with('tablas', $tabla)
						->with('cur_codigo', $this->cur_codigo)
						->with('records', $personas)
						->with('errores', $this->errores)
						->with('entidad', $entidad)
						->with('privilegio', $privilegio)
						->with('cargar', $cargar);
		}
	}

	public function show(){
		return redirect()->route('alumnos.index');
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
			$rol = new rol();
			$rol = rol::where('rol_nombre', '=', 'Alumno')->first();
			
			$calificacion = new calificacion();
			$calificacion = calificacion::join('alumnos', 'calificaciones.alu_codigo', '=', 'alumnos.alu_codigo')
										->where('alumnos.per_rut', '=', $id);
			if($calificacion->count() == 0){
				$asignacion = new asignacion();
				$asignacion = Asignacion::where('per_rut', '=', $id)->where('rol_codigo', '=', $rol->rol_codigo)->first();
				$alumno = new alumno();
				$alumno = Alumno::where('per_rut', '=', $id)->first();
				
				$apoderado_alumno = new apoderado_alumno();
				$apoderado_alumno = apoderado_alumno::where('alu_codigo', '=', $alumno->alu_codigo)->first();
				
				if (count($asignacion) > 0){
					$asignacion->delete();
				}
					
				if(count($apoderado_alumno) > 0){ 
					$apoderado = new apoderado();
					$apoderado = apoderado::where('apo_codigo', '=', $apoderado_alumno->apo_codigo)->first();
				
					if (count($apoderado_alumno) > 0){
						$apoderado_alumno->delete();
					}
					
					if (count($apoderado) > 0){
						$apoderado->delete();
					}
					
					$persona_apoderado = new persona();
					$persona_apoderado = persona::where('per_rut', '=', $apoderado->per_rut)->first();
					if (count($persona_apoderado) > 0){
						$persona_apoderado->delete();
					}
				
					
				}			
				if (count($alumno) > 0){
					$alumno->delete();
				}
	
				$persona_alumno = new persona();
				$persona_alumno = persona::where('per_rut', '=', $alumno->per_rut)->first();
				if (count($persona_alumno) > 0){
					$persona_alumno->delete();
				}
			}
			else {
				$alumno = new alumno();
				$alumno = Alumno::where('per_rut', '=', $id);
				$alumno->alu_activo = 0;
				$alumno->save();
			}
			return redirect()->route('alumnos.index');
		}
	}

	public function create($id)
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		if ($privilegio->mas_add == 0){
			return redirect()->route('logout');
		}
		else{
			$curso = Curso::join('niveles', 'niveles.niv_codigo', '=', 'cursos.niv_codigo')
							->join('profesores', 'profesores.pro_codigo', '=', 'cursos.pro_codigo')
							->join('personas', 'personas.per_rut', '=', 'profesores.per_rut')
							->where('cursos.cur_codigo', '=', $id)
							->select('cursos.cur_codigo', DB::raw('CONCAT(cursos.cur_numero, "&deg;", cursos.cur_letra, " ", niveles.niv_nombre, " - Profesor Jefe: ", personas.per_nombre, " ", personas.per_apellido_paterno) as name'))
							->first();
			$cantidad = Alumno::where('alumnos.cur_codigo', '=', $id)->count() + 1;
			if ($cantidad > 0){
				$enable_numero = ' disabled="disabled"';
			}
			$tabla = AlumnoController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'alumnos', 'pk' => 'per_rut', 'clase' => 'container col-md-10 col-md-offset-1', 'label' => 'container col-md-4');
			return view('mantenedor.add_alumno')
						->with('menu', $menu)
						->with('enable_numero', $enable_numero)
						->with('title', 'Ingresar Alumno')
						->with('curso', $curso)
						->with('numero', $cantidad)
						->with('tablas', $tabla)
						->with('entidad', $entidad);
		}
	}

	public function store()
	{
		$input = Input::all();
		$persona = new persona;
		$rut = util::format_rut($input['per_rut_alu']);

		$cantidad = Persona::where('per_rut', '=', $rut['numero'])->count();
		if ($cantidad == 0){
			$persona->per_rut = $rut['numero'];
			$persona->per_dv = $rut['dv'];
			$persona->per_nombre = $input['per_nombre'];
			$persona->per_nombre_segundo = $input['per_nombre_segundo'];
			$persona->per_apellido_paterno = $input['per_apellido_paterno'];
			$persona->per_apellido_materno = $input['per_apellido_materno'];
			if (isset($input['dat_adicionales'])){
				$persona->per_email = $input['per_email'];
			}
			$persona->save();
		}
		else{
			$persona = Persona::find($rut['numero']);
			$persona->per_nombre = $input['per_nombre'];
			$persona->per_nombre_segundo = $input['per_nombre_segundo'];
			$persona->per_apellido_paterno = $input['per_apellido_paterno'];
			$persona->per_apellido_materno = $input['per_apellido_materno'];
			if (isset($input['dat_adicionales'])){
				$persona->per_email = $input['per_email'];
			}
			$persona->save();
		}
		$rol = new rol;
		$rol = Rol::where('rol_nombre', '=', 'Alumno')->first();
		$cantidad_rol = asignacion::where('rol_codigo', '=', $rol->rol_codigo)->where('per_rut', '=', $rut['numero'])->count();
		if ($cantidad_rol == 0){
			$asignacion = new asignacion;
			$asignacion->rol_codigo = $rol->rol_codigo;
			$asignacion->per_rut = $rut['numero'];
			$asignacion->save();
		}
		
		$cantidad_alumno = alumno::where('alumnos.per_rut', '=', $rut['numero'])->count();
		
		$alumno = new alumno;
		if ($cantidad_alumno == 0){
			$alumno->per_rut = $rut['numero'];
			$alumno->alu_numero = $input['hid_numero'];
			$alumno->cur_codigo = $input['cur_codigo'];
			$alumno->alu_activo = 1;
		}
		else{
			$alumno = alumno::where('alumnos.per_rut', '=', $rut['numero'])->first();
			$alumno->alu_numero = $input['hid_numero'];
			$alumno->cur_codigo = $input['cur_codigo'];
			$alumno->alu_activo = 1;
		}
		$alumno->save();
		return redirect()->route('alumnos.index');
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
			$tabla = AlumnoController::arreglo();
			$alumno = Alumno::find($id);
			$record = Persona::join('alumnos', 'alumnos.per_rut', '=', 'personas.per_rut')
							->join('asignaciones', 'asignaciones.per_rut', '=', 'personas.per_rut')
							->join('roles', 'roles.rol_codigo', '=', 'asignaciones.rol_codigo')
							->join('cursos', 'alumnos.cur_codigo', '=', 'cursos.cur_codigo')
							->join('niveles', 'niveles.niv_codigo', '=', 'cursos.niv_codigo')
							->where('roles.rol_nombre', '=', 'Alumno')
							->select('cursos.cur_codigo',
									'alumnos.alu_numero', 
									'personas.per_rut',
									'personas.per_dv',
									'personas.per_nombre',
									'personas.per_nombre_segundo',
									'personas.per_apellido_paterno',
									'personas.per_apellido_materno',
									'personas.per_email')
							->find($id);
			$curso = Curso::join('niveles', 'niveles.niv_codigo', '=', 'cursos.niv_codigo')
							->join('profesores', 'profesores.pro_codigo', '=', 'cursos.pro_codigo')
							->join('personas', 'personas.per_rut', '=', 'profesores.per_rut')
							->where('cursos.cur_codigo', '=', $record->cur_codigo)
							->select('cursos.cur_codigo', DB::raw('CONCAT(cursos.cur_numero, "&deg;", cursos.cur_letra, " ", niveles.niv_nombre, " - Profesor Jefe: ", personas.per_nombre, " ", personas.per_apellido_paterno) as name'))
							->first();
			$rut = util::format_rut($record->per_rut, $record->per_dv);
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'alumnos', 'pk' => 'per_rut', 'clase' => 'container col-md-10 col-md-offset-1', 'label' => 'container col-md-4');
				
			return view('mantenedor.edit_alumno')
			->with('record',$record)
			->with('menu', $menu)
			->with('rut', $rut['numero'].'-'.$rut['dv'])
			->with('entidad', $entidad)
			->with('tablas', $tabla)
			->with('curso', $curso)
			->with('title', 'Ingresar Profesores');
		}
	}

	public function update($id)
	{
		$input = Input::all();
		$persona = new persona;
		$persona = Persona::find($id);

		$persona->per_nombre = $input['per_nombre'];
		$persona->per_nombre_segundo = $input['per_nombre_segundo'];
		$persona->per_apellido_paterno = $input['per_apellido_paterno'];
		$persona->per_apellido_materno = $input['per_apellido_materno'];
		if (isset($input['dat_adicionales'])){
			$persona->per_email = $input['per_email'];
		}
		$persona->save();

		
		return redirect()->route('alumnos.index');



	}

	public function importar_alumnos($id)
	{
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		if ($privilegio->mas_add == 0){
			return redirect()->route('logout');
		}
		else{
			$curso = Curso::join('niveles', 'niveles.niv_codigo', '=', 'cursos.niv_codigo')
								->join('profesores', 'profesores.pro_codigo', '=', 'cursos.pro_codigo')
								->join('personas', 'personas.per_rut', '=', 'profesores.per_rut')
								->where('cursos.cur_codigo', '=', $id)
								->select('cursos.cur_codigo', DB::raw('CONCAT(cursos.cur_numero, "&deg;", cursos.cur_letra, " ", niveles.niv_nombre, " - Profesor Jefe: ", personas.per_nombre, " ", personas.per_apellido_paterno) as name'))
								->first();
			$tabla = AlumnoController::arreglo();
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => 'alumnos', 'pk' => $id, 'clase' => 'container col-md-6 col-md-offset-3', 'label' => 'container col-md-4');
			return view('mantenedor.importar_alumno')
								->with('menu', $menu)
								->with('title', 'Importar Alumno')
								->with('curso', $curso)
								->with('tablas', $tabla)
								->with('entidad', $entidad);
		}
	}
	
	public function exportar_alumnos($id)
	{
		$curso = Curso::join('niveles', 'niveles.niv_codigo', '=', 'cursos.niv_codigo')
							->join('profesores', 'profesores.pro_codigo', '=', 'cursos.pro_codigo')
							->join('personas', 'personas.per_rut', '=', 'profesores.per_rut')
							->where('cursos.cur_codigo', '=', $id)
							->select('cursos.cur_codigo', DB::raw('CONCAT(cursos.cur_numero, "-", cursos.cur_letra, " ", niveles.niv_nombre) as name'),DB::raw('CONCAT(personas.per_rut, "-", personas.per_dv, " : ",personas.per_nombre, " ", personas.per_nombre_segundo, " ", personas.per_apellido_paterno, " ", personas.per_apellido_materno) as profesor'))
							->first();
		$alumnos = Alumno::join('personas', 'alumnos.per_rut', '=', 'personas.per_rut')
							->select('personas.per_rut', 'alumnos.alu_numero', 'personas.per_dv', 'personas.per_nombre', 'personas.per_nombre_segundo', 'personas.per_apellido_paterno', 'personas.per_apellido_materno', 'personas.per_email')
							->where('alumnos.cur_codigo', '=', $id)
							->where('alumnos.alu_activo', '=', 1)
							->orderBy('alu_numero', 'ASC')
							->get();
		//$nombre = $curso->name;
		if ($alumnos->count()> 0){
			Excel::create($curso->name.' - Alumno', function($excel) use($alumnos, $curso) {
				$excel->sheet($curso->name, function($sheet) use($alumnos, $curso){
					$cantidad = 0;
					foreach ($alumnos as $key => $alumno) {
						$rut = util::format_rut($alumno->per_rut, $alumno->per_dv);
						$data[] = array('Numero'			=> $alumno->alu_numero,
										'Run' 				=> $rut['numero'].'-'.$rut['dv'], 
										'Primer Nombre' 	=> $alumno->per_nombre,
										'Segundo Nombre' 	=> $alumno->per_nombre_segundo,
										'Apellido Paterno' 	=> $alumno->per_apellido_paterno, 
										'Apellido Materno'	=> $alumno->per_apellido_materno,
										'E-Mail'			=> $alumno->per_email
								
						); 
						$cantidad++;
					}
					
					$sheet->row(2, array(
							'','Curso: ', $curso->name
					));						
					$sheet->row(3, array(
							'','Profesor: ', $curso->profesor
					));
					$persona = Persona::join('alumnos', 'personas.per_rut', '=', 'alumnos.per_rut')
										->join('asignaciones', 'personas.per_rut', '=', 'asignaciones.per_rut')
										->where('alumnos.cur_codigo', '=', $curso->cur_codigo)
										->where('alumnos.alu_activo', '=', 1)
										->select(DB::raw('max(alumnos.alu_numero) maximo'))
										->first();
					$numero = $persona->maximo;
					for ($i = $cantidad; $i <= 50; $i++) {
						$sheet->row($i+5, array(
								$numero
						));
						$numero++;
					}
					$sheet->fromArray($data, null, 'A5', false, true);
					$sheet->setBorder('B2:D3', 'thin');
					$sheet->mergeCells('C1:D1');
					$sheet->mergeCells('C2:D2');
					$sheet->setBorder('A5:G55', 'thin');
					$sheet->cells('B2:B3', function($cells) {
						$cells->setBackground('#2fa4e7');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cells('A5:G5', function($cells) {
						$cells->setBackground('#2fa4e7');
						$cells->setFontColor('#ffffff');
					});
					$sheet->setWidth(array(
							'A'     =>  9,
							'B'     =>  15,
							'C'     =>  25,
							'D'     =>  25,
							'E'     =>  25,
							'F'     =>  25,
							'G'     =>  45
							
					));
				});
			})->download('xls');
		}
		else{
			Excel::create($curso->name.' - Alumno', function($excel) use($alumnos, $curso) {
				$excel->sheet($curso->name, function($sheet) use ($curso){
					$data[] = array('Numero'			=> '',
							'Run' 				=> '',
							'Primer Nombre' 	=> '',
							'Segundo Nombre' 	=> '',
							'Apellido Paterno' 	=> '',
							'Apellido Materno'	=> '',
							'E-Mail'			=> ''
		
					);
					$sheet->row(2, array(
							'','Curso: ', $curso->name
					));
					$sheet->row(3, array(
							'','Profesor: ', $curso->profesor
					));
					$persona = Persona::join('alumnos', 'personas.per_rut', '=', 'alumnos.per_rut')
					->join('asignaciones', 'personas.per_rut', '=', 'asignaciones.per_rut')
					->where('alumnos.cur_codigo', '=', $curso->cur_codigo)
					->select(DB::raw('max(alumnos.alu_numero) maximo'))
					->first();
					$numero = $persona->maximo + 1;
					for ($i = $numero; $i <= 50; $i++) {
						$sheet->row($i+5, array(
								$i
						));
					}
					$sheet->fromArray($data, null, 'A5', false, true);
					$sheet->setBorder('B2:D3', 'thin');
					$sheet->mergeCells('C1:D1');
					$sheet->mergeCells('C2:D2');
					$sheet->setBorder('A5:G55', 'thin');
					$sheet->cells('B2:B3', function($cells) {
						$cells->setBackground('#2fa4e7');
						$cells->setFontColor('#ffffff');
					});
						$sheet->cells('A5:G5', function($cells) {
							$cells->setBackground('#2fa4e7');
							$cells->setFontColor('#ffffff');
						});
							$sheet->setWidth(array(
									'A'     =>  9,
									'B'     =>  15,
									'C'     =>  25,
									'D'     =>  25,
									'E'     =>  25,
									'F'     =>  25,
									'G'     =>  45
										
							));
								
				});
			})->download('xls');
		}
	}
	
	public function save_alumnos()
	
	{
		$input = Input::all();
		if(Input::hasFile('import_file')){
			$path = Input::file('import_file')->getRealPath();
			$data = Excel::load($path, function($reader) {
			})->noHeading()->toarray();
			if ($data[4][0] == 'Numero' && $data[4][1] == 'Run' &&
				$data[4][2] == 'Primer Nombre' && $data[4][3] == 'Segundo Nombre' &&
				$data[4][4] == 'Apellido Paterno' && $data[4][5] == 'Apellido Materno' &&
				$data[4][6] == 'E-Mail')
							
				
			{
				if(!empty($data) && count($data) > 0){
					foreach ($data as $key => $value) {
						if ($key > 4){
							$EstadoPersona = true;
							$EstadoAsignacion = true;
							$EstadoAlumno = true;
							$new = true;
							$persona_new = new persona();
							$asignacion_new = new asignacion();
							$alumno_new = new alumno();
							if (isset($value[1])){
								$persona = AlumnoController::ValidarPersona($value, $EstadoPersona, $errores, $new);
								$asignacion = AlumnoController::ValidarAsignacion($value, $EstadoAsignacion, $errores);
								$alu_codigo = 0;
								$alumno = AlumnoController::ValidarAlumno($value, $EstadoAlumno, $errores, $input['cur_codigo'], $new, $alu_codigo, $EstadoPersona);
								if ($EstadoPersona){
									if ($new){
										$persona_new->insert($persona);
									}
									else{
										$rut = util::format_rut($value[1]);
										$persona_new = persona::where('per_rut', '=', $rut['numero'])->update($persona);
									}
								}
								if ($EstadoPersona && $EstadoAlumno){
									if ($EstadoAsignacion){
										$asignacion_new->insert($asignacion);
									}
									if ($new){
										$alumno_new->insert($alumno);
									}
									else{
										$alumno_new = Alumno::where('alu_codigo', '=', $alu_codigo)->update($alumno);
									}
								}
							}
						}
					}
					if ($input['groupOrganiza'] == 'orgRut'){
						$numero = 1;
						$alumnos = new alumno();
						$alumno_new = new alumno();
						$alumnos = Alumno::join('personas', 'alumnos.per_rut', '=', 'personas.per_rut')
										->select('personas.per_rut')
										->where('alumnos.cur_codigo', '=', $input['cur_codigo'])
										->orderBy(DB::raw('CONVERT(alumnos.per_rut,UNSIGNED INTEGER)'), 'ASC')
										->get();
						foreach ($alumnos as $key => $alumno) {
							$alumno_new = new alumno();
							$alumno_new = $alumno_new::where('alumnos.per_rut', '=', $alumno->per_rut)->update(array('alu_numero' => $numero));
							$numero = $numero + 1;
						}
							
					}
					if ($input['groupOrganiza'] == 'orgAlfabetico'){
						$numero = 1;
						$alumnos = new alumno();
						$alumnos = Alumno::join('personas', 'alumnos.per_rut', '=', 'personas.per_rut')
											->select('personas.per_rut')
											->where('alumnos.cur_codigo', '=', $input['cur_codigo'])
											->orderBy('per_apellido_paterno', 'ASC')
											->orderBy('per_apellido_materno', 'ASC')
											->orderBy('per_nombre', 'ASC')
											->get();
						foreach ($alumnos as $key => $alumno) {
							$alumno_new = new alumno();
							$alumno_new = $alumno_new::where('alumnos.per_rut', '=', $alumno->per_rut)->update(array('alu_numero' => $numero));
							$numero = $numero + 1;
						}
					}
				}
			}		
			else {
				$errores = 'La primera columna debe contener: "numero", "run", "nombre", "paterno", "materno", "email"'; 
			}
		}
		else {
			$errores = 'Error con el archivo';
		}
		if(empty($errores)){
			return redirect()->route('alumnos.index');
		}
		else {
			if (Session::has('search.alumno_curso')){
				$search = Session::get('search.alumno_curso');
				$this->cur_codigo	= $search['cur_codigo'];
				Session::put('search.alumno_errores', array(
						'errores'	=>	$errores));
				
			}
			return redirect()->route('alumnos.index');
				
		}
		
	}
	
	public function ValidarPersona($value, &$estado, &$errores, &$new){
		if (util::validaRut($value[1])){
			$rut = util::format_rut($value[1]);
			$cantidad_persona = persona::where('personas.per_rut', '=', $rut['numero'])->count();
			if ($cantidad_persona == 1){
				$new = false;
			}
			else {
				$persona['per_rut'] 		= $rut['numero'];
				$persona['per_dv'] 		= $rut['dv'];
				$persona['per_password'] = Hash::make(substr($rut['numero'], strlen($rut['numero'])-5, strlen($rut['numero'])));
				$persona['per_cantidad_intento'] = 3;
				$persona['per_activo'] = 1;
				$new = true;
			}
			$persona['per_nombre'] 	= $value[2];
			$persona['per_nombre_segundo'] = $value[3];
			$persona['per_apellido_paterno'] = $value[4];
			$persona['per_apellido_materno'] = $value[5];
			$estado = true;
			if (filter_var($value[6], FILTER_VALIDATE_EMAIL)) {
				if (!empty($value[6])){
					$cantidad_email = persona::where('per_email', '=', $value[6])->where('per_rut', '!=', $rut['numero'])->count();
					if ($cantidad_email == 0){
						$persona['per_email'] = $value[6];
					}
					else{
						if (!isset($errores)){
							$errores = 'Los siguientes Run no fueron ingresados o no cargaro todos los datos:\n';
						}
						else{
							$errores = $errores.'\n';
						}
						$errores = $errores.'El e-mail no fue cargador para el run: '.$value[1].', porque pertenece a otro usuario';
					}
				}
			}
			else {
				if (!isset($errores)){
					$errores = 'Los siguientes Run no fueron ingresados o no cargaro todos los datos:\n';
				}
				else{
					$errores = $errores.'\n';
				}
				$errores = $errores.'El e-mail no fue cargador para el run: '.$value[1].', porque no tiene formato correcto';
			}
		}
		else{
			$estado = false;
			$persona = "";
			if (!isset($errores)){
				$errores = 'Los siguientes Run no fueron ingresados o no cargaro todos los datos:\n';
			}
			else{
				$errores = $errores.'\n';
			}
			$errores = $errores.'El run: '.$value[1].', no es valido';
		}
		return $persona;
	}
	
	public function ValidarAsignacion($value, &$estado, &$errores){
		$rol = new rol;
		$rol = Rol::where('rol_nombre', '=', 'Alumno')->first();
		$rut = util::format_rut($value[1]);
		$estado = false;
		$cantidad_asignacion = asignacion::where('asignaciones.per_rut', '=', $rut['numero'])->where('asignaciones.rol_codigo', '=', $rol->rol_codigo)->count();
		if ($cantidad_asignacion == 0){
			$asignacion['rol_codigo']	= $rol->rol_codigo;
			$asignacion['per_rut']	= $rut['numero'];
			$estado = true;
			return $asignacion;
		}
	}
	
	public function ValidarAlumno($value, &$estado, &$errores, $cur_codigo, $new, &$alu_codigo, &$estado_persona){
		$rut = util::format_rut($value[1]);
		$cantidad_alumno = alumno::where('per_rut', '=', $rut['numero'])->count();
		$rut = util::format_rut($value[1]);
		if ($cantidad_alumno == 0){
			$alumno['per_rut'] 		= $rut['numero'];
			$alumno['alu_numero']	= $value[0];
			$alumno['alu_activo'] 	= 1;
			$alumno['cur_codigo'] 	= $cur_codigo;
			$new = true;
			$estado = true;
			return $alumno;
		}
		else{
			$alumno_new = new alumno();
			$alumno_new = alumno::where('per_rut', '=', $rut['numero'])->first();
			$alu_codigo = $alumno_new->alu_codigo; 
			if ($alumno_new->alu_activo == 0){
				$alumno['alu_numero']	= $value[0];
				$alumno['alu_activo'] 	= 1;
				$alumno['cur_codigo'] 	= $cur_codigo;
				$new = false;
				$estado = true;
				return $alumno;
			}
			else{
				$estado = false;
				$asignacion_new = asignacion::join('roles', 'asignaciones.rol_codigo', '=', 'roles.rol_codigo')
											->join('alumnos', 'asignaciones.per_rut', '=', 'alumnos.per_rut')
											->where('asignaciones.per_rut', '=', $rut['numero'])
											->where('alumnos.cur_codigo', '!=', $cur_codigo)
											->first();
				if (isset($asignacion_new)){
					$estado_persona = false;
					if ($asignacion_new->rol_nombre == 'Alumno'){
						if (!isset($errores)){
							$errores = 'Los siguientes Run no fueron actualizados o no cargaro todos los datos:\n';
						}
						else{
							$errores = $errores.'\n';
						}
						$errores = $errores.'El run esta inscrito en otro curso';
					}
					if ($asignacion_new->rol_nombre == 'Apoderado'){
						if (!isset($errores)){
							$errores = 'Los siguientes Run no fueron actualizados o no cargaro todos los datos:\n';
						}
						else{
							$errores = $errores.'\n';
						}
						$errores = $errores.'El run esta inscrito como apoderado';
					}
					if ($asignacion_new->rol_nombre == 'Profesor'){
						if (!isset($errores)){
							$errores = 'Los siguientes Run no fueron actualizados o no cargaro todos los datos:\n';
						}
						else{
							$errores = $errores.'\n';
						}
						$errores = $errores.'El run esta inscrito como profesor';
					}
				}
			}
		}
	}
	
	public function getAlumno(Request $request, $per_rut){
		$rut = util::format_rut($per_rut);
		$persona = new persona();
		$records = Persona::leftjoin('alumnos', 'alumnos.per_rut', '=', 'personas.per_rut')
							->leftjoin('profesores', 'profesores.per_rut', '=', 'personas.per_rut')
							->leftjoin('apoderados', 'apoderados.per_rut', '=', 'personas.per_rut')
							->where('personas.per_rut', '=', $rut['numero'])
							->select(DB::raw('alumnos.per_rut as alumno, profesores.per_rut as profesor, apoderados.per_rut as apoderado'))
							->get();
		if ($request->ajax()){
			return response()->json($records);
		}
		else{
			util::print_a($records,0);
		}
	}

	public function getAlumnoRetirado(Request $request, $per_rut){
		$rut = util::format_rut($per_rut);
		$persona = new persona();
		$records = Persona::join('alumnos', 'alumnos.per_rut', '=', 'personas.per_rut')
		->where('personas.per_rut', '=', $rut['numero'])
		->select('personas.per_nombre', 'personas.per_nombre_segundo', 'personas.per_apellido_paterno', 'personas.per_apellido_materno', 'personas.per_email')
		->get();
		if ($request->ajax()){
			return response()->json($records);
		}
		else{
			util::print_a($records,0);
		}
	}
	
	public function retirar($id){
		$idusuario = Auth::user()->per_rut;
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		if ($privilegio->mas_delete == 0){
			return redirect()->route('logout');
		}
		else{
			$alumno = new alumno();
			$alumno = alumno::where('per_rut', '=', $id)->first();
			$alumno->alu_activo = false;
			$alumno->save();
		}
		return redirect()->route('alumnos.index');
	}
	
	public function arreglo(){
		$tabla[] = array(	'nombre' 		=> 'Numero',
							'campo'			=> 'alu_numero',
							'clase' 		=> 'container col-md-5 required',
							'validate'		=> '',
							'descripcion'	=> 'Numero',
							'tipo'			=> 'input',
							'select'		=> 0,
							'value'			=> $this->alu_numero,
							'filter'		=> 1,
							'enable'		=> false);
		$tabla[] = array(	'nombre' 		=> 'Run',
							'campo'			=> 'per_rut',
							'clase' 		=> 'container col-md-5 required',
							'validate'		=> '',
							'descripcion'	=> 'Run',
							'tipo'			=> 'input',
							'select'		=> 0,
							'value'			=> $this->per_rut,
							'filter'		=> 1,
							'enable'		=> false);
		$tabla[] = array(	'nombre' 		=> 'Nombre',
							'campo'			=> 'per_nombre',
							'clase' 		=> 'container col-md-5',
							'validate'		=> '',
							'descripcion'	=> 'Nombre',
							'value'			=> $this->per_nombre,
							'tipo'			=> 'input',
							'select'		=> 0,
							'filter'		=> 1,
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

}
