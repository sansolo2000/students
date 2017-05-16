<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\helpers\navegador;
use Session;
use App\Http\Requests;
use App\models\curso;
use App\helpers\util;
use DB;
use App\models\periodo;
use App\models\calificacion;
use Maatwebsite\Excel\Facades\Excel;
use App\models\alumno;
use Illuminate\Support\Facades\Input;
use App\models\asignatura;
use Hamcrest\Type\IsNumeric;
use App\models\profesor;
use App\models\anyo;
use App\models\asign_profe_curso;



class CargarNotasController extends Controller
{
	public $cur_codigo;
	public $asg_nombre;
	public $per_rut;
	public $per_nombre;
	public $pri_codigo;
	public $errores;
	
	
	public $Privilegio_modulo = 'cargarnotas';
	public $paginate = 20;
	
	public function index($id = NULL)
	{
		// Menu
	
		$idusuario = Auth::user()->per_rut;
		$menu = navegador::crear_menu($idusuario);
		$cantidad = 0;

		if (Session::has('search.cargarnotas_errores')){
			$search = Session::get('search.cargarnotas_errores');
			$this->errores = $search['errores'];
			Session::forget('search.cargarnotas_errores');
		}
		
		
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
				$this->cur_codigo 	= $_POST['cur_nombre'];
				$this->pri_codigo	= $_POST['pri_nombre'];
				Session::put('search.cargarnotas', array(
						'cur_codigo' 	=> $this->cur_codigo,
						'pri_codigo'	=> $this->pri_codigo
				));
			}
			else{
				if (Session::has('search.cargarnotas')){
					$exist = 1;
					$search = Session::get('search.cargarnotas');
					$this->cur_codigo 	= $search['cur_codigo'];
					$this->pri_codigo 	= $search['pri_codigo'];
						
				}
				else{
					$exist = 1;
					$this->cur_codigo 	= -1;
					$this->pri_codigo 	= periodo::where('pri_activo', '=', 1)->select('pri_codigo')->first()->toArray();
				}
			}
	
