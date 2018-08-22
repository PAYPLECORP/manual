# PAYPLE 계좌간편결제 
PAYPLE 은 대부분의 온라인상점에서 제공하는 '실시간계좌이체'의 불편함을 해결하는 계좌출금 기반의 결제서비스 입니다.<br>
ARS 인증만으로 결제가 완료되기 때문에 별도 앱설치, 보안카드, 공인인증서 등이 필요없습니다.<br>
그리고 PAYPLE 은 단건결제 뿐 아니라 다양한 계좌기반의 결제서비스를 제공하고 있습니다. 
![Alt text](/img/payple.png)
## 공통 정의사항 
* 인코딩 : UTF-8 <br>
* SSL 보안통신 필수 <br>
* 메세지 포맷 : JSON <br>
## 준비 
[cPayPayple](/cPayPayple) 폴더를 가맹점 Web Root 에 저장하고 각 파일을 가맹점 환경에 맞는 개발언어로 수정해주시면 됩니다. 
> 가급적 폴더명 변경없이 이용해주세요. 
## 샘플 페이지 
참고 가능한 [샘플 페이지](/sample) 폴더입니다. 
## 가맹점 인증 
* Payple 서비스를 이용하기 위해서는 가맹점 계약을 통한 인증이 필요하지만 계약 전 테스트 계정을 통해 개발진행이 가능합니다. 계약 완료 후 Payple 에서 가맹점에 아래의 키를 발급합니다. 
  * cst_id (가맹점 식별을 위한 가맹점 ID)
  * custKey (API 통신전문 변조를 방지하기 위한 비밀키)
* 키가 탈취되는 일이 없도록 가맹점 서버사이드 스크립트 (PHP, ASP, JSP 등) 에서 API를 호출하시기 바랍니다.

#### 호출정보
구분 | 테스트 | 운영
---- | ---- | ----
URL | https://testcpay.payple.kr/php/auth.php | https://cpay.payple.kr/php/auth.php
ID | cst_id : test | cst_id : 가맹점 운영 ID 
KEY | custKey : abcd1234567890 | custKey : ID 매칭 Key
비고 | 인증은 진행되지만 출금은 되지 않습니다. | 실제 출금이 되며, 최소금액 1,000원부터 출금 가능합니다. 수수료도 발생합니다.<br>**AWS(아마존웹서비스)에서 AUTH0004 오류 발생 시 가맹점 서버도메인의 REFERER 추가가 필요할 수 있습니다.** 

#### 호출예시 
* 단건결제 - Request 
```html
POST /php/auth.php HTTP/1.1
Host: testcpay.payple.kr
Content-Type: application/json
Cache-Control: no-cache
{
  "cst_id": "test",
  "custKey": "abcd1234567890"
}
```
* 단건결제 - Response
```html
{
  "result": "success",
  "result_msg": "사용자 인증완료",
  "cst_id": "test",
  "custKey": "abcd1234567890",
  "AuthKey": "a688ccb3555c25cd722483f03e23065c3d0251701ad6da895eb2d830bc06e34d",
  "return_url": "https://cpay.payple.kr/php/PayAct.php?ACT_=PAYM"
}
```
* 정기결제 - Request 
```html
POST /php/auth.php HTTP/1.1
Host: testcpay.payple.kr
Content-Type: application/json
Cache-Control: no-cache
{
  "cst_id": "test",
  "custKey": "abcd1234567890",
  "PCD_REGULER_FLAG": "Y"
}
```
* 정기결제 - Response
```html
{
  "result": "success",
  "result_msg": "사용자 인증완료",
  "cst_id": "test",
  "custKey": "abcd1234567890",
  "AuthKey": "a688ccb3555c25cd722483f03e23065c3d0251701ad6da895eb2d830bc06e34d",
  "return_url": "https://cpay.payple.kr/php/RePayAct.php?ACT_=PAYM"
}
```
* 간편결제 - Request 
```html
POST /php/auth.php HTTP/1.1
Host: testcpay.payple.kr
Content-Type: application/json
Cache-Control: no-cache
{
  "cst_id": "test",
  "custKey": "abcd1234567890",
  "PCD_SIMPLE_FLAG": "Y"
}

```
* 간편결제 - Response
```html
{
  "result": "success",
  "result_msg": "사용자 인증완료",
  "cst_id": "test",
  "custKey": "abcd1234567890",
  "AuthKey": "a688ccb3555c25cd722483f03e23065c3d0251701ad6da895eb2d830bc06e34d",
   "return_url": "https://cpay.payple.kr/php/SimplePayAct.php?ACT_=PAYM"
}
```

