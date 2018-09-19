## PHP cURL 
```php
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://testcpay.payple.kr/php/auth.php",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => "{\n\"cst_id\": \"test\",\n\"custKey\": \"abcd1234567890\"}",
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache",
    "content-type: application/json"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}
```

## PHP HttpRequest
```php
$request = new HttpRequest();
$request->setUrl('https://testcpay.payple.kr/php/auth.php');
$request->setMethod(HTTP_METH_POST);

$request->setHeaders(array(
  'cache-control' => 'no-cache',
  'content-type' => 'application/json'
));

$request->setBody('{
"cst_id": "test",
"custKey": "abcd1234567890"
}');

try {
  $response = $request->send();

  echo $response->getBody();
} catch (HttpException $ex) {
  echo $ex;
}
```

## PHP pecl_http
```php
$client = new http\Client;
$request = new http\Client\Request;

$body = new http\Message\Body;
$body->append('{
"cst_id": "test",
"custKey": "abcd1234567890"
}');

$request->setRequestUrl('https://testcpay.payple.kr/php/auth.php');
$request->setRequestMethod('POST');
$request->setBody($body);

$request->setHeaders(array(
  'cache-control' => 'no-cache',
  'content-type' => 'application/json'
));

$client->enqueue($request)->send();
$response = $client->getResponse();

echo $response->getBody();
```

## HTTP Request
```http
POST /php/auth.php HTTP/1.1
Host: testcpay.payple.kr
Content-Type: application/json
Cache-Control: no-cache

{
"cst_id": "test",
"custKey": "abcd1234567890"
}
```

## cURL
```curl
curl -X POST \
  https://testcpay.payple.kr/php/auth.php \
  -H 'cache-control: no-cache' \
  -H 'content-type: application/json' \
  -d '{
"cst_id": "test",
"custKey": "abcd1234567890"
}'
```

## Shell cURL
```curl
curl --request POST \
  --url https://testcpay.payple.kr/php/auth.php \
  --header 'cache-control: no-cache' \
  --header 'content-type: application/json' \
  --data '{\n"cst_id": "test",\n"custKey": "abcd1234567890"\n}'
```

## Python http.client(Python 3)
```python
import http.client

conn = http.client.HTTPConnection("testcpay.payple.kr")

payload = "{\n\"cst_id\": \"test\",\n\"custKey\": \"abcd1234567890\"\n}"

headers = {
    'content-type': "application/json",
    'cache-control': "no-cache"
    }

conn.request("POST", "/php/auth.php", payload, headers)

res = conn.getresponse()
data = res.read()

print(data.decode("utf-8"))
```

## Python Requests
```python
import requests

url = "https://testcpay.payple.kr/php/auth.php"

payload = "{\n\"cst_id\": \"test\",\n\"custKey\": \"abcd1234567890\"\n}"
headers = {
    'content-type': "application/json",
    'cache-control': "no-cache"
    }

response = requests.request("POST", url, data=payload, headers=headers)

print(response.text)
```

## Java OK HTTP
```java
OkHttpClient client = new OkHttpClient();

MediaType mediaType = MediaType.parse("application/json");
RequestBody body = RequestBody.create(mediaType, "{\n\"cst_id\": \"test\",\n\"custKey\": \"abcd1234567890\"\n}");
Request request = new Request.Builder()
  .url("https://testcpay.payple.kr/php/auth.php")
  .post(body)
  .addHeader("content-type", "application/json")
  .addHeader("cache-control", "no-cache")
  .build();

Response response = client.newCall(request).execute();
```

## Java Unirest
```java
HttpResponse<String> response = Unirest.post("https://testcpay.payple.kr/php/auth.php")
  .header("content-type", "application/json")
  .header("cache-control", "no-cache")
  .body("{\n\"cst_id\": \"test\",\n\"custKey\": \"abcd1234567890\"\n}")
  .asString();
```

## JavaScript jQuery AJAX
```javascript
var settings = {
  "async": true,
  "crossDomain": true,
  "url": "https://testcpay.payple.kr/php/auth.php",
  "method": "POST",
  "headers": {
    "content-type": "application/json",
    "cache-control": "no-cache"
  },
  "processData": false,
  "data": "{\n\"cst_id\": \"test\",\n\"custKey\": \"abcd1234567890\"\n}"
}

$.ajax(settings).done(function (response) {
  console.log(response);
});
```

## JavaScript XHR
```javascript
var data = JSON.stringify({
  "cst_id": "test",
  "custKey": "abcd1234567890"
});

var xhr = new XMLHttpRequest();
xhr.withCredentials = true;

xhr.addEventListener("readystatechange", function () {
  if (this.readyState === 4) {
    console.log(this.responseText);
  }
});

xhr.open("POST", "https://testcpay.payple.kr/php/auth.php");
xhr.setRequestHeader("content-type", "application/json");
xhr.setRequestHeader("cache-control", "no-cache");

xhr.send(data);
```

## NodeJS Native
```javascript
var http = require("https");

var options = {
  "method": "POST",
  "hostname": "testcpay.payple.kr",
  "port": null,
  "path": "/php/auth.php",
  "headers": {
    "content-type": "application/json",
    "cache-control": "no-cache"
  }
};

var req = http.request(options, function (res) {
  var chunks = [];

  res.on("data", function (chunk) {
    chunks.push(chunk);
  });

  res.on("end", function () {
    var body = Buffer.concat(chunks);
    console.log(body.toString());
  });
});

req.write(JSON.stringify({ cst_id: 'test',
  custKey: 'abcd1234567890' }));
req.end();
```

## NodeJS Request
```javascript
var request = require("request");

var options = { method: 'POST',
  url: 'https://testcpay.payple.kr/php/auth.php',
  headers: 
   { 'cache-control': 'no-cache',
     'content-type': 'application/json' },
  body: 
   { cst_id: 'test',
     custKey: 'abcd1234567890' },
  json: true };

request(options, function (error, response, body) {
  if (error) throw new Error(error);

  console.log(body);
});
```

## NodeJS Unirest
```javascript
var unirest = require("unirest");

var req = unirest("POST", "https://testcpay.payple.kr/php/auth.php");

req.headers({
  "cache-control": "no-cache",
  "content-type": "application/json"
});

req.type("json");
req.send({
  "cst_id": "test",
  "custKey": "abcd1234567890"
});

req.end(function (res) {
  if (res.error) throw new Error(res.error);

  console.log(res.body);
});
```
