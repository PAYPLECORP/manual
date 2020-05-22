$header_data = array(
    'Content-Type: application/json',
    'Cache-Control: no-cache',
    'referer: https://aaa.com'
);

$data_string = '{
  "cst_id": "test",
  "custKey": "abcd1234567890"
}';

$ch = curl_init('https://testcpay.payple.kr/php/auth.php');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSLVERSION, 4);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header_data);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
$res = curl_exec($ch);
echo $res;