## 결제요청 
### 1. 단건결제 
* HTML Form Submission 을 이용합니다. <br>
* 아래 소스코드를 가맹점 주문페이지에 추가합니다.
* 자세한 내용은 [order.html 샘플](/sample/order.html)을 참고하시면 됩니다. 
```html
<!-- payple js 호출. 테스트/운영 선택 -->
<script src="https://testcpay.payple.kr/js/cpay.payple.1.0.0.js"></script> <!-- 테스트 -->
<script src="https://cpay.payple.kr/js/cpay.payple.1.0.0.js"></script> <!-- 운영 -->

<!-- 가맹점 주문페이지 '결제하기' 버튼 액션 -->
<script>
$(document).ready( function () {
  $('#payAction').on('click', function (event) {		
    PaypleCpayAuthCheck();		
    event.preventDefault();		
  });	
});
</script>

<form id="CpayForm" name="CpayForm" action="" method="post">
  <input type="hidden" name="PCD_CST_ID" id="PCD_CST_ID">  
  <input type="hidden" name="PCD_CUST_KEY" id="PCD_CUST_KEY">  
  <input type="hidden" name="PCD_AUTH_KEY" id="PCD_AUTH_KEY">  
  <input type="hidden" name="PCD_PAY_REQKEY" id="PCD_PAY_REQKEY">  
  <input type="hidden" name="PCD_PAY_COFURL" id="PCD_PAY_COFURL">  
  <input type="hidden" name="PCD_PAY_TYPE" id="PCD_PAY_TYPE" value="transfer">  
  <input type="hidden" name="PCD_PAY_WORK" id="PCD_PAY_WORK" value="CERT">  
  <input type="hidden" name="PCD_PAYER_ID" id="PCD_PAYER_ID">  
  <input type="hidden" name="PCD_PAYER_NO" id="PCD_PAYER_NO" value="<?=$buyer_no?>">  
  <input type="hidden" name="PCD_PAYER_NAME" id="PCD_PAYER_NAME" value="<?=$buyer_name?>">
  <input type="hidden" name="PCD_PAYER_HP" id="PCD_PAYER_HP" value="<?=$buyer_hp?>">  
  <input type="hidden" name="PCD_PAYER_EMAIL" id="PCD_PAYER_EMAIL" value="<?=$buyer_email?>">
  <input type="hidden" name="PCD_PAY_GOODS" id="PCD_PAY_GOODS" value="<?=$buy_goods?>">
  <input type="hidden" name="PCD_PAY_YEAR" id="PCD_PAY_YEAR" value="<?=$pay_year?>">
  <input type="hidden" name="PCD_PAY_MONTH" id="PCD_PAY_MONTH" value="<?=$pay_month?>">
  <input type="hidden" name="PCD_PAY_TOTAL" id="PCD_PAY_TOTAL" value="<?=$buy_total?>">
  <input type="hidden" name="PCD_PAY_OID" id="PCD_PAY_OID" value="<?=$order_num?>">
  <input type="hidden" name="PCD_TAXSAVE_FLAG" id="PCD_TAXSAVE_FLAG" value="<?=$is_taxsave?>">
  <input type="hidden" name="PCD_REGULER_FLAG" id="PCD_REGULER_FLAG" value="<?=$is_reguler?>">
  <input type="hidden" name="PCD_PAY_BANK" id="PCD_PAY_BANK">
  <input type="hidden" name="PCD_PAY_BANKNUM" id="PCD_PAY_BANKNUM">
  <input type="hidden" name="PCD_PAY_TIME" id="PCD_PAY_TIME">
  <input type="hidden" name="PCD_RST_URL" id="PCD_RST_URL" value="/order_result.html">
  <input type="hidden" name="PCD_PAY_RST" id="PCD_PAY_RST">
  <input type="hidden" name="PCD_PAY_MSG" id="PCD_PAY_MSG">
  <input type="hidden" name="PCD_TAXSAVE_RST" id="PCD_TAXSAVE_RST">
  <input type="hidden" name="REMOTE_IP" id="REMOTE_IP">
</form>

<!-- iframe 팝업을 위한 태그 -->
<div id="layer_cpay" name="layer_cpay" style="position:absolute; z-index:100; display:none; width:$(window).width(); height:$(window).height(); top:0; left:0 ;margin-top:0px; margin-left:0px;">
  <iframe id="cpay_ifr" name="cpay_ifr" style="width:450px; height:100%; position:absolute; z-index:200; background:white;" frameborder="0" scrolling="auto"></iframe>
</div>
```

