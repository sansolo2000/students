<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Dompdf\Dompdf;
use Dompdf\Options;

use App\Http\Requests;
use App\models\persona;
use App\models\curso;
use DB;
use App\helpers\util;
use App\models\anyo;

class ExportPDFController extends Controller
{
	public function ExportPDF($Id)
	{
		$data = $this->getData();
		$date = date('d-m-Y');
		$alumno = $this->getAlumno($Id);
		$anyo = anyo::where('any_activo', '=', 1)->first();
		$anyo_vigente = $anyo->any_numero;
		
		
		
		$idusuario = Auth::user()->per_rut;
		
		
		$notas = VerNotasController::notas_mostrar_get($idusuario, $Id, $alumno['cur_codigo']);
		
		$mostrar = $this->mostrar_notas($notas);
		
		$view =  view('pdf.exportPDF', compact('data', 'date', 'alumno', 'anyo_vigente', 'mostrar'))->render();
		
		$options = new Options();
		$options->set(array('isHtml5ParserEnabled' => 1, 'isRemoteEnabled' => 1));
		
		$dompdf = new Dompdf($options);
		
		$dompdf->load_html($view);
		
		
		// Render the HTML as PDF
		$dompdf->render();
		
		// Output the generated PDF to Browser
		$dompdf->stream('InformeNotas-'.$Id);
		
		
		$pdf->download($Id.'.pdf');
		//return PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('pdf.exportPDF')->stream();
		//return view('pdf.exportPDF')->with('data', $data)->with('date', $date)->with('alumno', $alumno)->with('anyo_vigente', $anyo_vigente)->with('mostrar', $mostrar);
	}
	
		
	public function getAlumno($Id){
		$persona = curso::select(DB::raw('pr.per_rut, pr.per_dv, pr.per_nombre, pr.per_apellido_paterno, pr.per_apellido_materno, CONCAT(cursos.cur_numero, "&deg;", cursos.cur_letra, " ", niveles.niv_nombre) as name, al.alu_numero, CONCAT(ppr.per_nombre," ", ppr.per_apellido_paterno," ", ppr.per_apellido_materno) as profesor, cursos.cur_codigo,  pro.pro_horario, pro.pro_logo'))
		->join('profesores as pro', 'cursos.pro_codigo', '=', 'pro.pro_codigo')
		->join('personas AS ppr', DB::raw('ppr.per_rut'), '=', 'pro.per_rut')
		->join('niveles', 'cursos.niv_codigo', '=', 'niveles.niv_codigo')
		->join('alumnos as al', 'cursos.cur_codigo', '=', DB::raw('al.cur_codigo'))
		->join('personas AS pr', DB::raw('pr.per_rut'), '=', 'al.per_rut')
		->where('cursos.cur_activo', '=', 1)
		->where('pr.per_rut', '=', $Id)
		->first();
		
		$rut = util::format_rut($persona->per_rut, $persona->per_dv);
		$rut_alumno = $rut['numero']. '-' . $rut['dv'];
		$alumno = [ 'rut_alumno'	=> $rut_alumno,
				'nombre' 			=> $persona->per_nombre,
				'apellido_paterno' 	=> $persona->per_apellido_paterno,
				'apellido_materno' 	=> $persona->per_apellido_materno,
				'curso'				=> $persona->name,
				'numero_lista'		=> $persona->alu_numero,
				'profesor'			=> $persona->profesor,
				'cur_codigo'		=> $persona->cur_codigo,
				'pro_horario'		=> $persona->pro_horario,
				'pro_logo'			=> $persona->pro_logo
		];
		return $alumno;
	}
		
	public function getData()
	{
		$data =  [
				'quantity'      => '1' ,
				'description'   => 'some ramdom text',
				'price'   => '500',
				'total'     => '500'
		];
		return $data;
	}
	
