<?php
#tiempo de ejecucions
set_time_limit(0);


#IMPORTANDO LA CLASE DE CURL
require_once("class/facilcurl.php");

#con n=40 sirbe para poner la cantidad de resultados para mostrar en mx.search.yahoo.com
#HEREDANDO LA CLASE CURL EN YAHOO
class yahoo extends facilcurl
{
	
	private $codigo;

	function dork_yahoo($url,$fecha="",$cantidad=10,$exprecion=null)
	{
		$pagina=1;
		$link=array();
		$link2=array();
		$error="NO SE ENCONTRARON ERRORES";
		$error2=array("ESTE ERROR NO CONTIENE ARRAY");
		$tolerancia=0;
		$tolerancia2=0;

		switch ($fecha) 
		{
			case 0:	#NO TIENEFECHA 
				$fecha="";
				break;
			case 1: #FECHA DE 24 HORAS
				$fecha="&age=1d&btf=d"; 
				break;
			case 2: #FECHA DE ULTIMAS SEMANADAS
				$fecha="&age=1w&btf=w";
				break;
			case 3: # FECHA DE ULTIMOS MESES
				$fecha="&age=1m&btf=m";
				break;
			default:
				die("EL NUMERO DE FECHA QUE COLOCASTE NO ES CORRECTA NUMEROS PARA USAR:<br>
					0 = SE EXTRAERAN TODOS LINK ENCONTRADOS SIN FLITRO DE FECHA <br>
					1 = SE EXTRAERAN LOS LINK QUE TENGAN UN TIEMPO DE 24 HORAS <br>
					2 = SE EXTRAERAN LOS LINK QUE TENGAN UN TIEMPO DE SEMANADAS  <br>
					3 = SE EXTRAERAN LOS LINK QUE TENGAN UN TIEMPO DE MESES   <br>");
				
		}





		while (true) 
		{
			
			#$this->curl("https://search.yahoo.com/search?fr=yhs-invalid&p=".preg_replace("[\s+]", "%20", $url)."&b=$pagina".$fecha);

			echo $pagina."- ";
			$this->curl("https://search.yahoo.com/search?fr=yhs-invalid&p=".urlencode($url)."&b=$pagina".$fecha,0,0,1,1);


/*
			if ($pagina == 81 ) {
				echo "esperando 10 sg";
				sleep(10);
			}
*/


			if(($this->codigo= $this->exe_curl()))
			{
				$link2=$this->url_yahoo();
				if((array_diff($link2, $link))==true)
				{

					#$link=array_merge($link,$link2);
					$link= array_merge($link,$link2);#QUITANDO LINK DE PUBLICIDAD DE yahoo


					$link = array_unique($link,SORT_STRING); #QUITANDO LINK REPETIDOS  "PROBLEMA CUANDO EL ARRAY TIENE MAS DE 600 ELEMENTOS"
					if(count($link)>=$cantidad)
					{
						
						break;
					}
					else
					{
							$pagina=$pagina+10;
							$tolerancia=0;
							$tolerancia2=0;
					}
				}
				else
				{
					if($tolerancia==5)  #se le da 5 paginas mas de tolerancia al no encontrar
					{
						$error="SE TERMINARON LOS LINK EN LA PAGINA NUM = ".$pagina;
						$error2=$link2;
						break;
					}
					$pagina=$pagina+10;
					$tolerancia++;
				}
			}
			else
			{


				if($tolerancia2==10)
				{
					$error="NO PUDO CARGAR LA URL NUM = ".$pagina;
					break;
				}
				$tolerancia2++;
			}

		}

		if($exprecion)
		{  #por si quieres que el link cumpla con una expresion

			if(($link2=preg_grep("#$exprecion#i", $link)))  #PREG_GREP_INVERT
			{
				#return array(0 => $link2, 1 => count($link),2 => ($cont-count($link))/*este campo retorna cantidad de link sin mostrar*/);
				return array(0 => $link2,  #son los link que cumplen con la exprecion  
							 1 => count($link2), #cantidad de link encontrados que cumplen con la exprecion
							 2 => preg_grep("#$exprecion#i",$link,PREG_GREP_INVERT), #link que no cumplen con la exprecion
							 3 => count($link), #total de link encontradas
							 4 => (count($link)-count($link2)),#cantidad de link no tomadas en cuanta
							 5 => $error,#cualquier error se mostrara
							 6 => $error2); #si se acaban los link aqui mostrara la ultima peticion que se realizo
			}
			else
			{
				return array(0 => array("NO SE ENCONTRARON LINK CON LA EXPRECION =  $exprecion"),
							 1=> 0,
							 2=> preg_grep("#$exprecion#i",$link,PREG_GREP_INVERT),
							 3=> count($link),
							 4=> 0,
							 5=> $error,
							 6=> $error2);
			}
		}
		else
		{
			return array(0 => $link, 1 => count($link),2=> 0, 3=> 0, 4=>0 ,5=> 0,5=> $error,6=> $error2);
		}

	}



	function url_yahoo()
	{
		// preg_match_all('#fz-ms fw-m fc-12th wr-bw lh-17">(.*?)<\/span#', $this->codigo, $findlink);
		preg_match_all('#\/RO\=10\/RU\=(.*?)\/RK\=2\/RS\=#', $this->codigo, $findlink);

		#Eliminando dominios basura
		return preg_grep("#bing\.com|yahoo\.|yahoo\.com|google\.com|cc.bingj\.com|www\.verizonmedia\.com#i",$findlink[1],PREG_GREP_INVERT); // cc.bingj.com  este domino muestra el cache de la pagina

		//return $findlink[1];
	}


	function __destruct()
	{
		unset($this->codigo);
	}



	
}





$m = new yahoo();



#dork_bing(URL,FECHA_DONDE_SE_BUSCARA_EL_LINK,    CANTIDAD_DE_LINK_QUE_QUIERES,    EXPRESION_REGULAR_QUE_CUMPLIRA_EL_CADA_LINK)
$j=$m->dork_yahoo("jose",0,500);

echo "\n";

foreach ($j[0] as $key => $value) {
	//echo str_replace(array("<b>","</b>"), array("",""), $value)."\n";   #quitandole la etiqueta b

	echo urldecode($value)."\n";

}



echo "\n\nSE ENCONTRARON ".$j[1]." LINK DE YAHOO ERROR --->".$j[5]."\n\n";







/*
#CON VALUES RECORRERA LAS KEY EMPEZANDO DESDE 0,1,2,3 ETC
print_r(array_values($array));

*/




?>