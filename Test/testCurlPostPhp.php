<?php
//
// A very simple PHP example that sends a HTTP POST to a remote site
//

include "./tmp/vendor/autoload.php";

$ch = curl_init();

curl_setopt_array($ch, array(
  CURLOPT_URL => "http://monitorps.sardegnasalute.it/monitorps/MonitorServlet?idProntoSoccorso=164",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => "idMacroArea=null&codiceAziendaSanitaria=null&idAreaVasta=null&idPresidio=null&tipoProntoSoccorso=null&vicini=null&xhr=null",
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache",
    "content-type: application/x-www-form-urlencoded"
  ),
));

$server_output = curl_exec ($ch);

curl_close ($ch);

$jsonObject = new JsonPath\JsonObject($server_output);

$jsonPathExpr = '$..view';

$res = $jsonObject->get($jsonPathExpr);
print $res[0];

$dom = new DOMDocument();
@$dom->loadHTML($res[0]);

$xpath = new DOMXPath($dom);

$xpath_for_parsing = '/html/body/div/div/div/div/table/tbody/tr[6]/td[5]';

$colorWaitingNumber = $xpath->query($xpath_for_parsing);

$theValue =  'N.D.';
foreach( $colorWaitingNumber as $node )
{
  $theValue = $node->nodeValue;
}

print $theValue;
print " ****** ";

?>
