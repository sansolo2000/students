function precise_round(num, decimals) {
    var parts = num;
    var hasMinus = parts.length > 0 && parts[0].length > 0 && parts[0].charAt(0) == '-';
    var integralPart = parts.length == 0 ? '0' : (hasMinus ? parts[0].substr(1) : parts[0]);
    var decimalPart = parts.length > 1 ? parts[1] : '';
    if (decimalPart.length > decimals) {
        var roundOffNumber = decimalPart.charAt(decimals);
        decimalPart = decimalPart.substr(0, decimals);
        if ('56789'.indexOf(roundOffNumber) > -1) {
            var numbers = integralPart + decimalPart;
            var i = numbers.length;
            var trailingZeroes = '';
            var justOneAndTrailingZeroes = true;
            do {
                i--;
                var roundedNumber = '1234567890'.charAt(parseInt(numbers.charAt(i)));
                if (roundedNumber === '0') {
                    trailingZeroes += '0';
                } else {
                    numbers = numbers.substr(0, i) + roundedNumber + trailingZeroes;
                    justOneAndTrailingZeroes = false;
                    break;
                }
            } while (i > 0);
            if (justOneAndTrailingZeroes) {
                numbers = '1' + trailingZeroes;
            }
            integralPart = numbers.substr(0, numbers.length - decimals);
            decimalPart = numbers.substr(numbers.length - decimals);
        }
    } else {
        for (var i = decimalPart.length; i < decimals; i++) {
            decimalPart += '0';
        }
    }
    return (hasMinus ? '-' : '') + integralPart + (decimals > 0 ? '.' + decimalPart : '');
}


function removeOptions(selectbox)
{
    var i;
    for(i = selectbox.options.length - 1 ; i >= 0 ; i--)
    {
        selectbox.remove(i);
    }
}
//using the function:

function msg_delete(url, id){
	switch (url) { 
		case '//localhost/students/public/anyos': 
			mensaje = 'Se eliminar&aacute;n los datos del a&ntilde;o en: Asignaturas, Niveles y Periodos. Esta seguro que desea eliminar la informaci&oacute;n?';
			break;
		default:
			mensaje = 'Esta seguro que desea eliminar la informaci&oacute;n?';
	}
	console.log(mensaje);
	console.log(url);
	BootstrapDialog.confirm({
		title: 'Precaucion',
		message: mensaje,
		type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
		closable: true, // <-- Default value is false
		draggable: true, // <-- Default value is false
		btnCancelLabel: 'Cancelar', // <-- Default value is 'Cancel',
		btnOKLabel: 'Continuar', // <-- Default value is 'OK',
		btnOKClass: 'btn-warning', // <-- If you didn't specify it, dialog type will be used,
		callback: function(result) {
			if(result) {
				$.ajax(
					{
						url		: url+"/"+id,
						type	: 'POST',
						data	: {	_method : "DELETE",
						_token 	: $('input[name="_token"]').val() 
					},
					error: function(status){
						console.log(status);
					}, 
					success: function(result) {
						location.reload();
					}
				});
			}
		}
	});
}

function msg_retirar(url, id){
	 BootstrapDialog.confirm({
        title: 'Precaucion',
        message: 'Esta seguro que desea retirar al alumno?',
        type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
        closable: true, // <-- Default value is false
        draggable: true, // <-- Default value is false
        btnCancelLabel: 'Cancelar', // <-- Default value is 'Cancel',
        btnOKLabel: 'Continuar', // <-- Default value is 'OK',
        btnOKClass: 'btn-warning', // <-- If you didn't specify it, dialog type will be used,
        callback: function(result) {
            // result will be true if button was click, while it will be false if users close the dialog directly.
            if(result) {
           	 $.ajax({
           		    url: url+"/retirar/"+id,
           		    type: 'POST',
        		    data: {	_token 	: $('input[name="_token"]').val() 
        		    },
           		    error: function(status){
           		    	console.log(status);
           		    }, 
           		    success: function(result) {
           		        // Do something with the result
           		    	location.reload();
           		    }
           		});
            }
        }
    });

}

