<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
//$field = $_REQUEST['field'];
//$_SERVER['http://host?sm371|AirTC'];

$datain = $_SERVER['QUERY_STRING'];
$fields = explode('|', $datain);
$station_tbname = $fields[0];
$field = $fields[1];
$interval = $fields[4];
//$dados = $_SERVER['HTTP_D'] ? $_SERVER['HTTP_D']  : $_REQUEST['s'];

include("csda_conexao_mysql.php");
 
/* Instanciamos o Objeto "conexao". */
$mysql = new conexao;

if ($fields[5]=='DAY'){

$query = "SELECT Timestamp, 
DATE_FORMAT(Timestamp, '%d') as dia,
ROUND(AVG($fields[1]),2) as $fields[1],
ROUND(AVG($fields[2]),2) as $fields[2],
ROUND(SUM($fields[3]),2) as $fields[3]
FROM `$station_tbname` 
WHERE ( (DATE_FORMAT(NOW(), '%m') - DATE_FORMAT(Timestamp, '%m') <= 0) AND DATE_FORMAT(Timestamp, '%Y') >='2012'
)
GROUP BY Day(timestamp)
ORDER BY Timestamp ASC
";
} else {
	
	if ($fields[6]=='HOJE')	{
		$query = "SELECT Timestamp, 
		DATE_FORMAT(Timestamp, '%d') as dia,
		ROUND(AVG($fields[1]),2) as $fields[1],
		ROUND(AVG($fields[2]),2) as $fields[2],
		ROUND(SUM($fields[3]),2) as $fields[3]
		FROM `$station_tbname` 
		WHERE (     (TIMESTAMP >DATE_SUB( DATE_FORMAT(NOW(), '%Y-%m-%d'), INTERVAL 0 DAY))
		        AND (TIMESTAMP <= DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-%d'), INTERVAL -1 DAY))
		      )
		GROUP BY $fields[5](timestamp)
		ORDER BY Timestamp ASC
		";
		
		} else {
		$query = "SELECT Timestamp, 
		DATE_FORMAT(Timestamp, '%d') as dia,
		ROUND(AVG($fields[1]),2) as $fields[1],
		ROUND(AVG($fields[2]),2) as $fields[2],
		ROUND(SUM($fields[3]),2) as $fields[3]
		FROM `$station_tbname` 
		WHERE (     (TIMESTAMP >DATE_SUB( DATE_FORMAT(NOW(), '%Y-%m-%d'), INTERVAL $interval DAY))
		        AND (TIMESTAMP <= DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-%d'), INTERVAL 0 DAY))
		      )	
		GROUP BY $fields[5](timestamp)
		ORDER BY Timestamp ASC ";
		}
	
/*$query = "SELECT Timestamp, 
DATE_FORMAT(Timestamp, '%d') as dia,
ROUND(AVG($fields[1]),2) as $fields[1],
ROUND(AVG($fields[2]),2) as $fields[2],
ROUND(SUM($fields[3]),2) as $fields[3]
FROM `$station_tbname` 
-- WHERE (TO_DAYS(NOW()) - TO_DAYS(Timestamp) <= $interval)

GROUP BY $fields[5](Timestamp)
ORDER BY Timestamp ASC
";*/
}
//$query = "SET time_zone = '-3:00'; SELECT TIMESTAMP,ROUND(AVG($field),3) as Air FROM `$station_tbname` WHERE (
TO_DAYS(NOW()) - TO_DAYS(Timestamp) <= $interval) GROUP BY HOUR(Timestamp) ORDER BY Timestamp ASC ";

//$query = "SELECT (UNIX_TIMESTAMP(Timestamp)) AS Timestamp, $field FROM `$station_tbname` WHERE (TO_DAYS(NOW())
 - TO_DAYS(Timestamp) <= $interval) limit 10";

/* Seleção de dados da tabela. */
$list_data = $mysql->sql_query($query);


/*while($variable = mysql_fetch_object($list_data)){
print $variable->Timestamp .'<br> ';

}*/
//$list_data = $mysql->sql_query("SELECT Timestamp, $field FROM `sr311` WHERE Timestamp >= Timestamp(current_dat
e()-2) ORDER BY Timestamp LIMIT 10");
/* Desconecta do Banco de Dados */
$mysql->desconecta();

/*  Manipulando retorno MySQL  */
 $retorno = '{'; //Cabeçalho do csv
$i = 0;

$freq = 3600; //seconds intervalo entre os dados
// limpando as variaveis
$vars[$fields[1]] = Array(); $vars[$fields[2]] = Array(); $vars[$fields[3]] = Array();
while($variable = mysql_fetch_object($list_data)){


$j = 0; // controle de fill gap	
  if ($i==0) {
 	$referencia = $variable->Timestamp;
  	$stamp = $referencia;
    // [[xAxis,yAxix],...] //$retorno .=  '['. $variable->Timestamp .'000,'.$variable->$field . '],';
    array_push($vars[$fields[1]],$variable->$fields[1]);
    array_push($vars[$fields[2]],$variable->$fields[2]);
    array_push($vars[$fields[3]],$variable->$fields[3]);
  } else  {

   $stamp += $freq;
   //print "$i Stamp e variable ".$stamp.'  '. $variable->Timestamp.'<br>';
   
   while ($stamp < (($variable->Timestamp)-$freq)){
   		$j += 1; 
        $stamp +=$freq;
        
        // [[xAxis,yAxix],...] //$retorno .= '['. $stamp .'000,null],';
        $retorno .= 'null,';
    array_push($vars[$fields[1]],'null');
    array_push($vars[$fields[2]],'null');
    array_push($vars[$fields[3]],'null');
      
   }

 
  // [[xAxis,yAxix],...] //$retorno .=  '['. $variable->Timestamp .'000,'.$variable->$field . '],';
    array_push($vars[$fields[1]],$variable->$fields[1]);
    array_push($vars[$fields[2]],$variable->$fields[2]);
    array_push($vars[$fields[3]],$variable->$fields[3]);
  }
  
   $i +=(1+$j); 
}

 
 
$resultado [ $fields[1] ] = $vars[$fields[1]];
$resultado [ $fields[2] ] = $vars[$fields[2]];
$resultado [ $fields[3] ] = $vars[$fields[3]];
$outresultado = json_encode ( $resultado ) ;
echo $outresultado ;


?>
