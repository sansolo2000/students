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
			if (cur_codigo == -1){
			    $("#add").attr('disabled', true);
			    
			}
			else {
				url = $("#add").attr("href")+'/'+cur_codigo;
				$("#add").attr('href', url);
				console.log(url);				
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
				    		<h3 class="panel-title">Asignaturas</h3>
				  		</div>
				  		<div class="panel-body">
							<table class="table table-striped table-hover ">
								<thead>
									<tr>
										<th style="width:25%">Nombre</th>
										<th style="width:40%">Profesor</th>
										<th style="width:10%">Orden</th>
										<th style="width:10%">Estado</th>
										<th style="width:15%">Acciones</th>
									</tr>
								</thead>
								<tbody>
									@if (count($records)>0)
										@foreach($records as $record)
											<tr>
												@foreach($tablas as $tabla)
													@if ($tabla['filter'] != 3)
														@if ($tabla['tipo'] == 'input' || $tabla['tipo'] == 'select')
															<?php 
																if ($tabla['campo'] == 'per_rut'){
																	$persona = new persona;
																	$persona = Persona::where('personas.per_rut', '=', $record[$tabla['campo']])->first();
																	$mostrar = util::format_rut($persona->per_rut,$persona->per_dv); 
																	$mostrar = $mostrar['numero'].'-'.$mostrar['dv'].' - '.$persona->per_nombre.' '.$persona->per_apellido_paterno;
																}
																else{
																	$mostrar = $record[$tabla['campo']];
																}
															?>
															<td><?php echo $mostrar; ?></td>
														@endif
														@if ($tabla['tipo'] == 'check')
															<td><?php if ($record[$tabla["campo"]] == 1){
																		echo "S&iacute;";
																	}
																	if ($record[$tabla["campo"]] == 0){
																		echo "No";
																	}
																	if ($record[$tabla["campo"]] == 3){
																		echo "N/A";
																	}
																	?></td>
														@endif
													@endif
												@endforeach
												<td>
													<div class="btn-toolbar" role="toolbar" style="width: 100px;">
														<div class="btn-group">
															<?php 
																$controller = $entidad['controller'].'/'.$record[$entidad['pk']].'/edit'; 
																if ($privilegio->mas_edit == 1){
																	$clase = '';
																}
																else{
																	$clase = "disabled";
																}
															?>
															<a href="<?php echo $controller ?>" class="btn btn-default <?php echo $clase; ?>">
																<span class="glyphicon glyphicon-edit"></span>
															</a>
															{{ Form::open(array('url' => $entidad['controller'].'/'.$record[$entidad['pk']], 'class' => 'pull-right')) }}
																<?php $url = $entidad['controller']; $id = $record[$entidad['pk']];  ?>
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
													$controller = $entidad['controller'].'/create';
													if ($privilegio->mas_add == 1){
														$clase = '';
													}
													else{
														$clase = "disabled";
													}
												?>
													<a id="add" href="<?php echo $controller ?>" class="btn btn-default <?php echo $clase; ?>">
															<span class="glyphicon glyphicon-plus-sign"></span>
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