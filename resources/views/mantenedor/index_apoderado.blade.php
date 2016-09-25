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
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js"></script>
{!! Html::script('assets/js/jquery-validate.bootstrap-tooltip.js') !!}
{!! Html::script('assets/js/dropdown.js') !!}

	<script type="text/javascript">

		$(document).ready(function() {
			var cur_codigo = $("#hid_cur_codigo").val();
			console.log(cur_codigo);
			if (cur_codigo == -1){
			    $("#add").attr('disabled', true);
			    $("#export").attr('disabled', true);
			    $("#import").attr('disabled', true);
			    
			}
			else {
				url1 = $("#add").attr("href")+'/'+cur_codigo;
				url2 = $("#import").attr("href")+'/import/'+cur_codigo;
				url3 = $("#export").attr("href")+'/export/'+cur_codigo;
				$("#add").attr('href', url1);
				$("#import").attr('href', url2);
				$("#export").attr('href', url3);
			}
			$.get("/students/public/cursos_disponibles", function(response,state){
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
			console.log('1');
			@if (isset($errores))
				var errores = '{!! $errores !!}';
			@else 
				var errores = '';
			@endif
			if (errores !== ''){
				console.log('2');
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
			}
			
		});
		
	</script>

	<div class="{{ $entidad['clase'] }}">
		<div class="panel panel-default" >
			<div class="panel-heading">
	    		<h3 class="panel-title">Asignar {{ $entidad['Nombre'] }}</h3>
	  		</div>
	  		<div class="panel-body">
				<div class="container col-md-6 col-md-offset-3">
					<div class="panel panel-primary" >
						<div class="panel-heading">
				    		<h3 class="panel-title">Curso</h3>
				  		</div>
				  		<div class="panel-body">
							{{ Form::model(Request::all(), array('url' => $entidad['controller'].'/search_curso')) }}
								<div class="col-sm-10">
									<select class="form-control"  id="cur_nombre" name="cur_nombre">
									<!-- Dropdown List Option -->
									</select>
									<input id="hid_cur_codigo" name="hid_cur_codigo" value="{{ $cur_codigo }}" type="hidden">
						  		</div>
								<div class="col-sm-2">
									{{ Form::button('<span class="glyphicon glyphicon-search"></span>', array('class'=>'btn btn-default', 'type'=>'submit')) }}
						  		</div>
							{{ Form::close() }}
				  		</div>
					</div>
				</div>
				<div class="container col-md-12">
					<div class="panel panel-primary" >
						<div class="panel-heading">
				    		<h3 class="panel-title">Alumnos</h3>
				  		</div>
				  		<div class="panel-body">
							<table class="table table-striped table-hover ">
								<thead>
									<tr class="active">
										<th colspan="4" style="width:46%">Alumno</th>
										<th colspan="3" style="width:39%">Apoderado</th>
										<th style="width:15%">&nbsp;</th>
									</tr>
									<tr class="active">
										<th style="width:5%">Numero</th>
										<th style="width:15%">Rut</th>
										<th style="width:16%">Nombre</th>
										<th style="width:16%">A. Paterno</th>
										<th style="width:15%">Rut</th>
										<th style="width:16%">Nombre</th>
										<th style="width:16%">A. Paterno</th>
										<th style="width:1%">Acciones</th>
									</tr>
								</thead>
								<tbody>
								<tr class="warning">
								{{ Form::model(Request::all(), array('url' => $entidad['controller'].'/search', 'class' => 'pull-right')) }}
									@foreach($tablas as $tabla)
										@if ($tabla['filter']!=3)
											<td>
												@if ($tabla['filter']==1)
													<?php 
														if ($tabla['value'] != '') { 
															$mostrar = "value=".$tabla['value'];
														}
														else {
															$mostrar = "placeholder=".$tabla['descripcion'];
														}
														if ($tabla['campo'] == 'per_rut'){
															$campo = $tabla['campo'].'bak';
														}
														else{
															$campo = $tabla['campo'];
														}
													?>
													<input class="form-control" id="{{ $campo }}" name="{{ $campo }}" <?php echo $mostrar; ?> type="text">
												@endif
												@if ($tabla['filter']==0)
													&nbsp;
												@endif
											</td>
										@endif
									@endforeach
									<td align="center">
											{{ Form::button('<span class="glyphicon glyphicon-search"></span>', array('class'=>'btn btn-default', 'type'=>'submit')) }}
											<?php 
												$export = $entidad['controller'];
											?>
											<a id="export" href="<?php echo $export ?>" class="btn btn-default">
													<span class="glyphicon glyphicon-export"></span>
											</a>
									</td>
							    </tr>
								{{ Form::close() }}
									@if (count($records)>0)
										@foreach($records as $record)
											<tr>
												<td><?php echo $record->alu_numero;?></th>
												<td><?php
														$mostrar = util::format_rut($record->alu_rut, $record->alu_dv);
														echo $mostrar['numero'].'-'.$mostrar['dv'];
														?></th>
												<td><?php echo $record->alu_nombre;?></th>
												<td><?php echo $record->alu_apellido_paterno;?></th>
												<td><?php
														if ($record->apo_rut == ''){
															$mostrar = '';
															echo $mostrar;
														}
														else {
															$mostrar = util::format_rut($record->apo_rut, $record->apo_dv);
															echo $mostrar['numero'].'-'.$mostrar['dv'];
														}
														?></th>
												<td><?php echo $record->apo_nombre;?></th>
												<td><?php echo $record->apo_apellido_paterno;?></th>
												<td>
													<div class="btn-toolbar" role="toolbar" style="width: 100px;">
														<div class="btn-group">
															<?php 
																$controller = $entidad['controller'].'/'.$record->alu_rut.'/edit'; 
																if ($privilegio->mas_add == 1){
																	$clase = '';
																}
																else{
																	$clase = "disabled";
																}
															?>
															<a href="<?php echo $controller ?>" class="btn btn-default <?php echo $clase; ?>">
																<span class="glyphicon glyphicon-edit"></span>
															</a>
															{{ Form::open(array('url' => $entidad['controller'].'/'.$record->alu_rut, 'class' => 'pull-right')) }}
																<?php $url = $entidad['controller']; $id = $record->alu_rut;  ?>
																@if ($privilegio->mas_delete == 1)
																	{{ Form::button('<span class="glyphicon glyphicon-remove-circle"></span>', array('class'=>'btn btn-default', 'type'=>'button', 'onclick' => "msg_delete('".$url."', $id); return false;")) }}
																@else												  
																	{{ Form::button('<span class="glyphicon glyphicon-remove-circle"></span>', array('class'=>'btn btn-default', 'disabled' => 'disabled', 'type'=>'submit')) }}
																@endif
											                    {{ Form::hidden('_method', 'DELETE') }}
											                {{ Form::close() }}
														</div>
													</div>
												</td>
											</tr>
										@endforeach
									@else
										<tr class="danger">
										    <td class="text-center" colspan="{{$entidad['col']}}">
										    	La consulta no contiene datos
											</td>								
										</tr>
									@endif
										<tr class="success">
										    <td class="text-center" colspan="{{$entidad['col'] - 1}}">
										    	&nbsp;
											</td>
										    <td class="text-center">
												<?php 
													$import = $entidad['controller'];
													if ($privilegio->mas_add == 1){
														$clase = '';
													}
													else{
														$clase = "disabled";
													}
												?>
												<a id="import" href="<?php echo $import ?>" class="btn btn-default <?php echo $clase; ?>">
														<span class="glyphicon glyphicon-import"></span>
												</a>

											</td>
									    </tr>
										<tr class="active">
										    <td class="text-center pager" colspan="{{$entidad['col']}}">
										    	 {{ $records->render() }}
										    </td>
									    </tr>
			    					</tbody>
							</table>
				  		</div>
					</div>
				</div>
	  		</div>
		</div>
	</div>
@endsection