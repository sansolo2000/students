<?php
use App\helpers\util;
use App\models\colegio;
use App\models\persona;

$colegios = Colegio::select()
->join('comunas', 'colegios.com_codigo', '=', 'comunas.com_codigo')
->join('regiones', 'regiones.reg_codigo', '=', 'comunas.reg_codigo')
->where('colegios.col_activo', '=', 1)
->first();
if (empty($colegios)){
	$colegios['col_nombre'] = 'Sin definir';
	$colegios['col_direccion'] = 'Sin definir';
	$colegios['com_nombre'] = 'Sin definir';
	$colegios['col_logo'] = 'Sin definir';
	$colegios['Fono'] = 'Sin definir';
}
else {
	$colegio['Fono'] = 'Sin definir';
}


?>

@extends('mantenedor.base',[
					'NamePage' 	=> ':: Proyect Students - '.$colegios['col_nombre'].' ::', 
					'Direccion' => $colegios['col_direccion']. ', '. $colegios['com_nombre'],
					'imagen'	=> $colegios['col_logo']
							])
@section('content')
<style type="text/css">
	.data-table{
		width:100%; 
		overflow-x: scroll; 
		overflow-y:hidden; }
</style>


<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js"></script>
{!! Html::script('assets/js/jquery-validate.bootstrap-tooltip.js') !!}
{!! Html::script('assets/js/dropdown.js') !!}

	<script type="text/javascript">

		$(document).ready(function() {
			function alumno_nombre(vCurCodigo){
				$.get("/students/public/alumnos_mostrar" + "/" + per_rut + "/" + vCurCodigo+"", function(response,state){
					if (response.length == 0){
						BootstrapDialog.alert({
							title: 'Error',
							message: 'No existe alumnos para mostrar',
							type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
							closable: true, // <-- Default value is false
							draggable: true, // <-- Default value is false
							buttonLabel: 'Volver', // <-- Default value is 'OK',
						});
						alu_nombre.value=-1;
						cur_nombre.value=-1;
					}
					for (i=0; i<response.length; i++){
						if (response.length == 2){
							$("#alu_nombre").append("<option value='"+response[i].id+"' selected>"+response[i].name+"</option>");
						}
						else {
							$("#alu_nombre").append("<option value='"+response[i].id+"'>"+response[i].name+"</option>");							
						}								
					}
					$("#alu_nombre").select2({
					});
					if (url == null){
						var url = '{{ $entidad['controller'] }}';
					}
					$('#btnSub').attr('src',url+'/assets/img/pdf_dis.png');
					$('#btnSub').prop( "disabled", true );
					$( ".Grilla" ).empty();	
					if (response.length == 2){
						$("#alu_nombre").prop( "disabled", true );
						notas_mostrar(response[1].id);
					}
					
				});
			}

			function notas_mostrar(vPer_Rut){
				$.get("/students/public/notas_mostrar" + "/" + per_rut + "/" + vPer_Rut + "/" + cur_nombre.value+"", function(response,state){
					if (response.length == 0){
						BootstrapDialog.alert({
							title: 'Error',
							message: 'No existe notas de mostrar',
							type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
							closable: true, // <-- Default value is false
							draggable: true, // <-- Default value is false
							buttonLabel: 'Volver', // <-- Default value is 'OK',
						});
						alu_nombre.value=-1;
					}
					else {
						$( ".Grilla" ).empty();	
						if (url == null){
							var url = '{{ $entidad['controller'] }}';
						}
						cantidadperiodo = response.columnas.cantidadperiodo;
						cantidadnotas = response.columnas.cantidadnotas;
						width = response.columnas.width;
						existencalificaciones = response.columnas.exitencalificaciones;
						promedio_periodos = [];
						promedio_asignaturas = [];
						asignatura = response.notas;
						if (existencalificaciones){
							$('#btnSub').attr('src',url+'/assets/img/pdf.png');
							$('#btnSub').prop( "disabled", false );
							$('#myform').attr('action', url+'export_pdf/'+vPer_Rut);
							console.log(2);
							mostrar = '<div class="container col-md-12">';
							mostrar += ' <div class="panel panel-primary" >';
							mostrar += ' 	<div class="panel-heading">';
							mostrar += ' 		<h3 class="panel-title">Notas alumnos</h3>';
							mostrar += ' 	</div>';
							mostrar += ' 	<div class="panel-body data-table">';
							mostrar += ' 		<table class="table table-striped table-hover table-bordered data-table">';
							mostrar += ' 			<thead>';
							mostrar += ' 				<tr class="active">';
							mostrar += ' 					<th style="width:10%">Asignatura</th>';
							for (j = 1; j <= cantidadperiodo; j++){
								for (i = 1; i <= cantidadnotas; i++){
									mostrar += ' 					<th style="width:'+width+'%">N'+i+'</th>';
								}
								mostrar += ' 					<th style="width:'+width+'%">Pr'+j+'</th>';
							}
							mostrar += ' 					<th style="width:'+width+'%">Pr.final</th>';
							mostrar += ' 				</tr>';
							mostrar += ' 			</thead>';
							mostrar += ' 			<tbody>';
							var asignaturas = []
							for (j=0; j<Object.keys(asignatura).length; j++){
								periodo = asignatura[j];
								mostrar += ' 				<tr>';
								mostrar += '		 			<td><strong>'+asignatura[j][0].asg_nombre+'</strong></td>';
								asg_suma = 0
								asg_cantidad = 0;
								for (i=0; i<Object.keys(periodo).length; i++){
									calificaciones = periodo[i][0];
									per_cantidad = 0;
									per_suma = 0;
									for (k=0; k<Object.keys(calificaciones).length; k++){
										if (calificaciones[k].nota == 'X'){
											mostrar += '		 			<td>&nbsp;</td>';
										}
										else{
											calificacion = calificaciones[k].nota.toFixed(1)
											mostrar += '		 			<td>'+calificacion.replace('.', ',')+'</td>';
											per_suma = per_suma + calificaciones[k].nota;
											per_cantidad++;
										}
									}
									if (per_cantidad > 0){
										//per_promedio = precise_round(per_suma / per_cantidad, 1);
										per_promedio = per_suma / per_cantidad;
										mostrar += '		 			<td>'+per_promedio.toFixed(1).replace('.', ',')+'</td>';
									}
									else {
										pre_promedio = 0; 
										mostrar += '		 			<td>&nbsp;</td>';
									}
									if (per_cantidad > 0){
										asg_suma = asg_suma + per_promedio;
										promedio_asignaturas[i] = per_promedio.toFixed(1); 
										per_suma = 0;
										asg_cantidad++;
									}
									
								}
								if (asg_cantidad > 0){
									//asg_promedio = precise_round(asg_suma / asg_cantidad, 1);
									asg_promedio = asg_suma / asg_cantidad;
									promedio_periodos[j] = promedio_asignaturas;
									promedio_asignaturas = [];
									mostrar += '		 			<td>'+asg_promedio.toFixed(1).replace('.', ',')+'</td>';
								}
								else {
									asg_promedio = 0; 
									mostrar += '		 			<td>&nbsp;</td>';
								}
								if (asg_cantidad > 0){
									asg_suma = asg_suma + asg_promedio;
									asg_suma = 0;
									asg_cantidad++;
								}
								
								mostrar += ' 				</tr>';
								
							}
							//$notas =  response.notas.0.asg_nombre
							promedio_final = [];
							indice = cantidadperiodo;
							cantidad_final = [];
							for (j=0; j<Object.keys(promedio_periodos).length; j++){
								for (i=0; i<Object.keys(promedio_periodos[j]).length; i++){
									vara = parseFloat(promedio_periodos[j][i]);
									varb = parseFloat(promedio_final[i]) || 0;
									total = vara + varb;
									promedio_final[i] = parseFloat(total).toFixed(1);
									varc =  parseInt(cantidad_final[i], 10) || 0;
									varc++;
									cantidad_final[i] = varc;
								}
							}
							mostrar += ' 				<tr class="success">';
							mostrar += '		 			<td><strong>Promedio</strong></td>';
							cantidad_total = 0;
							promedio_total = 0;
							for (j=0; j<cantidadperiodo; j++){
								total = 0;
								mostrar += '		 			<td colspan="'+cantidadnotas+'">&nbsp;</td>';
								if (promedio_final[j] > 0) {
									total = promedio_final[j] / cantidad_final[j];
									promedio_total = promedio_total + parseFloat(total.toFixed(1));
									cantidad_total++;
									mostrar += '		 			<td><strong>'+total.toFixed(1)+'</strong></td>';
								}
								 
							}
							promedio_total = promedio_total / cantidad_total 
							mostrar += '		 			<td><strong>'+promedio_total.toFixed(1)+'</strong></td>';
							mostrar += ' 				</tr>';
							mostrar += ' 		    </tbody>';
							mostrar += ' 		</table>';
							mostrar += ' 	</div>';
							mostrar += ' </div>';
							mostrar += '</div>';
						}
						else{
							mostrar = '<div class="container col-md-4 col-md-offset-4">';
							mostrar += '	<div class="alert alert-dismissible alert-danger">';
							mostrar += '			No hay notas cargadas.';
							mostrar += '	</div>';
							mostrar += '</div>';
							$('#btnSub').attr('src','assets/img/pdf_dis.png');
							$('#btnSub').prop( "disabled", true );
							$( ".Grilla" ).empty();	
						}	
						$( ".Grilla" ).append( mostrar );	
					}
				});
			}		

			var per_rut = $("#hid_per_rut").val();
			var cur_codigo = $("#hid_cur_codigo").val();
			
			$.get("/students/public/cursos_mostrar" + "/" + per_rut, function(response,state){
				console.log(response.length);
				if (response.length == 1){
					BootstrapDialog.alert({
						title: 'Error',
						message: 'No existe curso a mostrar',
						type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
						closable: true, // <-- Default value is false
						draggable: true, // <-- Default value is false
						buttonLabel: 'Volver', // <-- Default value is 'OK',
					});
					$("#cur_nombre").attr('disabled','disabled');
					$("#alu_nombre").attr('disabled','disabled');
				}
				else{
					var profesores = []; 
					for (i=0; i<response.length; i++){
						if (cur_codigo == response[i].id || response.length == 2){
							$("#cur_nombre").append("<option value='"+response[i].id+"' selected>"+response[i].name+"</option>");
						}
						else{
							$("#cur_nombre").append("<option value='"+response[i].id+"'>"+response[i].name+"</option>");							
						}
					}
					$("#cur_nombre").select2({
					});
					if (response.length == 2){
						$("#cur_nombre").attr('disabled','disabled');
						alumno_nombre(response[1].id);	
					}
				}
			});

			$("#cur_nombre").change(function(event){
				removeOptions(document.getElementById("alu_nombre"));
				if (event.target.value == -1){
					$('#btnSub').attr('src','assets/img/pdf_dis.png');
					$('#btnSub').prop( "disabled", true );
					$( ".Grilla" ).empty();	
				}
				else{					
					alumno_nombre(event.target.value);	
				}	
			});

			$("#alu_nombre").change(function(event){
				//removeOptions(document.getElementById("alu_nombre"));
				if (event.target.value == -1){
					if (url == null){
						var url = '{{ $entidad['controller'] }}';
					}
					console.log('url');
					$('#btnSub').attr('src',url+'/assets/img/pdf_dis.png');
					$('#btnSub').prop( "disabled", true );
					$( ".Grilla" ).empty();	
				}
				else{					
					notas_mostrar(event.target.value);
				}				
			});
		});
		
				
	</script>

	<div class="{{ $entidad['clase'] }}">
		<div class="panel panel-default" >
			<div class="panel-heading">
	    		<h3 class="panel-title">Ver notas del alumno</h3>
	  		</div>
	  		<div class="panel-body">
				<div class="container col-md-8 col-md-offset-2">
					<div class="panel panel-primary" >
						<div class="panel-heading">
				    		<h3 class="panel-title">Selecionar curso y alumnos</h3>
				  		</div>
				  		<div class="panel-body">
							{{ Form::model(Request::all(), array('url' => $entidad['controller'].'search_curso', 'id' => 'myform', 'name' =>'myform')) }}
								<div class="form-group col-sm-10">
									<div class="form-group col-sm-12">
										<div class="col-sm-2">
											Curso:
										</div>
										<div class="col-sm-10">
											<select class="form-control"  id="cur_nombre" name="cur_nombre">
											<!-- Dropdown List Option -->
											</select>
											<input id="hid_per_rut" name="hid_per_rut" value="{{ $id_usuario }}" type="hidden">
											<input id="hid_cur_codigo" name="hid_cur_codigo" value="{{ $cur_codigo }}" type="hidden">
								  		</div>
								  	</div>
									<div class="form-group col-sm-12">
										<div class="col-sm-2">
											Alumno:
										</div>
										<div class="col-sm-10">
											<select class="form-control"  id="alu_nombre" name="alu_nombre">
											<!-- Dropdown List Option -->
											</select>
								  		</div>
								  	</div>
							  	</div>
								<div class="form-group col-sm-2" style="margin: auto auto">
									{{ Form::image('assets/img/pdf_dis.png', 'btnSub', ['id' => 'btnSub', 'disabled' => 'disabled']) }}
								</div>
							{{ Form::close() }}
				  		</div>
					</div>
				</div>
				<div class="Grilla" >
				</div>
	  		</div>
		</div>
	</div>
@endsection