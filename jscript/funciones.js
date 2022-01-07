/**
 * autor: Antonio M. Bellido Romero
 * Funciones de utilidad proyecto para generar exámenes WebCT.
 */
	
	// A.B: Este array contiene las preguntas seleccionadas para ser 
	// exportadas.
	var arr = new Array();

	// A.B: Varliable que controla si todas las preguntas han sido marcadas
	// o desmarcadas.
	var marcado = false;
	
	// A.B: Esta función marca o desmarca una pregunta para ser incluida
	// (o excluida) en el cuestionario que se va a exportar. 
	// Recibe como parámetro el checkbox html asociado a la pregunta.
	// Cuando se marca el checbox la pregunta es incluida en un array
	// y eliminada del mismo cuando se desmarca.
	function marcarDesmarcar(check)
	{
		if(!check.checked)
		{
			remove(arr, check.value);
		}
		else
		{
			arr.push(check.value);
		}

	}
	
	// A.B: Esta función permite enviar el formulario que contiene las
	// preguntas seleccionadas para ser exportadas al editor de 
	// texto.
	function enviar()
	{
		$('#str').val(JSON.stringify(arr));
		document.formulario.action = 'formatoWord.php';
		document.formulario.submit();
	}

	function enviarDesordenado()
	{
		$('#str').val(JSON.stringify(arr));
		document.formulario.action = 'formatoWordDesordenado.php';
		document.formulario.submit();
	}
	
	// A.B: Activa o desactiva el modo profesor que permite mostrar u
	// ocultar las respuestas a las preguntas.
	
	function modoProfesor()
	{
		var modo = $('#modo').val();

		if (modo == 0)
		{
			$('#modo').val(1);
			document.formModo.submit();
		}
		else
		{
			$('#modo').val(0);
			document.formModo.submit();
		}
		
	}

	// A.B: Permite marcar o desmarcar todas las preguntas
	function marcarDesmarcarTodo()
	{
		arr.length = 0;
		
		if(marcado)
		{
			$( "input[type='checkbox']" ).each(function() 
					{
				 		$(this).prop('checked', false);
					});

				marcado = false;	
		}
		else
		{
			
			$( "input[type='checkbox']" ).each(function() 
					{
				 		$(this).prop('checked', true);
				 		arr.push($(this).val());
					});

			marcado = true;
		}
		
	}

	function validar(formFich)
	{
		if(formFich.archivo.value == '')
		{
			alert('Debe seleccionar un fichero de preguntas');
			return false;
		}
		
		return true;
	}
	
	function CargarOtroFichero(url)
	{
		document.location.href = url;
	}
	
	function remove(arr, item) {
	      for(var i = arr.length; i--;) {
	          if(arr[i] === item) {
	              arr.splice(i, 1);
	          }
	      }
	  }