	public function mostrar_notas($notas){
		$cantidadperiodo = $notas['columnas']['cantidadperiodo'];
		$cantidadnotas = $notas['columnas']['cantidadnotas'];
		$width = $notas['columnas']['width'];
		$width_final = $notas['columnas']['width_final'];
		$promedio_periodos = [];
		$promedio_asignaturas = [];
		$asignatura = $notas['notas'];
		$mostrar = ' 		<table class="calificaciones" style="width:100%">';
		$mostrar .= ' 			<thead>';
		$mostrar .= ' 				<tr class="active">';
		$mostrar .= ' 					<th style="width:20%">Asignatura</th>';
		for ($j = 1; $j <= $cantidadperiodo; $j++){
			for ($i = 1; $i <= $cantidadnotas; $i++){
				$mostrar .= ' 					<th style="width:'.$width.'%">N'.$i.'</th>';
			}
			$mostrar .= ' 					<th style="width:'.$width.'%">Pr'.$j.'</th>';
		}
		$mostrar .= ' 					<th style="width:'.$width_final.'%">Pr.final</th>';
		$mostrar .= ' 				</tr>';
		$mostrar .= ' 			</thead>';
		$mostrar .= ' 			<tbody>';
		$asignaturas = [];
		for ($j=0; $j < count($asignatura); $j++){
			$periodo = $asignatura[$j];
			$mostrar .= ' 				<tr>';
			$mostrar .= '		 			<td class="grueso"><strong>'.$asignatura[$j][0]['asg_nombre'].'</strong></td>';
			$asg_suma = 0;
			$asg_cantidad = 0;
			for ($i=0; $i<count($periodo); $i++){
				$calificaciones = $periodo[$i][0];
				$per_cantidad = 0;
				$per_suma = 0;
				for ($k=0; $k<count($calificaciones); $k++){
					if ($calificaciones[$k]['nota'] == 'X'){
						$mostrar .= '		 			<td class="delgado">&nbsp;</td>';
					}
					else{
						$calificacion = round($calificaciones[$k]['nota'], 1);
						$mostrar .= '		 			<td class="delgado">'.number_format($calificacion, 1, ',', ' ').'</td>';
						$per_suma = $per_suma + $calificaciones[$k]['nota'];
						$per_cantidad++;
					}
				}
				if ($per_cantidad > 0){
					//per_promedio = precise_round(per_suma / per_cantidad, 1);
					$per_promedio = round($per_suma / $per_cantidad,1);
					$mostrar .= '		 			<td class="grueso">'.number_format($per_promedio, 1, ',', ' ').'</td>';
				}
				else {
					$pre_promedio = 0;
					$mostrar .= '		 			<td class="grueso">&nbsp;</td>';
				}
				if ($per_cantidad > 0){
					$asg_suma = $asg_suma + $per_promedio;
					$promedio_asignaturas[$i] = round($per_promedio, 1);
					$per_suma = 0;
					$asg_cantidad++;
				}
					
			}
			if ($asg_cantidad > 0){
				//asg_promedio = precise_round(asg_suma / asg_cantidad, 1);
				$asg_promedio = round($asg_suma / $asg_cantidad, 1);
				$promedio_periodos[$j] = $promedio_asignaturas;
				$promedio_asignaturas = [];
				$mostrar .= '		 			<td class="grueso" align="center">'.number_format($asg_promedio, 1, ',', ' ').'</td>';
			}
			else {
				$asg_promedio = 0;
				$mostrar .= '		 			<td class="grueso">&nbsp;</td>';
			}
			if ($asg_cantidad > 0){
				$asg_suma = $asg_suma + $asg_promedio;
				$asg_suma = 0;
				$asg_cantidad++;
			}
		
			$mostrar .= ' 				</tr>';
		
		}
		//$notas =  response.notas.0.asg_nombre
		$promedio_final[0] = 0;
		$indice = $cantidadperiodo;
		$cantidad_final[0] = 0;
		for ($j=0; $j<count($promedio_periodos); $j++){
			for ($i=0; $i<count($promedio_periodos[$j]); $i++){
				$vara = $promedio_periodos[$j][$i];
				$varb = (empty($promedio_final[$i-1]))?0:$promedio_final[$i-1];
				$total = $vara + $varb;
				$promedio_final[$i] = round($total, 1);
				$cantidad_final[$i] = $i+1;
			}
		}
		for ($i = count($asignatura); $i<20; $i++){
			$mostrar .= ' 				<tr>';
			$mostrar .= '		 			<td class="grueso">&nbsp;</td>';
			for ($k = 0; $k < $cantidadperiodo; $k++){
				for ($j = 0; $j<$cantidadnotas; $j++){
					$mostrar .= '		 			<td class="delgado">&nbsp;</td>';
				}
				$mostrar .= '		 			<td class="grueso">&nbsp;</td>';
			}
			$mostrar .= '		 			<td class="grueso">&nbsp;</td>';
			$mostrar .= ' 				</tr>';
		}
		
		$mostrar .= ' 				<tr class="success">';
		$mostrar .= '		 			<td  class="grueso"><strong>Promedio</strong></td>';
		$cantidad_total = 0;
		$promedio_total = 0;
		for ($j=0; $j<$cantidadperiodo; $j++){
			$total = 0;
			$mostrar .= '		 			<td class="grueso" colspan="'.$cantidadnotas.'">&nbsp;</td>';
			if (isset($promedio_final[$j])) {
				if ($promedio_final[$j] > 0) {
					$total = round($promedio_final[$j] / $cantidad_final[$j], 1);
					$promedio_total = $promedio_total + round($total,1);
					$cantidad_total++;
					$mostrar .= '		 			<td class="grueso" align="center"><strong>'.number_format($total, 1, ',', ' ').'</strong></td>';
				}
			}
			else{
				$mostrar .= '		 			<td class="grueso"><strong>&nbsp;</strong></td>';
			}	
		}
		$promedio_total = round($promedio_total / $cantidad_total, 1);
		
		$mostrar .= '		 			<td class="grueso" align="center"><strong>'.number_format($promedio_total, 1, ',', ' ').'</strong></td>';
		$mostrar .= ' 				</tr>';
		$mostrar .= ' 		    </tbody>';
		$mostrar .= ' 		</table>';
		$mostrar .= ' 	</div>';
		$mostrar .= ' </div>';
		$mostrar .= '</div>';
		return $mostrar;
	}
}