			//$tabla = AsignaturaController::arreglo();
			if ($exist == 1){
				$profesor = curso::select(DB::raw('pr.per_rut, CONCAT(pr.per_nombre, " ", pr.per_apellido_paterno, " ", pr.per_apellido_materno) as profesor'))
							->join('niveles', 'cursos.niv_codigo', '=', 'niveles.niv_codigo')
							->join('profesores as pj', 'cursos.pro_codigo', '=', DB::raw('pj.pro_codigo'))
							->join('personas AS pr', DB::raw('pr.per_rut'), '=', 'pj.per_rut')
							->where('cursos.cur_codigo', '=', $this->cur_codigo);
				$cantidadprofesor = $profesor->count();
				$profesor = $profesor->first();
				$asignaturas = asign_profe_curso::join('cursos', 'asign_profe_curso.cur_codigo', '=', 'cursos.cur_codigo')
							->join('asignaturas', 'asign_profe_curso.asg_codigo', '=', 'asignaturas.asg_codigo')
							->join('profesores', 'asign_profe_curso.pro_codigo', '=', 'profesores.pro_codigo')
							->select('asignaturas.asg_codigo', 'asignaturas.asg_nombre')
							->orderBy('asignaturas.asg_orden', 'ASC')
							->where('cursos.cur_codigo', '=', $this->cur_codigo);
				if ($privilegio->rol_nombre == 'Profesor'  && $profesor['per_rut'] != $idusuario){
					$asignaturas = $asignaturas->where('profesores.per_rut', '=', $idusuario);
				}
				$asignaturas = $asignaturas->get();
				$alumnos = asign_profe_curso::join('cursos', 'asign_profe_curso.cur_codigo', '=', 'cursos.cur_codigo')
							->join('asignaturas', 'asign_profe_curso.asg_codigo', '=', 'asignaturas.asg_codigo')
							->join('profesores as pj', 'asign_profe_curso.pro_codigo', '=', DB::raw('pj.pro_codigo'))
							->join('alumnos as al', 'cursos.cur_codigo', '=', DB::raw('al.cur_codigo'))
							->join('personas AS pr', DB::raw('pr.per_rut'), '=', 'pj.per_rut')
							->join('personas AS ag', DB::raw('ag.per_rut'), '=', 'al.per_rut')
							->where('asign_profe_curso.cur_codigo', '=', $this->cur_codigo)
							->where('al.alu_activo', '=', 1)
							->select(DB::raw('al.alu_codigo'), 'asignaturas.asg_codigo', 'asignaturas.asg_nombre', DB::raw('ag.per_rut as per_rut'), DB::raw('ag.per_dv as per_dv'),
									DB::raw('ag.per_nombre as per_nombre'), DB::raw('ag.per_apellido_paterno as per_apellido_paterno'),
									DB::raw('ag.per_apellido_materno as per_apellido_materno'),
									DB::raw('pr.per_nombre as pro_nombre'), DB::raw('pr.per_apellido_paterno as pro_apellido_paterno'),
									DB::raw('pr.per_apellido_materno as pro_apellido_materno'))				;
							if ($privilegio->rol_nombre == 'Profesor'  && $profesor['per_rut'] != $idusuario){
								$alumnos = $alumnos->where(DB::raw('pj.per_rut'), '=', $idusuario);
							}
				$alumnos = $alumnos->orderBy('asignaturas.asg_orden', 'ASC')
							->orderBy(DB::raw('al.alu_numero'), 'ASC')
							->get();
//				util::print_a($alumnos, 0);
			}
			$script = '';
			$modal = '';
			if ($cantidadprofesor > 0 && $asignaturas->count() > 0 && $alumnos->count() > 0){
				$cabecera = '
					<div class="form-group col-sm-12">
					<ul class="nav nav-tabs">';
				$ubicacion = 1;
				$periodos = periodo::orderBy('periodos.pri_orden', 'ASC')->get();
	 			foreach ($asignaturas as $asignatura){
	 				$cantidad = curso::where('cursos.cur_codigo', '=', $this->cur_codigo)->first();
	 				//util::print_a($cantidad, 0);
				 	$notas = calificacion::join('asign_profe_curso', 'calificaciones.apc_codigo', '=', 'asign_profe_curso.apc_codigo')
				 			->join('asignaturas', 'asign_profe_curso.asg_codigo', '=', 'asign_profe_curso.asg_codigo')
							->where('asign_profe_curso.cur_codigo', '=', $this->cur_codigo)
							->where('asign_profe_curso.asg_codigo', '=', $asignatura->asg_codigo);
					$notas = $notas->groupby('asignaturas.asg_nombre')
							->select(DB::raw('asignaturas.asg_codigo, asignaturas.asg_nombre, count(*) as cantidad'))
							->orderBy(DB::raw('cantidad'), 'ASC')
							->first();
				 	$cantidad = $cantidad['cur_cantidad_notas'];
					
	//				util::print_a($modal, 0);
					
					if ($ubicacion == 1){
						$cabecera .= '<li class="active">';
						$ubicacion = 0;
					}
					else{
						$cabecera .= '<li>';
					}
					$cabecera .= '			<a aria-expanded="false" href="#'.$asignatura->asg_codigo.'" data-toggle="tab">'.$asignatura->asg_nombre.'</a>';
					$cabecera .= '</li>';
				}
				$cabecera .= '</ul>';
				
				
	 			$cuerpo	= '<div id="myTabContent" class="tab-content">';
	 			$asignatura_seleccionada = '';
	 			$ubicacion = 1;
	
				$periodos = periodo::orderBy('periodos.pri_orden', 'ASC')->get();
	 			foreach ($alumnos as $alumno){
	 				if ($alumno->asg_nombre != $asignatura_seleccionada){
	 					if ($asignatura_seleccionada != ''){
	 						$cuerpo	.= '</tbody>
	 									</table>
	 									</div>';
	 					}
	 					$asignatura_seleccionada = $alumno->asg_nombre; 
	 					if ($ubicacion == 1){
	 						$cuerpo	.= '<div class="tab-pane fade active in" id="'.$alumno->asg_codigo.'">';
	 						$ubicacion = 0;
	 					}
	 					else{
	 						$cuerpo	.= '<div class="tab-pane fade" id="'.$alumno->asg_codigo.'">';
	 					}
	 					$cuerpo .= '
							<div class="container col-md-12">
								&nbsp;
							</div>
							<div class="container col-md-12">
	 							<div class="container col-md-3 center_pers">
	 							
						  		</div>
	 							<div class="container col-md-6 center_pers">
									<div class="panel panel-primary">
										<div class="panel-heading">
							    			<h3 class="panel-title">Profesor de '.$alumno->asg_nombre.'</h3>
							  			</div>
							  			<div class="panel-body">
											<input class="form-control" id="asignatura" name="asignatura" type="text" disabled="disabled" value="'.$alumno->pro_nombre.' '.$alumno->pro_apellido_paterno.' '.$alumno->pro_apellido_materno.'">
										</div>
									</div>
								</div>
								<div class="container col-md-3 center_pers">
						  		</div>
							</div>';
	 					$cuerpo	.= '<table class="table table-striped table-hover table-bordered">
					 					<thead>
						 					<tr>
							 					<th style="width:12%">Rut</th>
							 					<th style="width:22%">Alumno</th>';
	 					$width = 66 / ($cantidad + 1);
						for ($i = 1; $i <= $cantidad; $i++){							
							$cuerpo	.= '<th style="width:'.$width.'%">N'.$i.'</th>';
						}
						$cuerpo	.= '<th style="width:'.$width.'%">Pro</th>
	 										</tr>
						 				</thead>
	 									<tbody>';
	 				}
	 				$rut = util::format_rut($alumno->per_rut, $alumno->per_dv);
	 				$cuerpo	.= '
						 					<tr>
							 					<td>'.$rut['numero'].'-'.$rut['dv'].'</th>
							 					<td>'.$alumno->per_nombre.' '.$alumno->per_apellido_paterno.' '.$alumno->per_apellido_paterno.'</th>';
	 				$maximo = calificacion::join('asign_profe_curso', 'calificaciones.apc_codigo', '=', 'asign_profe_curso.apc_codigo')
	 											->join('asignaturas', 'asign_profe_curso.asg_codigo', '=', 'asignaturas.asg_codigo')
												->where('asign_profe_curso.cur_codigo', '=', $this->cur_codigo)
												->where('asign_profe_curso.asg_codigo', '=', $alumno->asg_codigo)
												->where('calificaciones.alu_codigo', '=', $alumno->alu_codigo)
												->where('calificaciones.pri_codigo', '=', $this->pri_codigo)
												->select(DB::raw('max(calificaciones.cal_fecha) as fecha'))
												->first();
	 				$calificaciones = calificacion::join('asign_profe_curso', 'calificaciones.apc_codigo', '=', 'asign_profe_curso.apc_codigo')
	 											->join('asignaturas', 'asign_profe_curso.asg_codigo', '=', 'asignaturas.asg_codigo')
	 											->where('asign_profe_curso.cur_codigo', '=', $this->cur_codigo)
												->where('asign_profe_curso.asg_codigo', '=', $alumno->asg_codigo)
												->where('calificaciones.alu_codigo', '=', $alumno->alu_codigo)
												->where('calificaciones.pri_codigo', '=', $this->pri_codigo);
					if (isset($maximo['fecha'])){
						$calificaciones = $calificaciones->where('calificaciones.cal_fecha', '=', $maximo['fecha']);
					}
													
					$calificaciones = $calificaciones->select('calificaciones.cal_numero', 'calificaciones.cal_posicion')
									->orderby('calificaciones.cal_posicion')
	 								->get();
					$i = 1;
					$suma = 0;
					$cantidad_notas = 0;
					foreach ($calificaciones as $calificacion) {
						if ((float) $calificacion->cal_numero > 0){
							$NotasMostrar[$calificacion->cal_posicion]['html'] = '<td>'.number_format($calificacion->cal_numero, 1, ',', ' ').'</th>';
							$NotasMostrar[$calificacion->cal_posicion]['exite'] = true;
							$NotasMostrar[$calificacion->cal_posicion]['nota'] = $calificacion->cal_numero;
						}
						else{
							$NotasMostrar[$calificacion->cal_posicion]['html'] = '<td>&nbsp;</td>';
							$NotasMostrar[$calificacion->cal_posicion]['exite'] = false;
							$NotasMostrar[$calificacion->cal_posicion]['nota'] = 0;
						}
					}
					for ($i = 1; $i <= $cantidad; $i++){
						if (!isset($NotasMostrar[$i])){
							$NotasMostrar[$i]['html'] = '<td>&nbsp;</td>';
							$NotasMostrar[$i]['exite'] = false;
							$NotasMostrar[$i]['nota'] = 0;
						}
					}
					for ($i = 1; $i <= $cantidad; $i++){
						if ($NotasMostrar[$i]['exite']){
							$suma = $suma + $NotasMostrar[$i]['nota'];
							$cantidad_notas++;
						}
						$cuerpo	.= $NotasMostrar[$i]['html'];
	 				}
	 				if ($cantidad_notas == 0){
		 				$cuerpo	.= '				<td>'.number_format($suma, 1, ',', ' ').'</td>
		 										</tr>';
	 				}
	 				else {
		 				$suma = $suma / $cantidad_notas;
		 				$cuerpo	.= '				<td>'.number_format($suma, 1, ',', ' ').'</td>
		 										</tr>';
	 				}
	 				unset($NotasMostrar); 						
	 			}
	 			$cuerpo	.= '</tbody>
	 						</table>
	 						</div>';
	 			$cuerpo	.= '</div>
						</div>';
			}
			else {
				$cabecera = '
					<div class="form-group col-sm-12"></div>';
				$cuerpo	= '<div id="myTabContent" class="tab-content">';
				if ($this->cur_codigo == -1){
					$cuerpo .= '&nbsp;';
				}
				else{
					$cuerpo .= '	<div class="alert alert-dismissible alert-danger">';
					$cuerpo .= '			El curso debe tener: <strong>asignaturas</strong>, <strong>profesores</strong> y <strong>alumnos</strong> para cargar notas.';
					$cuerpo .= '	</div>';
				}
				$cuerpo .= '</div>';
			}
 			$periodo = new periodo();
 			$periodo = periodo::orderBy('pri_orden', 'ASC')
 									->lists('pri_nombre', 'pri_codigo')
 									->toArray();
 			$periodo2 = $periodo;
 			$periodo2 = util::array_indice($periodo, 3);
 			$periodo = util::array_indice($periodo, -1);
 			
