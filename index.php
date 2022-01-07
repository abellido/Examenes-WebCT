<html >
	<head>
		<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
		<script src="jscript/funciones.js"></script>
		<link rel="stylesheet" href="css/estilo.css"> 
	</head>	

<body>

<?php 

error_reporting(E_ALL ^ E_NOTICE);

include_once 'funciones.php';

$path = "preguntas";

//A.B: Controla si hay que mostrar el formulario para seleccionar fichero
$cargar = $_POST['cargar'];

//A.B: Controla si ya se ha cargado el fichero (y por tanto ya se había seleccionado)
$cargado = $_POST['cargado'];

if(!isset($cargar))
{ 
?> 

<h3>GENERACIÓN DE EXAMENES DESDE WEBCT</h3>

<h4 class="italica">Para comenzar seleccione un fichero en el botón "Examinar" y haga click en el botón "Cargar Fichero"</h4>

<p>El fichero que debe seleccionar es el que se exporta desde webct en formato comprimido. No es necesario que lo descomprima.</p>

<form name="elForm" method="post" action="" enctype="multipart/form-data" 
      onsubmit="return validar(this)" class="formFichero"> 
	
	<input type="hidden" name="cargar" value="1">
    
    <p align="left" style="font-weight: bold;">
        Seleccione un fichero de exportaci&oacute;n
    </p>
    
    <p align="left">
        <input type="file" name="archivo" />
    </p> 
    
    <p align="left">
        <input type="submit" value="Cargar Fichero">
    </p> 

</form>

<?

eliminarContenidoDirectorio($path);

}

