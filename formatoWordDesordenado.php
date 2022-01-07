<html>

<head>

<?php

include_once 'funciones.php';

error_reporting(E_ALL ^ E_NOTICE);

/*
print_r($_SERVER);
print_r($_SERVER['DOCUMENT_ROOT']);exit();
*/
//echo "si";exit();

$str = json_decode($_POST['str'], true); 

//print_r($str);exit();


 header('Content-Type: text/html; charset=utf-8');
 header('Content-type: application/vnd.ms-word');
 header("Content-Disposition: attachment; filename=examen.doc");
 header("Pragma: no-cache");
 header("Expires: 0");

?>
</head>

<body>
<?php 
	
	$path = "preguntas";
	
	$elemento = $_POST['elemento'];
	
	$ruta = $path . '\\' . $elemento;

	//echo $ruta;exit();
	
	//echo "<br><p style='color:red'>" . $ruta . "</p>";
	
	$xml = simplexml_load_file($ruta);
	
	$conjuntos = $xml->assessment->section; 
	
	//echo "<br><p style='color:red'>" . strtoupper($xml->assessment['title']) . "</p>";
	
	$numPregunta = 1;
	
	$arrayPreguntas = array();
	$indice = 0;
	
	foreach ($conjuntos as $conjunto)
	{
		
		$preguntas = $conjunto->item;
		
		foreach($preguntas as $pregunta)
		{
			
			$titulo = $pregunta['title'];
			
			$idPreg = $pregunta->itemmetadata->bbmd_asi_object_id;
			
			if(in_array($idPreg, $str))
			{
			
			$tipoPregunta = $pregunta->itemmetadata->bbmd_questiontype;
			
			?>
			
			<?php 
			
			//echo "Pregunta " . $numPregunta;
			//echo utf8_decode(strip_tags($titulo));
			
			$arrayPreguntas[$indice]['titulo'] = (string)$titulo;
			$arrayPreguntas[$indice]['tipo'] = (string)$tipoPregunta;
			$arrayPreguntas[$indice]['idPreg'] = (string)$idPreg;
			
			
			$bloques = $pregunta->presentation->flow->flow;
			
			$puntuacion = $pregunta->resprocessing->respcondition;
			
			
			//A.B: Obtenemos la pregunta:
			$preguntaXML = $bloques[0];
			//print_r($pregunta);
		
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
				}
				else if (trim($fl['class']) == 'FILE_BLOCK')
				{
					$urlimagen = $fl->material->matapplication['uri'];
					$carpetaImg =  substr($elemento, 0, strpos($elemento, ".dat"));
					$imagenPregunta = $path."/".$carpetaImg."/".$urlimagen;
					//echo $imagenPregunta;
				}
			}
			
			//A.B: Si la pregunta es de rellenar textos en blanco tenemos que parsearla
			if($tipoPregunta == "Fill in the Blank Plus")
			{
				//$pos = strpos($pregunta_texto, "[");
				
				$patron = "/\[x[0-9]+\]/";
				
				$encontrado = preg_match_all($patron, $pregunta_texto, $coincidencias, PREG_OFFSET_CAPTURE);
				
			}
			
				
				$enunciado = formateaCodigo(utf8_decode(strip_tags($pregunta_texto)));
			    $arrayPreguntas[$indice]['enunciado'] = $enunciado;
			
			
			  	$rutaImagen = str_replace( '/', '\\', $_SERVER['DOCUMENT_ROOT']. "/Examenes/" .$imagenPregunta);
			
				if($imagenPregunta!= null)
				{	
			        $arrayPreguntas[$indice]['imagen'] = $rutaImagen;
			
				}
						
			//A.B: Tratamos las respuestas:
			
			if($tipoPregunta == "Multiple Answer")
			{
				$listaRespuestas = $respuestasXML->response_lid->render_choice->flow_label;
				
				
				foreach ($listaRespuestas as $lr)
				{
					$idPregunta = $lr->response_label['ident'];
					$respuesta = utf8_decode(strip_tags($lr->response_label->flow_mat->material->mat_extension->mat_formattedtext));
					$arrayPreguntas[$indice]['respuestas'][] = $respuesta;
					
				
				}
				
				shuffle($arrayPreguntas[$indice]['respuestas']);
				
			}
			else if($tipoPregunta == "Matching")
			{
				
				//A.B: Obtenemos las respuestas:
				$bloqueIzquierda = $bloques[1];
				
				$bloquesI = $bloqueIzquierda->flow;
				foreach($bloquesI as $b)
				{
						 				
			 		$opcion = trim(strip_tags($b->flow->material->mat_extension->mat_formattedtext));
			 		
			 		$respuesta = formateaCodigo(utf8_decode(strip_tags($opcion)));
			 		$arrayPreguntas[$indice]['respuestas']['Izq'][] = $respuesta;
			 		
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
							
				}
				
				shuffle($arrayPreguntas[$indice]['respuestas']['Izq']);
			
				$bloqueDerecha =$bloques[2];
				$bloquesD = $bloqueDerecha->flow;
						
				$i=0;
				foreach($bloquesD as $b)
				{
			 				$respD = strip_tags($b->flow->material->mat_extension->mat_formattedtext) ;
			 				$i++;
			 				$respDarr[$i] = $respD;  
			 				
			 				$respuesta = formateaCodigo(utf8_decode(strip_tags($respD)));
			 				$arrayPreguntas[$indice]['respuestas']['Der'][] = $respuesta;
			 		
				}
				
				shuffle($arrayPreguntas[$indice]['respuestas']['Der']);
						
			}
			else if($tipoPregunta == "Multiple Choice")
			{
				//echo $tipoPregunta;
				$respuestas = $respuestasXML->response_lid->render_choice->flow_label;
    			foreach ($respuestas as $r)
				{
					$id = $r->response_label['ident'];
					$respTexto = strip_tags($r->response_label->flow_mat->material->mat_extension->mat_formattedtext);
					$resps5["$id"] = $respTexto;
					
					$respuesta = formateaCodigo(utf8_decode(strip_tags($respTexto)));
					$arrayPreguntas[$indice]['respuestas'][] = $respuesta;
				}
				
				shuffle($arrayPreguntas[$indice]['respuestas']);
				
			}
		}
		
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
        </p>     
	   
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

</body>
</html>