			$entidad = array('Nombre' => $this->Privilegio_modulo, 'controller' => '/'.util::obtener_url().'cargarnotas', 'pk' => 'cur_codigo', 'clase' => 'container col-md-12', 'col' => 5);
			return view('main.cargarnotas')
					->with('menu', $menu)
					->with('cuerpo', $cuerpo)
					->with('cur_codigo', $this->cur_codigo)
					->with('pri_codigo', $this->pri_codigo)
					->with('periodo2', $periodo2)
					->with('periodo', $periodo)
					->with('user', $idusuario)
					->with('profesor', $profesor)
					->with('record', $asignaturas)
					->with('cabecera', $cabecera)
					->with('entidad', $entidad)
					->with('script', $script)
					->with('modal', $modal)
					->with('errores', $this->errores)
					->with('CantidadNotas', $cantidad)
					->with('privilegio', $privilegio);
					
		}
	}
	
	public function show(){
		return redirect()->route('cargarnotas.index');
	}
	
	public function exportar_calificaciones($curso, $periodoMostrar)
	{
		$idusuario = Auth::user()->per_rut;
		$privilegios = navegador::privilegios($idusuario, $this->Privilegio_modulo);
		$privilegio = $privilegios[0];
		$profesor = curso::select('cursos.cur_codigo', DB::raw('pr.per_rut, CONCAT(cursos.cur_numero, "-", cursos.cur_letra, " ", niveles.niv_nombre) as name'),
								DB::raw('CONCAT(pr.per_nombre, " ", pr.per_apellido_paterno) as profesor'))
							->join('niveles', 'cursos.niv_codigo', '=', 'niveles.niv_codigo')
							->join('profesores as pj', 'cursos.pro_codigo', '=', DB::raw('pj.pro_codigo'))
							->join('personas AS pr', DB::raw('pr.per_rut'), '=', 'pj.per_rut')
							->where('cursos.cur_codigo', '=', $curso)
							->first();
		$asignaturas = asign_profe_curso::join('asignaturas', 'asign_profe_curso.asg_codigo', '=', 'asignaturas.asg_codigo')
							->join('profesores', 'asign_profe_curso.pro_codigo', '=', 'profesores.pro_codigo')
							->join('personas', 'profesores.per_rut', '=', 'personas.per_rut')
							->orderBy('asignaturas.asg_orden', 'ASC')
							->select('asignaturas.asg_codigo', 'asignaturas.asg_nombre', DB::raw('CONCAT(personas.per_nombre, " ", personas.per_apellido_paterno) as profesor'))
							->where('asign_profe_curso.cur_codigo', '=', $curso);
		if ($privilegio->rol_nombre == 'Profesor'  && $profesor['per_rut'] != $idusuario){
			$asignaturas = $asignaturas->where('profesores.per_rut', '=', $idusuario);
		}
		$asignaturas = $asignaturas->get();
		//util::print_a($asignaturas, 0);
		
		//$nombre = $curso->name;
//		util::print_a($asignaturas, 0);
		$alumnos = alumno::where('alumnos.cur_codigo', '=', $curso)->where('alumnos.alu_activo', '=', 1)->get();
		$CantidadAlumnos = $alumnos->count(); 
		if ($CantidadAlumnos> 0){
			$libros = $profesor->name.' - Libro de clases';
			$alfabeto = util::alfabeto(0);
			$curso_cantidad = curso::where('cursos.cur_codigo', '=', $curso)->first();
			$notas = $curso_cantidad->cur_cantidad_notas; 
			Excel::create($libros, function($excel) use($profesor, $asignaturas, $notas, $periodoMostrar, $curso, $CantidadAlumnos, $alfabeto, $idusuario) {
				if ($periodoMostrar == 0){
					$periodos = periodo::orderBy('periodos.pri_orden', 'ASC')->get();
				}
				else{
					$periodos = periodo::orderBy('periodos.pri_orden', 'ASC')->Where('periodos.pri_codigo', '=', $periodoMostrar)->get();
				}
				$columnas_excel = CargarNotasController::columnas_excel($notas, $periodos->count());
				foreach ($asignaturas as $asignaturalistar){
					$excel->sheet($asignaturalistar->asg_nombre, function($sheet) use($profesor, $asignaturalistar, $periodoMostrar, $notas, $curso, $CantidadAlumnos, $columnas_excel, $periodos, $alfabeto, $idusuario){
						$ind = 0;
						$alumnos = asign_profe_curso::select(DB::raw('al.alu_numero'), DB::raw('al.alu_codigo'), 'asignaturas.asg_codigo', 'asignaturas.asg_nombre', DB::raw('ag.per_rut as per_rut'), DB::raw('ag.per_dv as per_dv'),
											DB::raw('ag.per_nombre as per_nombre'), DB::raw('ag.per_apellido_paterno as per_apellido_paterno'),
											DB::raw('ag.per_apellido_materno as per_apellido_materno'),
											DB::raw('pr.per_nombre as pro_nombre'), DB::raw('pr.per_apellido_paterno as pro_apellido_paterno'),
											DB::raw('pr.per_apellido_materno as pro_apellido_materno'))
											->join('cursos', 'cursos.cur_codigo', '=', 'asign_profe_curso.cur_codigo')
											->join('asignaturas', 'asignaturas.asg_codigo', '=', 'asign_profe_curso.asg_codigo')
											->join('profesores as pj', 'asign_profe_curso.pro_codigo', '=', DB::raw('pj.pro_codigo'))
											->join('alumnos as al', 'cursos.cur_codigo', '=', DB::raw('al.cur_codigo'))
											->join('personas AS pr', DB::raw('pr.per_rut'), '=', 'pj.per_rut')
											->join('personas AS ag', DB::raw('ag.per_rut'), '=', 'al.per_rut')
											->where('asign_profe_curso.cur_codigo', '=', $curso)
											->where('asignaturas.asg_codigo', '=', $asignaturalistar->asg_codigo)
											->where('al.alu_activo', '=', 1)
											->orderBy('asignaturas.asg_orden', 'ASC')
											->orderBy(DB::raw('al.alu_numero'), 'ASC')
											->get();
						foreach ($alumnos as $key => $alumno) {
							$pos = 0;
							$rut = util::format_rut($alumno->per_rut, $alumno->per_dv);
							$data[$ind][$columnas_excel[$pos]['name']] 	= $alumno->alu_numero;
							$pos++;
							$data[$ind][$columnas_excel[$pos]['name']] 	= $rut['numero'].'-'.$rut['dv'];
							$pos++;
							$data[$ind][$columnas_excel[$pos]['name']] 	= $alumno->per_nombre.' '.$alumno->per_apellido_paterno.' '.$alumno->per_apellido_materno;
							$pos++;
							foreach ($periodos as $periodo){
								$col = $pos;
								$curso = curso::where('cursos.cur_codigo', '=', $profesor->cur_codigo)->first();
								$cantidadnotas = $curso->cur_cantidad_notas;
								for ($i=1; $i <= $cantidadnotas; $i++){
									$calificaciones = asign_profe_curso::join('calificaciones', 'asign_profe_curso.apc_codigo', '=', 'calificaciones.apc_codigo')
									->join('asignaturas', 'asign_profe_curso.asg_codigo', '=', 'asignaturas.asg_codigo')
									->where('asign_profe_curso.cur_codigo', '=', $profesor->cur_codigo)
									->where('asignaturas.asg_codigo', '=', $alumno->asg_codigo)
									->where('calificaciones.alu_codigo', '=', $alumno->alu_codigo)
									->where('calificaciones.pri_codigo', '=', $periodo['pri_codigo'])
									->where('calificaciones.cal_posicion', '=', $i)
									->select('calificaciones.cal_numero');
									
									$calificacionexiste = $calificaciones->count();
									
									if($calificacionexiste == 1){
										$calificacion = $calificaciones->first();
										if ((float) $calificacion->cal_numero > 0){
											$data[$ind][$columnas_excel[$pos]['name']] = (float) $calificacion->cal_numero;
										}
										else{
											$data[$ind][$columnas_excel[$pos]['name']] = '';
										}
									}
									else{
										$data[$ind][$columnas_excel[$pos]['name']] = '';
									}
									$pos++;
								}
								$row = $ind + 7;
								
								$data[$ind][$columnas_excel[$pos]['name']] = "=if(sum({$alfabeto[$col]}{$row}:{$alfabeto[$pos - 1]}{$row})=0, 0, round(average({$alfabeto[$col]}{$row}:{$alfabeto[$pos - 1]}{$row}),1)";
								$pos++;
							}
							if ($periodoMostrar == 0){
								$celda = '';
								foreach ($columnas_excel as $columna_excel){
									if ($columna_excel['type'] == 2){
										$celda .= "{$columna_excel['letter']}{$row};";
									}
								}
								$celda = substr($celda, 0, strlen($celda)-1);
								$celda = "=if(sum({$celda})=0, 0, round(average({$celda}),1)";
								$data[$ind][$columnas_excel[$pos]['name']] = $celda;
							}
							$ind++;
						}
						
						$sheet->row(2, array(
								'','Curso:', $profesor->name, '', '', 'Asignatura:', '', '', '', $asignaturalistar['asg_nombre'], '', '', '', '', '', 'Cargar:', '', '', 'Marcar con X para cargar notas de la asignatura'
						));

						$sheet->row(3, array(
								'','Profesor Jefe:', $profesor->profesor, '', '', 'Profesor de la Asignatura:', '', '', '', $asignaturalistar['profesor']
						));
						
						if ($periodoMostrar == 0){
							$NombreMostrar = 'Todos';
						}
						else{
							$periodos = periodo::orderBy('periodos.pri_orden', 'ASC')->Where('periodos.pri_codigo', '=', $periodoMostrar)->first();
//							util::print_a($periodos)
							$NombreMostrar = $periodos->pri_nombre;
						}
						
						$sheet->row(4, array(
								'','', '', '', '', 'Periodos:', '', '', '', $NombreMostrar
						));
						
						$sheet->fromArray($data, null, 'A6', false, true);
						$sheet->setBorder('B2:D3', 'thin');
						$sheet->mergeCells('C2:D2');
						$sheet->mergeCells('C3:D3');
						$sheet->setBorder('F2:N4', 'thin');
						$sheet->mergeCells('F2:I2');
						$sheet->mergeCells('F3:I3');
						$sheet->mergeCells('F4:I4');
						$sheet->mergeCells('J2:N2');
						$sheet->mergeCells('J3:N3');
						$sheet->mergeCells('J4:N4');
						$sheet->mergeCells('P2:Q2');
						$sheet->mergeCells('S2:V3');
						$sheet->setBorder('S2:V3', 'thin');
						$sheet->cells('S2:V3', function($cells) {
							$cells->setAlignment('justify');
							$cells->setValignment('center');
						});
						$sheet->cells('P2:Q2', function($cells) {
							$cells->setBackground('#2fa4e7');
							$cells->setFontColor('#ffffff');
						});
						$sheet->setBorder('P2:R2', 'thin');
						$sheet->cells('F2:I4', function($cells) {
							$cells->setBackground('#2fa4e7');
							$cells->setFontColor('#ffffff');
						});
						$celda = $CantidadAlumnos+6;
						$letter1 = $columnas_excel[0]['letter'];
						$letter2 = $columnas_excel[count($columnas_excel)-1]['letter'];
						$celda = "{$letter1}6:{$letter2}{$celda}";
						$sheet->setBorder($celda, 'thin');
						$sheet->cells('B2:B3', function($cells) {
							$cells->setBackground('#2fa4e7');
							$cells->setFontColor('#ffffff');
						});
						$titulo = '';
						foreach ($columnas_excel as $clave => $columna_excel){
							if ($titulo == ''){
								$titulo =  "{$columna_excel['letter']}6";
							}
							if (count($columnas_excel)-1 == $clave){
								$a = $clave;
								$titulo =  "{$titulo}:{$columna_excel['letter']}6";
								$sheet->cells("{$titulo}", function($cells) {
									$cells->setBackground('#E8E8E8');
									$cells->setFontColor('#000000');
								});
								
							}
							if ($columna_excel['type'] == 2 || $columna_excel['type'] == 3){
								$celda = $CantidadAlumnos+6;
								$letter1 = $columna_excel['letter'];
								$celda = "{$letter1}6:{$letter1}{$celda}";
								$sheet->cells("{$celda}", function($cells) {
									$cells->setBackground('#E8E8E8');
									$cells->setFontColor('#000000');
								});
							}
							if ($columna_excel['type'] > 0){
								$celda = $CantidadAlumnos+6;
								$celda = "{$columna_excel['letter']}6:{$columna_excel['letter']}{$celda}";
								$decimal[$celda] = '0.0';
							}
							$width[$columna_excel['letter']] = $columna_excel['width'];
						}
						$sheet->setColumnFormat($decimal);
						$sheet->setWidth($width);
					});
					unset($data);
				}
				$excel->setActiveSheetIndex(0);
			})->download('xls');
		}
		else{
			//mensaje
		}
	}

	public function importar_calificaciones(){
		$input = Input::all();
		if(Input::hasFile('import_file')){
			$path = Input::file('import_file')->getRealPath();
			$sheetNames = Excel::load($path)->getSheetNames();
			foreach ($sheetNames as $sheetName){
				$validar = true;
				$validarNotas = true;
				//				$results = Excel::selectSheets($sheetName)->load($path)->all();
				$results = Excel::selectSheets($sheetName)->load($path, function($reader) {
					$reader->noHeading();
				})->get()->toArray();
				
				$periodo = $results[3][9];
				if ($periodo == 'Todos'){
					$validar = false;
					$mensajes[] = array('tipo' => 2, 'descripcion' => 'La planilla carga correspode al a&ntilde;o completo y \nsolo pueden ser cargadas las planilla de un periodo especifico.');
					break;
				}
				else {
					$pri_periodo = periodo::where('periodos.pri_nombre', '=', $periodo)->First();
					if ($pri_periodo->pri_cerrado == 1){
						$validar = false;
						$mensajes[] = array('tipo' => 2, 'descripcion' => 'La planilla correspode a un periodo cerrado por la direcci&oacute;n, \npor lo tanto, no puede ser cargadas.');
						break;
					}
				}
				$i = 1;
				$x = 4;
				$j = 1;
				$idusuario = Auth::user()->per_rut;
				$profesor = curso::join('profesores', 'cursos.pro_codigo', '=', 'profesores.pro_codigo')
								->join('niveles', 'cursos.niv_codigo', '=', 'niveles.niv_codigo')
								->where('cursos.cur_codigo', '=', $input['curso'])
								->select('profesores.per_rut', DB::raw('CONCAT(cursos.cur_numero, "-", cursos.cur_letra, " ", niveles.niv_nombre) as name'))
								->first();
				$asignatura = asign_profe_curso::join('asignaturas', 'asign_profe_curso.asg_codigo', '=', 'asignaturas.asg_codigo')
								->join('profesores', 'asign_profe_curso.pro_codigo', '=', 'profesores.pro_codigo')
								->where('asign_profe_curso.cur_codigo', '=', $input['curso'])
								->select('asign_profe_curso.apc_codigo', 'asignaturas.asg_codigo', 'profesores.per_rut', 'asignaturas.asg_nombre')
								->where('asignaturas.asg_nombre', '=', $sheetName)
								->first();
				$roles = util::roles_persona($idusuario);								
				if (($roles->rol_nombre != 'Administrador' && $roles->rol_nombre != 'Direccion')){
					if ((!($profesor->per_rut == $idusuario || $asignatura->per_rut == $idusuario)) && ($validar)){
						$validar = false;
						$mensajes[] = array('tipo' => 1, 'descripcion' => 'La asignatura de '.$asignatura->asg_nombre .' no esta asignado al profesor y no es profesor jefe');
					}
				}
				$cantidad = count($results);
				if ((!strtoupper($results[1][17]) == 'X') && ($validar)){
					$validar = false;
					$mensajes[] = array('tipo' => 2, 'descripcion' => 'La asignatura de '.$asignatura->asg_nombre .' no esta marcada para actualizar');
				}
				if ($validar){
					if (!($results[1][2] == $profesor->name)){
						$validar = false;
						$mensajes[] = array('tipo' => 2, 'descripcion' => 'La planilla ingresada no corresponde al curso');
						break;
					}
				}
				$columna = $results[5];
				$pos = 1;
				if ($validar){
					foreach ($columna as $clave => $celda){
						if ($validarNotas){
							if ($celda == 'Numero' || $celda == 'Run' ||
									$celda == 'Nombre Alumno' || $celda == 'N'.$i ||
									$celda == 'Prom.'.$j || $celda == 'Prom. Final' ||
									(!isset($celda) && $j > 1)
									)
							{
								if ($celda == 'N'.$i){
									//if ($clave > 7){
									//	$validarNotas = false;
									//}
									$notas[$clave] = array('N'.$i => $clave, 'Nota' => true, 'periodo' => $j-1, 'posicion' => $pos);
									$i++;
									$pos++;
								}
								if ($celda == 'Prom.'.$j){
									$notas[$clave] = array('Prom.'.$j => $clave, 'Nota' => false, 'periodo' => $j-1, 'posicion' => 0);
									$j++;
									$pos = 1;
								}
								if ($celda == 'Prom. Final'){
									$notas[$clave] = array('Prom. Final' => $clave, 'Nota' => false, 'periodo' => 0, 'posicion' => 0);
								}
							}
							else{
								$mensajes[] = array('tipo' => 3, 'descripcion' => 'La asignatura de '.$asignatura->asg_nombre .' no tiene todas las columnas');
								$validar = false;
							}
						}
					}
				}
				if ($validar){
					$columnas = $results;
					$col = 6;
					$row = 3;
					for ($i = 6; $i < count($columnas); $i++){
						$nota = 1;
						$columna = $columnas[$i];
						for ($j = 3; $j <= count($notas) + 2; $j++){
							if ($notas[$j]['Nota']){
								$fila =  $columna[$j];
								if (is_numeric($fila)){
									if (!($fila <= 7 && $fila >= 1)){
										$validarNotas = false;
										break;
									}
								}
								$nota++;
							}
						}
						if (!$validarNotas){
							break;
						}
					}
						
				}
				if ($validar && $validarNotas){
					$columnas = $results;
					$col = 6;
					$row = 3;
					$fecha = date('Y-m-d H:i:s');
					for ($i = 6; $i < count($columnas); $i++){
						$nota = 1;
						$columna = $columnas[$i];
						$rut = util::format_rut($columna[1]);
						$alumno = new alumno();
						$alumno = alumno::where('alumnos.cur_codigo', '=', $input['curso'])->where('alumnos.per_rut', '=', $rut['numero'])->first();
						for ($j = 3; $j <= count($notas) + 2; $j++){
							if ($notas[$j]['Nota']){
								$fila =  $columna[$j];

								$pri_codigo = $pri_periodo->pri_codigo;

								$calificacion = asign_profe_curso::join('calificaciones', 'asign_profe_curso.apc_codigo', '=', 'calificaciones.apc_codigo')
														->where('asg_codigo', '=', $asignatura['asg_codigo'])
														->where('cal_posicion', '=', $nota)
														->where('alu_codigo', '=', $alumno->alu_codigo)
														->where('pri_codigo', '=', $pri_codigo);
								$calificacionExiste = $calificacion->count();
								if ($calificacionExiste == 0){
									if (is_numeric($fila)){
										$calificacion_new = new calificacion();
										$calificacion_new->apc_codigo 		= $asignatura['apc_codigo'];
										$calificacion_new->cal_numero 		= $fila; 
										$calificacion_new->cal_posicion 	= $nota;
										$calificacion_new->cal_fecha		= $fecha;	
										$calificacion_new->alu_codigo 		= $alumno->alu_codigo;
										$calificacion_new->pri_codigo		= $pri_codigo;
										$calificacion_new->created_at		= date('Y-m-d H:i:s');
										$calificacion_new->updated_at		= date('Y-m-d H:i:s'); 
										$calificacion_new->save();
									}
								}
								else{
									if (is_numeric($fila)){
										$calificacion = $calificacion->first();
										$calificacion_upd = new calificacion();
										$calificacion_upd = calificacion::find($calificacion->cal_codigo);
										$calificacion_upd->cal_numero 		= $fila;
										$calificacion_upd->cal_fecha		= $fecha;
										$calificacion_upd->save();
									}
									else {
										$calificacion = $calificacion->first();
										$calificacion_upd = new calificacion();
										$calificacion_upd = calificacion::find($calificacion->cal_codigo);
										$calificacion_upd->cal_numero 		= -1;
										$calificacion_upd->cal_fecha		= $fecha;
										$calificacion_upd->save();
									}
								}
								$nota++;
							}
						}
					}
					
					$mensajes[] = array('tipo' => 4, 'descripcion' => 'La asignatura de '.$asignatura->asg_nombre.' fue cargada');
				}
				if (!$validarNotas){
					break;
				}
			}
		}
		$mensajemostar = '';
		if (!$validarNotas){
			$mensajemostar = 'No se realiz&oacute; ninguna carga de notas, porque una de las notas ingresada es menor a 1,0 o mayor 7,0\n\n  Revise las notas cargadas y vuelva a intentarlo';
		}
		else {
			foreach ($mensajes as $mensaje){
				$mensajemostar .= $mensaje['descripcion'].'\n';
			}
		}
		
		Session::put('search.cargarnotas_errores', array(
				'errores'	=>	$mensajemostar));
		
		return redirect()->route('cargarnotas.index');
	}
	public function columnas_excel($CantidadNotas, $CantidadPeriodos){
		$columnas[] = array(
				'name' 			=> 'Numero',
				'background'	=> false,
				'width'			=> 9,
				'letter'		=> 'A',	
				'type'			=> 0
		);
		$columnas[] = array(
				'name' 			=> 'Run',
				'background'	=> false,
				'width'			=> 15,
				'letter'		=> 'B',	
				'type'			=> 0
		);
		$columnas[] = array(
				'name' 			=> 'Nombre Alumno',
				'background'	=> false,
				'width'			=> 30,
				'letter'		=> 'C',	
				'type'			=> 0
		);
		$WidthNotas = 6;
		$pos = 2;
		$notas = 1;
		for ($i = 1; $i <= $CantidadPeriodos; $i++) {
			for ($j = 1; $j <= $CantidadNotas; $j++) {
				$columnas[] = array(
						'name' 			=> 'N'.$notas,
						'background'	=> false,
						'width'			=> $WidthNotas,
						'position'		=> $j + $pos,
						'letter'		=> util::alfabeto($j + $pos),	
						'type'			=> 1
				);
				$notas++;
			}
			$columnas[] = array(
					'name' 			=> 'Prom.'.$i,
					'background'	=> false,
					'width'			=> $WidthNotas + 1,
					'position'		=> $j + $pos,
					'letter'		=> util::alfabeto($j + $pos),	
					'type'			=> 2
			);
			$pos = $pos + $j; 
		}
		$pos++;
		if ($CantidadPeriodos > 1){
			$columnas[] = array(
					'name' 			=> 'Prom. Final',
					'background'	=> false,
					'width'			=> $WidthNotas + 5,
					'letter'		=> util::alfabeto($pos),	
					'type'			=> 3
			);
		}
		return $columnas;		
	}
	
	
}