파라미터 ID | 설명 | 가맹점 | PAYPLE
:----: | :----: | :----: | :----:
PCD_CST_ID | 가맹점 ID |  | O
PCD_CUST_KEY | 가맹점 식별을 위한 비밀키 |  | O
PCD_AUTH_KEY | 결제요청을 위한 Transaction 키 | | O
PCD_PAY_REQKEY | 결제생성후 승인을 위한 키 | | O 
PCD_PAY_COFURL | 결제생성, 승인후 리턴 URL | | O 
PCD_PAY_TYPE | 결제수단<br>(transfer=계좌 / card=카드) | O |  
PCD_PAY_WORK | 결제요청방식<br>(CERT=결제생성 후 승인 / PAY=즉시승인) | O |  
PCD_PAYER_ID | 결제고객 고유 ID | | O 
PCD_PAYER_NO | 결제고객 고유 번호 | O | 
PCD_PAYER_NAME | 결제고객명 | O | 
PCD_PAYER_HP | 결제고객 휴대폰번호 | O |  
PCD_PAYER_EMAIL | 결제고객 이메일 | O |  
PCD_PAY_GOODS | 상품명 | O |  
PCD_PAY_YEAR | 정기결제 적용연도<br>(정기결제만 해당) | O |  
PCD_PAY_MONTH | 정기결제 적용월<br>(정기결제만 해당) | O |  
PCD_PAY_TOTAL | 결제금액 | O |  
PCD_PAY_OID | 주문번호<br>(NULL 인 경우 페이플에서 임의생성) | O |  
PCD_TAXSAVE_FLAG | 현금영수증 발행여부<br>(Y=발행 / N=미발행) | O |  
PCD_REGULER_FLAG | 정기결제 여부<br>(Y=정기 / N=단건) | O | 
PCD_PAY_BANK | 결제 은행 |  | O 
PCD_PAY_BANKNUM | 결제 계좌번호 |  | O 
PCD_PAY_TIME | 결제완료시간<br>(예: 20180110152911) |  | O 
PCD_RST_URL | 가맹점 결제완료 페이지 경로<br>(형식은 가맹점 URL을 제외한 /결과받을경로/파일명) | O |  
PCD_PAY_RST | 결제 성공여부<br>(Y / N) |  | O 
PCD_PAY_MSG | 결과메세지 |  | O 
PCD_TAXSAVE_RST | 현금영수증 발행결과 |  | O 
REMOTE_IP | 결제고객 접속 IP |  | O 

#### 1-1. 결제생성 후 승인(PCD_PAY_WORK : CERT) 
* 가맹점의 최종 승인 후에 결제를 진행하며 REST Request 방식으로 진행합니다. 
* Request 예시 
```html
<!-- 가맹점 인증 -->
POST /php/auth.php HTTP/1.1
Host: testcpay.payple.kr
Content-Type: application/json
Cache-Control: no-cache
{
  "cst_id": "test",
  "custKey": "abcd1234567890"
}

<!-- 결제요청 -->
POST /php/PayConfirmAct.php?ACT_=PAYM HTTTP/1.1
Host: testcpay.payple.kr
Content-Type: application/json
Cache-Control: no-cache
{
  "PCD_CST_ID": "test",										
  "PCD_CUST_KEY": "abcd1234567890",								
  "PCD_AUTH_KEY": "a688ccb3555c25cd722483f03e23065c3d0251701ad6da895eb2d830bc06e34d", 
  "PCD_PAY_REQKEY": "RmFBWWFBTWNS9qNTgzU2xdd2XRNHR2",					
  "PCD_PAYER_ID": "NS9qNTgzU2xRNHR2RmFBWWFBTWk5UT09"
}
```
* Request 파라미터 설명 