$(document).ready(function() {
	$("#apl_nombre").change(function(event){
		if (rol_nombre.value == -1){
			  BootstrapDialog.alert({
		            title: 'Error',
		            message: 'Debe seleccionar un rol',
		            type: BootstrapDialog.TYPE_DANGER, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
		            closable: true, // <-- Default value is false
		            draggable: true, // <-- Default value is false
		            buttonLabel: 'Volver', // <-- Default value is 'OK',
		        });
			  event.target.value = -1;
		}
		else{
			removeOptions(document.getElementById("mod_nombre"));
			$("#mod_nombre").append("<option value='-1'>::Seleccionar::</option>");
			console.log(event);
			$.get("modulo/"+event.target.value+"/"+rol_nombre.value+"", function(response,state){
				console.log(event);
				if (response.length == 0){
					BootstrapDialog.alert({
						title: 'Error',
						message: 'No existe modulos asignables de esta aplicación para este rol',
						type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
						closable: true, // <-- Default value is false
						draggable: true, // <-- Default value is false
						buttonLabel: 'Volver', // <-- Default value is 'OK',
					});
					mod_nombre.value=-1;
					apl_nombre.value=-1;
					rol_nombre.value=-1;
					
				}
				for (i=0; i<response.length; i++){
					$("#mod_nombre").append("<option value='"+response[i].mod_codigo+"'>"+response[i].mod_nombre+"</option>");
				}
				
			});
		}
	});

	$("#reg_nombre").change(function(event){
		removeOptions(document.getElementById("com_nombre"));
		$("#com_nombre").append("<option value='-1'>::Seleccionar::</option>");
		console.log(event);
		$.get("comuna/"+event.target.value+"", function(response,state){
			if (response.length == 0){
				BootstrapDialog.alert({
					title: 'Error',
					message: 'No existe comunas para esta región',
					type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
					closable: true, // <-- Default value is false
					draggable: true, // <-- Default value is false
					buttonLabel: 'Volver', // <-- Default value is 'OK',
				});
				reg_nombre.value=-1;
				com_nombre.value=-1;
			}
			for (i=0; i<response.length; i++){
				$("#com_nombre").append("<option value='"+response[i].com_codigo+"'>"+response[i].com_nombre+"</option>");
			}
			
		});
	});
	
	$('#per_rut').Rut({
		  on_error: function(){ 
			  BootstrapDialog.alert({
		            title: 'Error',
		            message: 'El RUN ingresado es incorrecto!!',
		            type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
		            closable: true, // <-- Default value is false
		            draggable: true, // <-- Default value is false
		            buttonLabel: 'Volver', // <-- Default value is 'OK',
		            //callback: function(result) {
		                // result will be true if button was click, while it will be false if users close the dialog directly.
		                //alert('Result is: ' + result);
		            //}
		        });
				console.log('prueba');
			}
		});
	$('#per_rut_pro').Rut({
		  on_error: function(){ 
			  BootstrapDialog.alert({
		            title: 'Error',
		            message: 'El RUN ingresado es incorrecto!!',
		            type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
		            closable: true, // <-- Default value is false
		            draggable: true, // <-- Default value is false
		            buttonLabel: 'Volver', // <-- Default value is 'OK',
		            //callback: function(result) {
		                // result will be true if button was click, while it will be false if users close the dialog directly.
		                //alert('Result is: ' + result);
		            //}
		        });
				console.log('Rut');
			}
		});

	$("#per_rut").change(function(event){
		$("#cargando").css("display", "inline");
		$.get("persona/"+event.target.value+"", function(response,state){
			if (response.length== 0){
				console.log('1');
				per_nombre.value='';
				per_apellido_paterno.value='';
				per_apellido_materno.value='';
				per_email.value='';
				$('#per_nombre').focus();
			}
			else{
				if (response[0].rol_nombre == 'Profesor'){
					console.log('2'+response[0].rol_nombre);
					BootstrapDialog.alert({
						title: 'Error',
						message: 'El profesor ya fue ingresado',
						type: BootstrapDialog.TYPE_WARNING, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
						closable: true, // <-- Default value is false
						draggable: true, // <-- Default value is false
						buttonLabel: 'Volver', // <-- Default value is 'OK',
					});
					per_rut.value='';
					per_nombre.value='';
					per_apellido_paterno.value='';
					per_apellido_materno.value='';
					per_email.value='';
					$('#per_rut').focus();
				}
				else{
					console.log('3');
					per_nombre.value=response[0].per_nombre;
					per_apellido_paterno.value=response[0].per_apellido_paterno;
					per_apellido_materno.value=response[0].per_apellido_materno;
					per_email.value=response[0].per_email;
					$('#per_password').focus();
				}
			}
			
			
		});
		//$("#cargando").css("display", "none");
	});

});





