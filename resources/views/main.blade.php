@extends('layout',['NamePage' => ':: Students ::'])

@section('content')

	<div class="container">

		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="/students/public/main">:: Proyect Students ::</a>
			</div>
			<div id="navbar" class="navbar-collapse collapse">
			</div><!--/.navbar-collapse -->
		</div>

		<div class="container">
			<div class="row">
				<nav class="navbar navbar-default">
					<div class="container-fluid">
				    	<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
							{!! $menu !!}
						</div>
				  	</div>
				</nav>
			</div>
		</div>
	</div>
<div class="container">
  <div class="row" style="height: 400px">
	&nbsp;
  </div>
</div>

	<div class="container fill">
		<div class="panelpanel-default">
			<div class="panel-body">
				<div class="col-md-10">
					San Eugenio 1100, &Ntilde;u&ntilde;oa, Regi&oacute;n Metropolitana, Chile
				</div>
				<div class="col-md-2">
					<a href="http://validator.w3.org/check?uri=referer" rel="external" style="line-height: 1.3em; font-style: normal;" title="HTML versión 5">HTML 5</a>
					<span style="line-height: 1.538em; font-style: normal;">&nbsp;|&nbsp;</span>
					<a href="http://jigsaw.w3.org/css-validator/check/referer/?profile=css3" rel="external" style="line-height: 1.3em; font-style: normal;" title="CSS versión 3">CSS 3</a>
					<span style="line-height: 1.538em; font-style: normal;">&nbsp;|</span>
					<a href="http://creativecommons.org/licenses/by/2.0/cl/" rel="external" style="line-height: 1.3em; font-style: normal;" title="Creative Commons">CC</a>
				</div>
			</div>
		</div>
	</div>




@endsection