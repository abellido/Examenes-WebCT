<?php

function eliminarDirectorio($filepath) {
    if (is_dir($filepath) && !is_link($filepath)) {
        if ($dh = opendir($filepath)) {
            while (($sf = readdir($dh)) !== false) {  
                if ($sf == '.' || $sf == '..') {
                    continue;
                }
                if (!eliminarDirectorio($filepath.'/'.$sf)) {
                    throw new Exception($filepath.'/'.$sf.' could not be deleted.');
                }
            }
            closedir($dh);
        }
        return rmdir($filepath);
    }
    return unlink($filepath);
}

function eliminarContenidoDirectorio($filepath) 
{
    if (is_dir($filepath) && !is_link($filepath)) 
    {
        if ($dh = opendir($filepath)) 
        {
            while (($sf = readdir($dh)) !== false) 
            {  
                if ($sf == '.' || $sf == '..') 
                {
                    continue;
                }
                if (!eliminarContenidoDirectorio($filepath.'/'.$sf)) 
                {
                    throw new Exception($filepath.'/'.$sf.' could not be deleted.');
                }
            }
            closedir($dh);
        }
        
	    if($filepath != "preguntas")
	    {
	    	return rmdir($filepath);
	    }
	    else 
	    {
	    	return 1;
	    }
    }
    
    if($filepath != "preguntas")
    {
    	return unlink($filepath);
    }
    else 
    {
    	return 1;
    }
    
}

function buscarFicheroPreguntas($filepath)
{
	$max = 0;
	if ($gestor = opendir($filepath)) 
	{
	    //echo "Gestor de directorio: $gestor\n";
	    //echo "Entradas:\n";
	 
	    /* Esta es la forma correcta de iterar sobre el directorio. */
	    while (false !== ($entrada = readdir($gestor))) 
	    {
	    	if(!is_dir($entrada) && strpos($entrada,".dat"))
	    	{
	    		//echo "$entrada " . filesize($filepath."/".$entrada) . "<br>";
	    		
	    		if(filesize($filepath."/".$entrada) > $max)
	    		{
	    			$max = filesize($filepath."/".$entrada);
	    			$fichero = $entrada;
	    		}
	    			
	    	}
	        	
	    }
	    closedir($gestor);
	}
	
	return $fichero;	
}

function formateaCodigo($cadena)
{
	$cadena = str_replace("#include", "<br>#include&nbsp;", $cadena);
	$cadena = str_replace("void main(", "<br>void main(", $cadena);
	$cadena = str_replace("while(", "while(", $cadena);
	$cadena = str_replace("printf(", "<br>printf(", $cadena);
	$cadena = str_replace("scanf(", "<br>scanf(", $cadena);
	$cadena = str_replace("for(", "<br>for(", $cadena);
	$cadena = str_replace("if(", "<br>if(", $cadena);
	$cadena = str_replace("void main (", "<br>void main(", $cadena);
	$cadena = str_replace("while (", "<br>while(", $cadena);
	$cadena = str_replace("printf (", "<br>printf(", $cadena);
	$cadena = str_replace("scanf (", "<br>scanf(", $cadena);
	$cadena = str_replace("for (", "<br>for(", $cadena);
	$cadena = str_replace("if (", "<br>if(", $cadena);
	$cadena = str_replace("{", "{<br>", $cadena);
	$cadena = str_replace("}", "}<br>", $cadena);
	if(!strpos($cadena, "for"))
	{
	    $cadena = str_replace(";", ";<br>", htmlspecialchars_decode($cadena));
	}
	return $cadena;
}


