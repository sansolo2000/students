<?php 
use App\models\colegio;
use App\models\modulo_asignado;
use App\helpers\util;
use App\models\permiso_especial;
use App\models\curso;

$colegios = Colegio::select()
			->join('comunas', 'colegios.com_codigo', '=', 'comunas.com_codigo')
			->join('regiones', 'regiones.reg_codigo', '=', 'comunas.reg_codigo')
			->where('colegios.col_activo', '=', 1)
			->first();
if (empty($colegios)){
	$colegios['col_nombre'] = 'Sin definir';
	$colegios['col_direccion'] = 'Sin definir';
	$colegios['com_nombre'] = 'Sin definir';
	$colegios['Fono'] = 'Sin definir';
}
else {
	$colegios['Fono'] = 'Sin definir';
}
?>
@extends('mantenedor.base',[
					'NamePage' => ':: Proyect Students - '.$colegios['col_nombre'].' ::', 
					'Direccion' => $colegios['col_direccion']. ', '. $colegios['com_nombre']
							])


@section('content')

	<script type="text/javascript">
		$().ready(function () {
			$('#mycabecera').validate({
				rules: {
					'cur_nombre'			:	{required: true, min: 1},
					'pri_nombre'			:	{required: true, min: 1}
				},
				messages: {
					'cur_nombre'			: { min: 'Seleccione un curso' },
					'pri_nombre'			: { min: 'Seleccione un periodo' }
				},
			});
		});
	

		$(document).ready(function() {
 			var validator = $('#myform').validate({
			    rules: {
				    'import_file'			: { required: true, extension: "xls"  }
		    	},
			    messages: {
				    'import_file' 			: 'Debe ingresar un archivo xls' 
				},
			});

 			var userid = {{ $user }};
			var curcodigo = {{ $cur_codigo }};
			if (url == null){
				var url = '{{ $entidad['controller'] }}';
			}
			$('#export_all').attr('href', url+'/downloadscore/{{ $cur_codigo }}/'+$("#pri_nombre_2").val());
			$('#import_all').attr('href', url+'/uploadscore/{{ $cur_codigo }}/'+$("#pri_nombre_3").val());
			
			var cur_codigo = $("#hid_cur_codigo").val();
			$("#pri_nombre_2").change(function() {
				$('#export_all').attr('href', url+'/downloadscore/{{ $cur_codigo }}/'+$("#pri_nombre_2").val());
			});
			$.get("/students/public/cursos_disponibles_profesores/"+userid, function(response,state){
				if (response.length == 0){
					BootstrapDialog.alert({
						title: 'Error',
						message: 'No existen profesores para asignar',
						type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
						closable: true, // <-- Default value is false
						draggable: true, // <-- Default value is false
						buttonLabel: 'Volver', // <-- Default value is 'OK',
					});
				}
				else{
					var profesores = []; 
					for (i=0; i<response.length; i++){
						if (cur_codigo == response[i].id){
							$("#cur_nombre").append("<option value='"+response[i].id+"' selected>"+response[i].name+"</option>");
						}
						else{
							$("#cur_nombre").append("<option value='"+response[i].id+"'>"+response[i].name+"</option>");							
						}
					}
					$("#cur_nombre").select2({
					});
				}
			});

			$( "#download" ).click(function( event ) {
				event.preventDefault();
				event.stopPropagation();
				$('#myModalLibroExport').modal('show')
				
			});
 			$( "#export_all" ).click(function( event ) {
 				$('#myModalLibroExport').modal('toggle');
			});
 			$('#myModalLibroExport').on('shown.bs.modal', function (evt) {
				evt.preventDefault();
				evt.stopPropagation();			
				$('#myModalLibroExport').focus()
			})

			
			$( "#upload" ).click(function( event ) {
				event.preventDefault();
				event.stopPropagation();
				$('#myModalLibroImport').modal('show')
			});
 			$( "#import_all" ).click(function( event ) {
 				$('#myModalLibroImport').modal('toggle');
			});
 			$('#myModalLibroImport').on('shown.bs.modal', function (evt) {
				evt.preventDefault();
				evt.stopPropagation();			
				$('#myModalLibroImport').focus()
			})
			console.log(1);
			@if (isset($errores))
				var errores = '{!! $errores !!}';
				console.log(errores);
			@else 
				var errores = '';
			@endif
			console.log('Errores: '+errores);
			console.log(2);
			if (errores !== ''){
				BootstrapDialog.alert({
			            title: 'Error',
			            message: errores,
			            type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
			            closable: true, // <-- Default value is false
			            draggable: true, // <-- Default value is false
			            buttonLabel: 'Volver', // <-- Default value is 'OK',
			            //callback: function(result) {
			                // result will be true if button was click, while it will be false if users close the dialog directly.
			                //alert('Result is: ' + result);
			            //}
			        });
				console.log(3);
			}
			
			{!! $script !!}
		});


		
		
		
	</script>


<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet" />
{!! Html::style('assets/css/personal.css') !!}