파라미터 ID | 설명 | 필수 | 비고
:----: | :----: | :----: | :----:
PCD_CST_ID | 가맹점 ID | O | 
PCD_CUST_KEY | 가맹점 식별을 위한 비밀키 | O | 
PCD_AUTH_KEY | 결제요청을 위한 Transaction 키 | O | 
PCD_PAY_REQKEY | 최종 승인요청용 키 | O | 
PCD_PAYER_ID | 결제고객 고유 ID | O | 

#### 1-2. 즉시 승인(PCD_PAY_WORK : PAY) 
* 가맹점의 최종 승인없이 즉시 결제를 진행하며 별도 Request 는 없습니다.  

### 2. 정기결제
* 최초 1회 결제는 [1. 단건결제](#1-단건결제)와 동일하며 이후 결제는 REST Request 방식으로 진행합니다.
* Request 예시 
```html
<!-- 가맹점 인증 -->
POST /php/auth.php HTTP/1.1
Host: testcpay.payple.kr
Content-Type: application/json
Cache-Control: no-cache
{
  "cst_id": "test",
  "custKey": "abcd1234567890",
  "PCD_REGULER_FLAG": "Y"
}

<!-- 결제요청  -->
POST /php/RePayAct.php?ACT_=PAYM HTTTP/1.1
Host: testcpay.payple.kr
Content-Type: application/json
Cache-Control: no-cache
{
   "PCD_CST_ID": "test",
   "PCD_CUST_KEY": "abcd1234567890",
   "PCD_AUTH_KEY": "a688ccb3555c25cd722483f03e23065c3d0251701ad6da895eb2d830bc06e34d",
   "PCD_PAY_TYPE": "transfer",							
   "PCD_PAYER_NO": "2324",
   "PCD_PAYER_ID": "NS9qNTgzU2xRNHR2RmFBWWFBTWk5UT09",
   "PCD_PAYER_NAME": "홍길동",
   "PCD_PAYER_HP": "01022224444",
   "PCD_PAYER_BIRTH": "19900211",
   "PCD_PAY_BANK": "081",
   "PCD_PAY_BANKNUM": "2881204040404",
   "PCD_PAY_GOODS": "정기구독",
   "PCD_PAY_YEAR": "2018",	
   "PCD_PAY_MONTH": "04",	
   "PCD_PAY_TOTAL": "1000",
   "PCD_PAY_OID": "test201804000001",
   "PCD_TAXSAVE_FLAG": "Y",
   "PCD_TAXSAVE_TRADE": "personal",
   "PCD_TAXSAVE_IDNUM": "01022224444",
   "PCD_REGULER_FLAG": "Y",
   "PCD_PAYER_EMAIL": "test@test.com"
}
```
* Request 파라미터 설명 

파라미터 ID | 설명 | 필수 | 비고
:----: | :----: | :----: | :----:
PCD_CST_ID | 가맹점 ID | O | 
PCD_CUST_KEY | 가맹점 식별을 위한 비밀키 | O | 
PCD_AUTH_KEY | 결제요청을 위한 Transaction 키 | O | 
PCD_PAY_TYPE | 결제수단<br>(transfer = 계좌 / card = 카드) | O | 
PCD_PAYER_NO | 가맹점의 결제고객 고유번호 | O | 
PCD_PAYER_ID | 결제 빌링키<br>(해당 키를 통해 정기, 간편결제 시 결제요청) | O | 
PCD_PAYER_NAME | 결제고객명 | ▵ | PCD_PAYER_ID 미입력시 필수 
PCD_PAYER_HP | 결제고객 휴대폰번호 | ▵ | PCD_PAYER_ID 미입력시 필수 
PCD_PAYER_BIRTH | 결제고객 생년월일 8자리 | ▵ | PCD_PAYER_ID 미입력시 필수 
PCD_PAY_BANK | 결제 은행코드 | ▵ | PCD_PAYER_ID 미입력시 필수 
PCD_PAY_BANKNUM | 결제 계좌번호 | ▵ | PCD_PAYER_ID 미입력시 필수 
PCD_PAY_GOODS | 상품명 | O | 
PCD_PAY_YEAR | 과금연도 | O | 
PCD_PAY_MONTH | 과금월 | O | 
PCD_PAY_TOTAL | 결제금액 | O | 
PCD_PAY_OID | 주문번호 | O | 
PCD_TAXSAVE_FLAG | 현금영수증 발행 여부<br>(Y=발행 / N=미발행) | O | 
PCD_TAXSAVE_TRADE | 현금영수증 발행 타입<br>(personal=소득공제 / company=지출증빙) |  |  
PCD_TAXSAVE_IDNUM | 현금영수증 발행 번호<br>(휴대폰번호, 사업자번호) |  | 
PCD_REGULER_FLAG | 정기결제 여부 | O | 
PCD_PAYER_EMAIL | 결제고객 이메일 | O | 

### 3. 계좌등록 간편결제 
* 최초 1회 결제는 [1. 단건결제](#1-단건결제)와 동일하며 이후 결제는 REST Request 방식으로 진행합니다.
* Request 예시 
```html
<!-- 가맹점 인증 -->
POST /php/auth.php HTTP/1.1
Host: testcpay.payple.kr
Content-Type: application/json
Cache-Control: no-cache
{
  "cst_id": "test",
  "custKey": "abcd1234567890",
  "PCD_SIMPLE_FLAG": "Y"
}

<!-- 결제요청  -->
POST /php/SimplePayAct.php?ACT_=PAYM HTTTP/1.1
Host: testcpay.payple.kr
Content-Type: application/json
Cache-Control: no-cache
{
   "PCD_CST_ID": "test",
   "PCD_CUST_KEY": "abcd1234567890",
   "PCD_AUTH_KEY": "a688ccb3555c25cd722483f03e23065c3d0251701ad6da895eb2d830bc06e34d",
   "PCD_PAY_TYPE": "transfer",							
   "PCD_PAYER_NO": "2324",
   "PCD_PAYER_ID": "NS9qNTgzU2xRNHR2RmFBWWFBTWk5UT09",
   "PCD_PAYER_NAME": "홍길동",
   "PCD_PAYER_HP": "01022224444",
   "PCD_PAYER_BIRTH": "19900211",
   "PCD_PAY_BANK": "081",
   "PCD_PAY_BANKNUM": "2881204040404",
   "PCD_PAY_GOODS": "정기구독",
   "PCD_PAY_TOTAL": "1000",
   "PCD_PAY_OID": "test201804000001",
   "PCD_TAXSAVE_FLAG": "Y",
   "PCD_TAXSAVE_TRADE": "personal",
   "PCD_TAXSAVE_IDNUM": "01022224444",
   "PCD_SIMPLE_FLAG": "Y",
   "PCD_PAYER_EMAIL": "test@test.com"
}
```
* Request 파라미터 설명 

파라미터 ID | 설명 | 필수 | 비고
:----: | :----: | :----: | :----:
PCD_CST_ID | 가맹점 ID | O | 
PCD_CUST_KEY | 가맹점 식별을 위한 비밀키 | O | 
PCD_AUTH_KEY | 결제요청을 위한 Transaction 키 | O | 
PCD_PAY_TYPE | 결제수단<br>(transfer = 계좌 / card = 카드) | O | 
PCD_PAYER_NO | 가맹점의 결제고객 고유번호 | O | 
PCD_PAYER_ID | 결제 빌링키<br>(해당 키를 통해 정기, 간편결제 시 결제요청) | O | 
PCD_PAYER_NAME | 결제고객명 | ▵ | PCD_PAYER_ID 미입력시 필수 
PCD_PAYER_HP | 결제고객 휴대폰번호 | ▵ | PCD_PAYER_ID 미입력시 필수 
PCD_PAYER_BIRTH | 결제고객 생년월일 8자리 | ▵ | PCD_PAYER_ID 미입력시 필수 
PCD_PAY_BANK | 결제 은행코드 | ▵ | PCD_PAYER_ID 미입력시 필수 
PCD_PAY_BANKNUM | 결제 계좌번호 | ▵ | PCD_PAYER_ID 미입력시 필수 
PCD_PAY_GOODS | 상품명 | O | 
PCD_PAY_TOTAL | 결제금액 | O | 
PCD_PAY_OID | 주문번호 | O | 
PCD_TAXSAVE_FLAG | 현금영수증 발행 여부<br>(Y=발행 / N=미발행) | O | 
PCD_TAXSAVE_TRADE | 현금영수증 발행 타입<br>(personal=소득공제 / company=지출증빙) |  |  
PCD_TAXSAVE_IDNUM | 현금영수증 발행 번호<br>(휴대폰번호, 사업자번호) |  | 
PCD_SIMPLE_FLAG | 간편결제 여부 | O | 
PCD_PAYER_EMAIL | 결제고객 이메일 | O |

## 결제결과 수신  
* 아래 소스코드를 가맹점 결제완료 페이지에 추가하고 가맹점 환경에 맞는 개발언어로 수정해주세요.
* 자세한 내용은 [order_result.html 샘플](/sample/order_result.html)을 참고하시면 됩니다. 
```php
<?
$PCD_PAY_RST = (isset($_POST['PCD_PAY_RST'])) ? $_POST['PCD_PAY_RST'] : ""; 
$PCD_PAY_MSG = (isset($_POST['PCD_PAY_MSG'])) ? $_POST['PCD_PAY_MSG'] : ""; 
$PCD_AUTH_KEY = (isset($_POST['PCD_AUTH_KEY'])) ? $_POST['PCD_AUTH_KEY'] : "";
$PCD_PAY_REQKEY = (isset($_POST['PCD_PAY_REQKEY'])) ? $_POST['PCD_PAY_REQKEY'] : "";
$PCD_PAY_COFURL = (isset($_POST['PCD_PAY_COFURL'])) ? $_POST['PCD_PAY_COFURL'] : "";
$PCD_PAY_OID = (isset($_POST['PCD_PAY_OID'])) ? $_POST['PCD_PAY_OID'] : "";         
$PCD_PAY_TYPE = (isset($_POST['PCD_PAY_TYPE'])) ? $_POST['PCD_PAY_TYPE'] : "";      
$PCD_PAY_WORK = (isset($_POST['PCD_PAY_WORK'])) ? $_POST['PCD_PAY_WORK'] : "";      
$PCD_PAYER_ID = (isset($_POST['PCD_PAYER_ID'])) ? $_POST['PCD_PAYER_ID'] : "";      
$PCD_PAYER_NO = (isset($_POST['PCD_PAYER_NO'])) ? $_POST['PCD_PAYER_NO'] : "";      
$PCD_PAYER_NAME = (isset($_POST['PCD_PAYER_NAME'])) ? $_POST['PCD_PAYER_NAME'] : "";
$PCD_PAYER_HP = (isset($_POST['PCD_PAYER_HP'])) ? $_POST['PCD_PAYER_HP'] : "";     
$PCD_PAYER_EMAIL = (isset($_POST['PCD_PAYER_EMAIL'])) ? $_POST['PCD_PAYER_EMAIL'] : "";
$PCD_REGULER_FLAG = (isset($_POST['PCD_REGULER_FLAG'])) ? $_POST['PCD_REGULER_FLAG'] : "";
$PCD_PAY_YEAR = (isset($_POST['PCD_PAY_YEAR'])) ? $_POST['PCD_PAY_YEAR'] : ""; 
$PCD_PAY_MONTH = (isset($_POST['PCD_PAY_MONTH'])) ? $_POST['PCD_PAY_MONTH'] : "";
$PCD_PAY_GOODS = (isset($_POST['PCD_PAY_GOODS'])) ? $_POST['PCD_PAY_GOODS'] : "";
$PCD_PAY_TOTAL = (isset($_POST['PCD_PAY_TOTAL'])) ? $_POST['PCD_PAY_TOTAL'] : "";
$PCD_PAY_BANK = (isset($_POST['PCD_PAY_BANK'])) ? $_POST['PCD_PAY_BANK'] : "";   
$PCD_PAY_BANKNUM = (isset($_POST['PCD_PAY_BANKNUM'])) ? $_POST['PCD_PAY_BANKNUM'] : "";
$PCD_PAY_TIME = (isset($_POST['PCD_PAY_TIME'])) ? $_POST['PCD_PAY_TIME'] : "";         
$PCD_TAXSAVE_FLAG = (isset($_POST['PCD_TAXSAVE_FLAG'])) ? $_POST['PCD_TAXSAVE_FLAG'] : "";
$PCD_TAXSAVE_RST = (isset($_POST['PCD_TAXSAVE_RST'])) ? $_POST['PCD_TAXSAVE_RST'] : "";   
?>
```

* 파라미터 설명 

파라미터 ID | 설명 | 예시
:----: | :----: | :----: 
PCD_PAY_RST | 결제요청 결과 | success / error 
PCD_PAY_MSG | 결제요청 결과 메세지 | 출금이체완료 / 실패 등 
PCD_AUTH_KEY | 결제요청을 위한 Transaction 키 | a688ccb3... 
PCD_PAY_REQKEY | 결제생성후 승인을 위한 키 | RmFBWWFBTWNS9qNTgzU2xdd2XRNHR2
PCD_PAY_COFURL | 결제생성, 승인후 리턴 URL | https://cpay.payple.kr/php/PayConfirmAct.php 
PCD_PAY_OID | 주문번호 | test201804000001
PCD_PAY_TYPE | 결제수단 | transfer / card
PCD_PAY_WORK | 결제요청방식 | CERT / PAY 
PCD_PAYER_ID | 결제 빌링키<br>(정기결제, 간편결제만 해당) | NS9qNTgzU2xRNHR2RmFBWWFBTWk5UT09
PCD_PAYER_NO | 결제고객 고유번호 | 1234 
PCD_PAYER_NAME | 결제고객명 | 홍길동 
PCD_PAYER_HP | 결제고객 휴대폰번호 | 01012345678
PCD_PAYER_EMAIL | 결제고객 이메일 | help@payple.kr 
PCD_REGULER_FLAG | 정기결제 여부 | Y / N
PCD_PAY_YEAR | 과금연도<br>(정기결제만 해당) | 2018 
PCD_PAY_MONTH | 과금월<br>(정기결제만 해당) | 08
PCD_PAY_GOODS | 상품명 | 정기구독 
PCD_PAY_TOTAL | 결제금액 | 1000
PCD_PAY_BANK | 결제 은행코드 | 081
PCD_PAY_BANKNUM | 결제 계좌번호 | 2881204040404
PCD_PAY_TIME | 결제완료 시간 | 20180110152911
PCD_TAXSAVE_FLAG | 현금영수증 발행 여부 | Y / N
PCD_TAXSAVE_RST | 현금영수증 발행 결과 | Y / N 

## 결제결과 조회  
* 결제에 대한 결과조회는 REST Request 방식으로 진행합니다.
* Request 예시 
```html
POST /php/auth.php HTTP/1.1
Host: testcpay.payple.kr
Content-Type: application/json
Cache-Control: no-cache
{
  "cst_id": "test",
  "custKey": "abcd1234567890",
  "PCD_PAYCHK_FLAG": "Y"
}

POST /php/PayChkAct.php HTTTP/1.1
Host: testcpay.payple.kr
Content-Type: application/json
Cache-Control: no-cache
{
   "PCD_CST_ID": "test",
   "PCD_CUST_KEY": "abcd1234567890",
   "PCD_AUTH_KEY": "a688ccb3555c25cd722483f03e23065c3d0251701ad6da895eb2d830bc06e34d",
   "PCD_PAYCHK_FLAG": "Y",
   "PCD_PAY_TYPE": "transfer",
   "PCD_REGULER_FLAG": "Y",							
   "PCD_PAY_YEAR": "2018",	
   "PCD_PAY_MONTH": "04",	
   "PCD_PAY_OID": "test201804000001",
   "PCD_PAY_DATE": 20180502
}
```
* Request 파라미터 설명 

파라미터 ID | 설명 | 필수 | 비고
:----: | :----: | :----: | :----:
PCD_CST_ID | 가맹점 ID | O | 
PCD_CUST_KEY | 가맹점 식별을 위한 비밀키 | O | 
PCD_AUTH_KEY | 결제요청을 위한 Transaction 키 | O | 
PCD_PAYCHK_FLAG | 결과조회 여부 | O | 
PCD_PAY_TYPE | 결제수단<br>(transfer = 계좌 / card = 카드) | O | 
PCD_REGULER_FLAG | 정기결제 여부 |  | 
PCD_PAY_YEAR | 정기결제 과금연도 |  | 
PCD_PAY_MONTH | 정기결제 과금월 |  | 
PCD_PAY_OID | 주문번호 | O | 
PCD_PAY_DATE | 결제요청일자(YYYYMMDD) | O | 

* Response 예시 
```html
{
   "PCD_PAY_RST": "success",
   "PCD_PAY_MSG": "출금이체완료",
   "PCD_PAY_OID": "test201804000001",
   "PCD_PAY_TYPE": "transfer",
   "PCD_PAYER_NO": "1234",
   "PCD_PAYER_NAME": "홍길동",
   "PCD_PAYER_HP": "01022224444",
   "PCD_PAYER_BIRTH": "19900211",
   "PCD_PAY_YEAR" => "2018",
   "PCD_PAY_MONTH" => "05",
   "PCD_PAY_GOODS": "간편상품",
   "PCD_PAY_TOTAL": "1000",
   "PCD_PAY_BANK": "081",
   "PCD_PAY_BANKNUM": "2881204040404",
   "PCD_PAY_TIME" => "20180423130201",
   "PCD_TAXSAVE_RST": "Y",
   "PCD_REGULER_FLAG": "Y"
}
```
* Response 파라미터 설명 

파라미터 ID | 설명 | 필수 | 비고
:----: | :----: | :----: | :----:
PCD_PAY_RST | 결제요청 결과 | success / error 
PCD_PAY_MSG | 결제요청 결과 메세지 | 출금이체완료 / 실패 등 
PCD_PAY_OID | 주문번호 | test201804000001
PCD_PAY_TYPE | 결제수단 | transfer / card
PCD_PAYER_NO | 결제고객 고유번호 | 1234 
PCD_PAYER_NAME | 결제고객명 | 홍길동 
PCD_PAYER_HP | 결제고객 휴대폰번호 | 01012345678
PCD_PAYER_BIRTH | 결제고객 생년월일 8자리 | 19900108
PCD_PAY_YEAR | 과금연도<br>(정기결제만 해당) | 2018 
PCD_PAY_MONTH | 과금월<br>(정기결제만 해당) | 08
PCD_PAY_GOODS | 상품명 | 정기구독 
PCD_PAY_TOTAL | 결제금액 | 1000
PCD_PAY_BANK | 결제 은행코드 | 081
PCD_PAY_BANKNUM | 결제 계좌번호 | 2881204040404
PCD_PAY_TIME | 결제완료 시간 | 20180110152911
PCD_TAXSAVE_RST | 현금영수증 발행 결과 | Y / N 
PCD_REGULER_FLAG | 정기결제 여부 | Y / N

## 서비스가능 은행 및 점검시간 

은행명 | 코드 | 평일, 토요일 | 공휴일
:----: | :----: | :----: | :----:
국민은행 | 004 | 23:40 ~ 00:10 | 23:40 ~ 00:10 
농협 | 011 | 00:00 ~ 00:30 | 00:00 ~ 00:30 
신한은행 | 088 | 23:40 ~ 00:10 | 23:40 ~ 00:10
하나은행 | 081 | 00:00 ~ 01:00 | 00:00 ~ 01:00
외환은행 | 005 | 23:50 ~ 00:10 | 23:50 ~ 00:10 
우체국 | 071 | 00:00 ~ 00:10 | 00:00 ~ 00:10 
새마을금고 | 045 | 23:30 ~ 00:30 | 23:30 ~ 00:30
대구은행 | 031 | 23:50 ~ 01:00 | 23:50 ~ 01:00 
광주은행 | 034 | 23:30 ~ 00:30 | 23:30 ~ 00:30 
경남은행 | 039 | 23:30 ~ 06:00 | 23:30 ~ 06:00 
산업은행 | 002 | 00:00 ~ 03:00 | 00:00 ~ 03:00 
신협 | 048 | 22:00 ~ 08:00 | 22:00 ~ 08:00 

> IBK 기업은행, 우리은행 추후 연결 예정입니다.

## 문의  
* 기술문의 : help@payple.kr 을 통해 보다 자세한 문의가 가능합니다.
* 가입문의 : 페이플 웹사이트 [가입문의하기](https://www.payple.kr) 를 통하시면 가장 빠르게 안내 받으실 수 있습니다. 
