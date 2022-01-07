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
	
	//A.B: Activa o desactiva el modo profesor (puede ver las repuestas correctas)
	
	$profesor = $_POST['profesor'];
	
	$path = "preguntas";
	
	$elemento = $_POST['elemento'];
	
	$ruta = $path . '\\' . $elemento;

	//echo $ruta;exit();
	
	//echo "<br><p style='color:red'>" . $ruta . "</p>";
	
	$xml = simplexml_load_file($ruta);
	
	$conjuntos = $xml->assessment->section; 
	
	echo "<br><p style='color:red'>" . strtoupper($xml->assessment['title']) . "</p>";
	
	$numPregunta = 1;
	
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
			
			<p style='color:blue'>
			
			<?php 
			
			echo "Pregunta " . $numPregunta;
			//echo utf8_decode(strip_tags($titulo));
			
			if($profesor)
			{
				echo " ( Tipo: $tipoPregunta )";
			}
			
			?>
			
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
			
			<p style="">
			<?php 
				
				echo formateaCodigo(utf8_decode(strip_tags($pregunta_texto)));
			
				?>
			</p>
			
			<?php

				$rutaImagen = str_replace( '/', '\\', $_SERVER['DOCUMENT_ROOT']. "/Examenes/" .$imagenPregunta);
			
				if($imagenPregunta!= null)
				{	
			?>
			
					<img src="<?=$rutaImagen?>">
			
			<?php
				}
			
			
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
						echo utf8_decode(strip_tags($lr->response_label->flow_mat->material->mat_extension->mat_formattedtext));
					?>
					</li>
					<?php 
					
					if($profesor)
					{
						
						foreach ($puntuacion as $p)
						{
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
				
				<div style="clear:both"></div>
				
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
						 		echo utf8_decode(strip_tags($opcion));
						 		
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
					 				echo  utf8_decode(strip_tags($respD));
					 				
					 		?>
				 				</li>
				 			<?php
							
						}
						
						?>
						</ol>
						</td>
					</tr>
				</table>
				
				<div style="clear:both"></div>
				
				
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
						
						foreach ($resp["$idValor"] as $ind => $val)
						{
							if($val == $valorCorrecto)
							{
								echo "<span style='color:green'>" . utf8_decode(strip_tags(trim($resp["$idValor"]['literal']))) . "</span> &rarr; <span style='color:blue'>" . utf8_decode(strip_tags(trim($respDarr[$ind]))) . "</span><br/>";
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
					$id = $r->response_label['ident'];
					$respTexto = strip_tags($r->response_label->flow_mat->material->mat_extension->mat_formattedtext);
					$resps5["$id"] = $respTexto;
					echo utf8_decode(strip_tags($respTexto));
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
								echo "<span style='color:green'>" . utf8_decode(strip_tags(trim($resps5[$idC]))) . "</span><br>";
							}
						}
					}
				}
			
				//print_r($respuestasXML);
			}
		}
		
		$numPregunta++;
	}
}	
?>

</body>
</html>


