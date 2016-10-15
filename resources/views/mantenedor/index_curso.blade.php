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
{!! Html::script('assets/js/dropdown.js') !!}

	<div class="{{ $entidad['clase'] }}">
		<div class="panel panel-default">
			<div class="panel-heading">
	    		<h3 class="panel-title">{{ $entidad['Nombre'] }}</h3>
	  		</div>
	  		<div class="panel-body">
				<table class="table table-striped table-hover ">
					<thead>
						<tr>
							<th style="width:10%">Numero</th>
							<th style="width:10%">Letra</th>
							<th style="width:33%">Profesor</th>
							<th style="width:10%">Numero</th>
							<th style="width:12%">A&ntilde;o</th>
							<th style="width:10%">Estado</th>
							<th style="width:15%">Acciones</th>
						</tr>
					</thead>
					<tbody>
							{{ Form::model(Request::all(), array('url' => $entidad['controller'].'/search', 'class' => 'pull-right')) }}
							<tr class="warning">
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
									
									</td>
						    </tr>
								                {{ Form::close() }}

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
									<a href="<?php echo $controller ?>" class="btn btn-default <?php echo $clase; ?>">
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
@endsection