<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js"></script>
{!! Html::script('assets/js/jquery-validate.bootstrap-tooltip.js') !!}
{!! Html::script('assets/js/dropdown.js') !!}


	<div class="{{ $entidad['clase'] }}">
		<div class="panel panel-default" >
			<div class="panel-heading">
	    		<h3 class="panel-title">{{ $entidad['Nombre'] }}</h3>
	  		</div>
	  		<div class="panel-body">
				<div class="container col-md-12">
					<div class="container col-md-3 center_pers">
						<?php 
							if ($cur_codigo != -1){
								$clase = '';
							}
							else{
								$clase = "disabled";
							}
						?>
						
						<a href="downloadscore/{{ $cur_codigo }}" class="btn btn-primary <?php echo $clase; ?>" id="download">Bajar Notas</a>
			  		</div>
					<div class="container col-md-6">
						<div class="panel panel-primary" >
							<div class="panel-heading">
					    		<h3 class="panel-title">Curso</h3>
					  		</div>
					  		<div class="panel-body">
								{{ Form::model(Request::all(), array('url' => $entidad['controller'].'/search_curso', 'id' => 'mycabecera', 'name' =>'mycabecera')) }}
									<div class="form-group col-sm-12">
										<div class="col-sm-2">
											Curso:
								  		</div>
										<div class="col-sm-8">
											<select class="form-control"  id="cur_nombre" name="cur_nombre">
											<!-- Dropdown List Option -->
											</select>
											<input id="hid_cur_codigo" name="hid_cur_codigo" value="{{ $cur_codigo }}" type="hidden">
								  		</div>
										<div class="col-sm-2">
											&nbsp;
								  		</div>
							  		</div>
									<div class="form-group col-sm-12">
										<div class="col-sm-2">
											Periodo:
								  		</div>
										<div class="col-sm-6">
											{{ Form::select('pri_nombre', $periodo, $pri_codigo, ['id' => 'pri_nombre', 'class' => 'form-control', 'name' => 'pri_nombre' ]) }}								
								  		</div>
										<div class="col-sm-2">
											&nbsp;
								  		</div>
										<div class="col-sm-2">
											{{ Form::button('<span class="glyphicon glyphicon-search"></span>', array('class'=>'btn btn-default', 'type'=>'submit')) }}
								  		</div>
							  		</div>
									{{ Form::close() }}
								<div class="form-group col-sm-12">
									<div class="col-sm-4">
										<label for="curso" class="control-label">Profesor Jefe:</label>
									</div>
									<div class="col-sm-8">
										<input class="form-control" id="profesor" name="profesor" placeholder="Profesor Jefe" type="text" disabled="disabled" value="{{ $profesor['profesor'] }}">
									</div>
								</div>
					  		</div>
					  		
						</div>
					</div>
					<div class="container col-md-3 center_pers">
						<?php 
							if ($cur_codigo != -1){
								$clase = '';
							}
							else{
								$clase = "disabled";
							}
						?>
						<a href="uploadscore/{{ $cur_codigo }}" class="btn btn-primary <?php echo $clase; ?>"  id="upload">Subir Notas</a>
			  		</div>
		  		</div>
				<div class="container col-md-12">
					<div class="panel panel-primary" >
						<div class="panel-heading">
				    		<h3 class="panel-title">Asignaturas</h3>
				  		</div>
				  		<div class="panel-body">
				  			{!! $cabecera !!}
				  			{!! $cuerpo !!}
				  		</div>
			  		</div>
		  		</div>
	  		</div>
		</div>
	</div>



<div class="modal fade" id="myModalLibroExport" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Exportar libro de clase a Excel</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-4">
						<label for="curso" class="control-label">Periodos:</label>
					</div>
					<div class="col-sm-4">
						{{ Form::select('pri_nombre_2', $periodo2, 0, ['id' => 'pri_nombre_2', 'class' => 'form-control', 'name' => 'pri_nombre_2' ]) }}								
					</div>
					<div class="col-sm-4">
						&nbsp;
					</div>
				</div>
				
			</div>
			<div class="modal-footer">
				<input type="hidden" value="1" id="codigo">
				<button type="button" class="btn btn-default" data-dismiss="modal">Volver</button>
				<a class="btn btn-primary" id="export_all">Exportar</a>
<!-- 				<button type="button" class="btn btn-primary" id="export">Exportar</button> -->
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="myModalLibroImport" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			{{ Form::model('Curso', array('route' => array('cargarnotas/uploadscore'), 'method' => 'POST', 'files' => true, 'class' => 'form-horizontal', 'id' => 'myform', 'name' =>'myform')) }}
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel">Importar libro de clase a Excel</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-sm-4">
							<label for="curso" class="control-label">Archivo:</label>
						</div>
						<div class="col-sm-8">
							{{ Form::file('import_file', ['class' => 'form-control']) }}
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Volver</button>
					<input type="hidden" value="{{$cur_codigo}}" id="curso" name="curso">
				    {{ Form::button('Importar Notas', array('class'=>'btn btn-primary', 'type'=>'submit')) }}    
				</div>
			{{ Form::close() }}
		</div>
	</div>
</div>

{!! $modal !!}

@endsection