#Aquí realizamos la carga del fichero 
if(isset($cargar))
{ 
	
	//A.B: Activa o desactiva el modo profesor (puede ver las repuestas correctas)
	$profesor = $_POST['modo'];
	
	$elemento = $_POST['elemento'];
	
	if(isset($elemento))
	{
		$elemento = $_POST['elemento'];	
	}
	else
	{
		$elemento = $_FILES['archivo']['name'];
	}
	
	$ruta = $path . '\\' . $elemento;	
	
	//echo $ruta . $cargado;exit;
	
	if(!$cargado)
	{
		if ($_FILES['archivo']["error"] > 0)
		{
			echo "Error: " . $_FILES['archivo']['error'] . "<br>";
		}
		else
		{
			//print_r($_FILES);exit;
		    echo "Nombre: " . $_FILES['archivo']['name'] . "<br>";
			echo "Tipo: " . $_FILES['archivo']['type'] . "<br>";
			echo "Tamaño: " . ($_FILES["archivo"]["size"] / 1024) . " kB<br>";
			echo "Carpeta temporal: " . $_FILES['archivo']['tmp_name'];
		  
		 	//A.B: ahora con la funcion move_uploaded_file lo guardamos en el destino que queramos
		 	move_uploaded_file($_FILES['archivo']['tmp_name'],	"$path/" . $_FILES['archivo']['name']);
		 
			//A.B: Descomprimimos el fichero
			$zip = new ZipArchive;
		
			if ($zip->open($ruta) === TRUE) 
			{
		    	$zip->extractTo($path);
		    	$zip->close();
		    	echo "<br/> Descomprimido Correctamente <br/>";
			} 
			else 
			{
		 		echo "<br/> Error descomprimiendo <br/>";
			}
		}
	}
	
	//echo "<br><p style='color:red'>" . $ruta . "</p>";
	
	$ficheroPreguntas = buscarFicheroPreguntas($path);
	
	$ficheroCargar = $path . '\\' . $ficheroPreguntas;
	
	//echo $ficheroCargar;
	
	$xml = simplexml_load_file($ficheroCargar);
	
	$conjuntos = $xml->assessment->section; 
	
	?>
	<table width="80%" align="center">
		<tr>
			<td>
				<input type="button" id="modoProf" value="Ver Respuestas" onclick="modoProfesor();"></input>		
			</td>
			<td>
				<button onclick="marcarDesmarcarTodo();">Marcar/Desmarcar Todas</button>		
			</td>
			<td>
				<button onclick="enviar();">Generar Documento</button>		
			</td>
			<td>
				<button onclick="CargarOtroFichero('<?=$_SERVER['PHP_SELF']?>')"> Cargar Otro Conjunto </button>		
			</td>
			<td>
				<button onclick="enviarDesordenado()"> Generar Doc. Aleatorio </button>		
			</td>
		<tr>
	</table>
		
	<?php 
	
	echo "<br><p style='color:red'>" . strtoupper($xml->assessment['title']) . "</p>";
	
	$numPregunta = 1;
	
	$arrayPreguntas = array();
	$indice = 0;
	
	foreach ($conjuntos as $conjunto)
	{
		
		$preguntas = $conjunto->item;
		
		foreach($preguntas as $pregunta)
		{
			?>
			
			<div id="<?=$elemento?>">
			
			<?php 
    		$titulo = $pregunta['title'];
			
    		
    		
    		
			$tipoPregunta = $pregunta->itemmetadata->bbmd_questiontype;
			$idPreg = $pregunta->itemmetadata->bbmd_asi_object_id;
			
			?>
			
			<p style='color:blue'>
			
			<?php 
			
			echo "Pregunta " . $numPregunta;
			//echo utf8_decode(strip_tags($titulo));
			
			if($profesor)
			{
				echo " ( Tipo: $tipoPregunta )";
			}
			
			$arrayPreguntas[$indice]['titulo'] = (string)$titulo;
			$arrayPreguntas[$indice]['tipo'] = (string)$tipoPregunta;
			$arrayPreguntas[$indice]['idPreg'] = (string)$idPreg;
			
			?>
			
			</p>
			
			<p>	
				<input type="checkbox" name="pregunt" value="<?=$idPreg?>" onclick="marcarDesmarcar(this);" >
	           		<span style="color:maroon; font-weight: bold">Incluir esta pregunta en el examen</span>
	           	</input>
			</p>	
			
			<?php 
			
			$bloques = $pregunta->presentation->flow->flow;
			
			$puntuacion = $pregunta->resprocessing->respcondition;
			
			/*
			 foreach($bloques as $b)
			 {
				echo $b['class'] . "<br>";
			 }
			*/
			
			//A.B: Obtenemos la pregunta:
			$preguntaXML = $bloques[0];
			//print_r($preguntaXML);	exit();
			//A.B: Obtenemos las respuestas:
			$respuestasXML = $bloques[1];
			//print_r($respuestas);	
			
			$imagenPregunta = null;
			
			//A.B: El texto de la pregunta:
			foreach ($preguntaXML->flow as $fl)
			{
				if (trim($fl['class']) == 'FORMATTED_TEXT_BLOCK')
				{
					$pregunta_texto = $fl->material->mat_extension->mat_formattedtext;
					/*
					if($titulo == "134")
					{
						print_r($pregunta_texto);exit();
					}
					*/
				}
				else if (trim($fl['class']) == 'FILE_BLOCK')
				{
					$urlimagen = $fl->material->matapplication['uri'];
					$carpetaImg =  substr($ficheroPreguntas, 0, strpos($ficheroPreguntas, ".dat"));
					$imagenPregunta = $path."/".$carpetaImg."/".$urlimagen;
				}
			}
			//$pregunta_texto = $preguntaXML->flow->material->mat_extension->mat_formattedtext;
			
			//A.B: Si la pregunta es de rellenar textos en blanco tenemos que parsearla
			if($tipoPregunta == "Fill in the Blank Plus")
			{
				//$pos = strpos($pregunta_texto, "[");
				
				$patron = "/\[[x|Blank[0-9]+\]/";
				
				$encontrado = preg_match_all($patron, $pregunta_texto, $coincidencias, PREG_OFFSET_CAPTURE);
				
				//A.B: Si el modo profesor no está activado ponemos los huecos a rellenar
				if(!$profesor)
				{	
					foreach ($coincidencias[0] as $coincide)
					{
						$pregunta_texto = str_replace($coincide[0], " ______________ ", $pregunta_texto);
					}
				}
				
				/*
				if ($encontrado) 
				{
					print "<pre>"; print_r($coincidencias); print "</pre>\n";
					print "<p>Se han encontrado $encontrado coincidencias.</p>\n";
					
					foreach ($coincidencias[0] as $coincide) 
					{
						print "<p>Cadena: '$coincide[0]' - Posici&oacute;n: $coincide[1]</p>\n";
					}
				} 
				else 
				{
					print "<p>No se han encontrado coincidencias.</p>\n";
				}
				
				*/
				
			
			}
			
			?>
			<p style="text-align: justify;">
			
			<?php 
			
			     		     
			     $enunciado = formateaCodigo(utf8_decode(strip_tags($pregunta_texto)));
			     
			     $arrayPreguntas[$indice]['enunciado'] = $enunciado;
			     
			     echo $enunciado;
				
				if($imagenPregunta!= null)
				{
				    $arrayPreguntas[$indice]['imagen'] = $imagenPregunta;
			?>
			
			</p>
					<img src="<?=$imagenPregunta?>">
			<?php
				}
			?>	
			
			<?php
			
			//A.B: Tratamos las respuestas:
			
			if($tipoPregunta == "Multiple Answer")
			{
				$listaRespuestas = $respuestasXML->response_lid->render_choice->flow_label;
				
				?>
				<ol type="a">
				<?php 
				
				foreach ($listaRespuestas as $lr)
				{
					$idPregunta = $lr->response_label['ident'];
					?>
					<li>
					<?php 

					$respuesta = formateaCodigo(utf8_decode(strip_tags($lr->response_label->flow_mat->material->mat_extension->mat_formattedtext)));
					$arrayPreguntas[$indice]['respuestas'][] = $respuesta;
					echo $respuesta;
					//echo "<br>" . utf8_decode(htmlentities($lr->response_label->flow_mat->material->mat_extension->mat_formattedtext, ENT_QUOTES,'UTF-8'));
						
					?>
					</li>
					<?php 
					
					if($profesor)
					{
						
						foreach ($puntuacion as $p)
						{
						    //print_r($p);
						    
							if(!isset($p['title']))
							{
// 								if((string)$p->conditionvar->varequal['respident'] == $idPregunta)
// 								{
// 								    //echo (string)$p->conditionvar->varequal['respident'];
// 									if($p->setvar > 0)
// 									{
// 										echo "<p style='color:green'>Correcta. (Suma $p->setvar puntos).</p>";
// 									}
// 									else 
// 									{
// 										echo "<p style='color:red'>Incorrecta. (Resta $p->setvar puntos).</p>";
// 									}
									
// 								}
								
							}
							else if($p['title'] == "correct")
							{
							    $correctas = $p->conditionvar->and->varequal;
							    
							    foreach($correctas as $c)
							    {
							        //echo $c . " " . $idPregunta;
							        if((string)$c == $idPregunta)
							        {
							            echo "<p style='color:green'>Correcta.</p>";
							            $esCorrecta = true;
							        }
							        
							    }
							    
							    if(!$esCorrecta)
							    {
							        echo "<p style='color:red'>Incorrecta.</p>";
							    }
							    
							    $esCorrecta = false;
							}
								
						}		
					}
				}
				
				//print_r($arrayPreguntas);
				
			?>
			
			</ol>
			
			<?php 
			
			}
			else if($tipoPregunta == "Fill in the Blank Plus")
			{
				if($profesor)
				{
					$puntuaciones = $puntuacion->conditionvar->and->or;
					
					echo "<p style='color:red'>La respuesta correcta es: </p>";
					
					foreach ($puntuaciones as $p)
					{
						foreach ($p as $acierto)
						{
							echo "<p style='float: left;margin: 20px;color:green'>" . utf8_decode(strip_tags($acierto['respident'])) . " = " . utf8_decode(strip_tags($acierto)) . "</p>";
						}
					}
					
					echo "<p style='clear:both'></p>";
				}	
			}
			
			else if($tipoPregunta == "Matching")
			{
				//echo $tipoPregunta;
				
				//A.B: Obtenemos las respuestas:
				$bloqueIzquierda = $bloques[1];
				
				$bloquesI = $bloqueIzquierda->flow;
				?>
				
				<table>
					<tr>
						<td>
						
						<ol type="1">
						<?php 
						foreach($bloquesI as $b)
						{
							?>
				 			<li>
						 		<?php 	
						 				
						 		$opcion = trim(strip_tags($b->flow->material->mat_extension->mat_formattedtext));
						 		
						 		//echo formateaCodigo(utf8_decode(strip_tags($opcion)));
						 		$respuesta = formateaCodigo(utf8_decode(strip_tags($opcion)));
						 		$arrayPreguntas[$indice]['respuestas']['Izq'][] = $respuesta;
						 		echo $respuesta;
						 		
						 		$id = strip_tags($b->response_lid['ident']); 
						 		//echo $id;
						 		
						 		$respuestas = $b->response_lid->render_choice->flow_label->response_label;
						 				
				 				$i = 0;
				 				
				 				$resp[$id]['literal'] = $opcion;
				 				
				 				foreach($respuestas as $r)
				 				{
				 					//echo  $r['ident'];
				 					$i++;
									
				 					$resp[$id][$i] = (string)$r['ident'];
				 					
				 				}
								
				 				?>
				 			</li>
				 			<?php
							
						}
			
						//print_r($resp);
						
						?>
						</ol>
					
						</td>
					
						<td style="padding-left: 100px">
					
						<ol type="a">
						
						<?php 
						
						$bloqueDerecha =$bloques[2];
						$bloquesD = $bloqueDerecha->flow;
						
						$i=0;
						foreach($bloquesD as $b)
						{
							?>
				 				<li>
					 		<?php 	
					 				$respD = strip_tags($b->flow->material->mat_extension->mat_formattedtext) ;
					 				$i++;
					 				$respDarr[$i] = $respD; 
					 				
					 				$respuesta = formateaCodigo(utf8_decode(strip_tags($respD)));
					 				$arrayPreguntas[$indice]['respuestas']['Der'][] = $respuesta;
					 				echo $respuesta;
					 				//echo  formateaCodigo(utf8_decode(strip_tags($respD)));
					 				
					 		?>
				 				</li>
				 			<?php
							
						}
						
						?>
						</ol>
					</td>
				</tr>
			</table>
				
			<?php 
				
				if($profesor)
				{
					echo "<p style='color:red'>La respuesta correcta es: </p>";
					
					foreach ($puntuacion as $p)
					{
						//print_r($p);
						$idValor = $p->conditionvar->varequal['respident'];
						$valorCorrecto = $p->conditionvar->varequal;
						
						//echo $resp["$idValor"]['literal'] . " == " . $idValor . " == " . $valorCorrecto ."<br>";
						
						if($resp["$idValor"])
						{
						    foreach ($resp["$idValor"] as $ind => $val)
    						{
    							if($val == $valorCorrecto)
    							{
    								echo "<span style='color:green'>" . utf8_decode(strip_tags(trim($resp["$idValor"]['literal']))) . "</span> &rarr; <span style='color:blue'>" . utf8_decode(strip_tags(trim($respDarr[$ind]))) . "</span><br/>";
    							}
    						}
						}
						
					}
				}
				
				//print_r($respDarr);
				//print_r($resp);
				
			}
			else if($tipoPregunta == "Multiple Choice")
			{
				//echo $tipoPregunta;
				$respuestas = $respuestasXML->response_lid->render_choice->flow_label;
			
			?>	
			
				<ol type="a">
			
			<?php 	
				foreach ($respuestas as $r)
				{
			?>		
					<li>
			<?php 
					$id = (string)$r->response_label['ident'];
					$respTexto = (string)strip_tags($r->response_label->flow_mat->material->mat_extension->mat_formattedtext);
					$resps5["$id"] = $respTexto;
					//echo formateaCodigo(utf8_decode(strip_tags($respTexto)));
					$respuesta = formateaCodigo(utf8_decode(strip_tags($respTexto)));
					$arrayPreguntas[$indice]['respuestas'][] = $respuesta;
					echo $respuesta;
			?>		
					</li>
			<?php 		
				}
				
				//print_r($resps5);
				
			?>	
				</ol>
			<?php 	
			
				if($profesor)
				{
					echo "<p style='color:red'>La respuesta correcta es: </p>";
					
					foreach ($puntuacion as $p)
					{
						//print_r($p);
						
						if($p['title'] == "correct")
						{
							foreach ($p->conditionvar as $r5)
							{
								//print_r($r5);
								
								$idC = (string)trim($r5->varequal);
								//echo $idC;
								//print_r($idC);
								//print_r($resps5);
								//echo $resps5[trim($idC)];
								echo "<span style='color:green'>" . formateaCodigo(utf8_decode(strip_tags(trim($resps5[$idC])))) . "</span><br>";
							}
						}
					}
				}
			
				//print_r($respuestasXML);
			}
	
		?>			
			</div>
				
		<?php 	
		
		$numPregunta++;
		$indice++;
		}	
	}
	
	$indiceP = 1;
	
	shuffle($arrayPreguntas);
	
	foreach ($arrayPreguntas as $pr)
	{
	 ?>
	    <p style='color:blue'>
     <?php
            echo "Pregunta " . $indiceP;
     ?>
     
     <?php     
	    
	    if($profesor)
	    {
	        echo " ( Tipo: $tipoPregunta ) ";
	    }
    ?>
        </p>     
	    
	    <input type="checkbox" name="pregunt" value="<?=$pr['idPreg']?>" onclick="marcarDesmarcar(this);" />
	    <span style="color:maroon; font-weight: bold">Incluir esta pregunta en el examen</span>
	    
	    <p>
	    <?php     
	       echo $pr['enunciado'];
	     ?>
	    </p>
		
	<?php 

	   if($pr['imagen']!= null)
	   {
	       $img = $pr['imagen'];
	 ?>
				
	   <p>
		<img src="<?=$img?>">
	   </p>
	<?php

	   }
	   
	   if($pr['tipo'] != "Fill in the Blank Plus")
	   {
	       if($pr['tipo'] == "Matching")
	       {
	       ?>
           <table>
               <tr>
                   <td>
                       <ol type="1">
           	           <?php
           	           foreach($pr['respuestas']['Izq'] as $b)
           	           {
           	           ?>
   				 	      <li>
           	           <?php 	
           	                   echo $b;
           	            ?>
           	            </li>
           	            <?php 
           	            }
               	        ?>
               	        </ol>
					
					</td>
					
					<td style="padding-left: 100px">
					
						<ol type="a">
						<?php 
						
						    foreach($pr['respuestas']['Der'] as $b)
						    {
							?>
				 				<li>        
	           	         <?php 	
           	                   echo $b;
           	            ?>
           	            </li>
           	            <?php 
           	            }
               	        ?>
               	        </ol>
					</td>
				</tr>
			</table>
			<?php 	          
   	       }
   	       else
   	       {	  
   	           ?>
   	           <ol type="a">
   	           <?php      
               foreach ($pr['respuestas'] as $rp)
               {
    	          ?>
    	          
					<li>
					<?php 
					echo $rp;
					?>
					</li>
    			  <?php 
    	       }
    	       ?>
    	       </ol>
    	       <?php 
    	   }
	   }
	 
	    $indiceP++;
	    //print_r($pr);
	    
	}
	
	
   
	?>
	
	<form name="formModo" method="post" action="">
		<input type="hidden" id="modo" name="modo" value="<?=$profesor?>" />
		<input type="hidden" name="cargar" value="1">
		<input type="hidden" name="cargado" value="1">
		<input type="hidden" name="elemento" value="<?=$ficheroPreguntas?>">
	</form>		

	<form name="formulario" method="post" action="formatoWord.php">
		<input type="hidden" id="profesor" name="profesor" value="<?=$profesor?>" />
		<input type="hidden" id="str" name="str" value="" /> 
		<input type="hidden" name="elemento" value="<?=$ficheroPreguntas?>">
	</form>

	<table width="80%" align="center">
		<tr>
			<td>
				<input type="button" id="modoProf" value="Ver Respuestas" onclick="modoProfesor();"></input>		
			</td>
			<td>
				<button onclick="marcarDesmarcarTodo();">Marcar/Desmarcar Todas</button>		
			</td>
			<td>
				<button onclick="enviar();">Generar Documento</button>		
			</td>
			<td>
				<button onclick="CargarOtroFichero('<?=$_SERVER['PHP_SELF']?>')"> Cargar Otro Conjunto </button>		
			</td>
		<tr>
	</table>
	
<?php 
	
}

?>
	
</body>
</html>
	