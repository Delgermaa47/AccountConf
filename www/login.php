<?php
$url = "http://172.29.2.30/Auth/Login";

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = array(
   "Accept: application/json",
   "Content-Type: application/json",
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

$data = <<<DATA
{
  "id": -99999999,
  "loginId": "deegitest",
  "lang": 0,
  "IMIE": "imie",
  "captcha": "captcha",
  "roldId": "6",
  "secRule": {
    "SPSecRuleId": 3,
    "SPPassParam": [
      "Test#123"
    ]
  }
}
DATA;

curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

$resp = curl_exec($curl);
curl_close($curl);

echo $resp;
?>