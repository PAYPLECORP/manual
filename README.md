# 페이플 계좌등록 간편결제 
페이플 계좌등록 간편결제는 최초 1회 계좌등록 후 비밀번호만으로 결제 가능한 서비스입니다.<br> 
ARS 인증만으로 계좌등록과 결제가 완료되기 때문에 별도 앱설치, 보안카드, 공인인증서 등이 필요없습니다.<br>
그리고 페이플은 계좌간편결제 뿐 아니라 다양한 계좌기반의 결제서비스를 제공하고 있습니다. 
![Alt text](/img/main_simple.png)

<br><br><br>
## 공통 정의사항 
* 인코딩 : UTF-8 <br>
* SSL 보안통신 필수 <br>
* 메세지 포맷 : JSON <br>
<br><br><br>
## 준비 
[cPayPayple](/cPayPayple) 폴더를 가맹점 Web Root 에 저장하고 각 파일을 가맹점 환경에 맞는 개발언어로 수정해주시면 됩니다. 
> 폴더 위치 변경을 원하시면 아래와 같이 가맹점 인증시 "payple_dir_path" 옵션 설정을 추가하시기 바랍니다. 
```php
/* 예 : cPayPayple 폴더 위치가 /webroot/pg/pay/cPayPayple 인 경우 payple_dir_path = "/pg/pay" */
$post_data = array (
        "custKey" => "abcd1234567890",
        "cst_id" => "test",
        "payple_dir_path" => "/pg/pay"
        );
```
<br><br><br>
## 샘플 페이지 
참고 가능한 [샘플 페이지](/sample) 폴더입니다. 
<br><br><br>
## 가맹점 인증 
* Payple 서비스를 이용하기 위해서는 가맹점 계약을 통한 인증이 필요하지만 계약 전 테스트 계정을 통해 개발진행이 가능합니다. 계약 완료 후 Payple 에서 가맹점에 아래의 키를 발급합니다. 
  * cst_id (가맹점 식별을 위한 가맹점 ID)
  * custKey (API 통신전문 변조를 방지하기 위한 비밀키)
* 키가 탈취되는 일이 없도록 가맹점 서버사이드 스크립트 (PHP, ASP, JSP 등) 에서 API를 호출하시기 바랍니다.
<br><br><br>
#### 호출정보
구분 | 테스트 | 운영
---- | ---- | ----
URL | https://testcpay.payple.kr/php/auth.php | https://cpay.payple.kr/php/auth.php
ID | cst_id : test | cst_id : 가맹점 운영 ID 
KEY | custKey : abcd1234567890 | custKey : ID 매칭 Key
비고 | 인증은 진행되지만 출금은 되지 않습니다. | 실제 출금이 되며, 최소금액 1,000원부터 출금 가능합니다. 수수료도 발생합니다.<br>**AWS(아마존웹서비스)에서 AUTH0004 오류 발생 시 가맹점 서버도메인의 REFERER 추가가 필요할 수 있습니다.** 
* 호출을 위한 [각 언어별 샘플](/sample/language)을 확인해보세요. 
<br><br><br>
#### 호출예시 
* 계좌등록 간편결제 - Request 
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
* 계좌등록 간편결제 - Response
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
<br><br><br>
## 결제요청 
### 1. 공통  
* 최초결제(간편, 정기결제 포함)를 위해서는 가맹점의 HTML Form Submission 을 이용합니다. <br>
![Alt text](/img/onetime_01.png)
* 아래 소스코드를 가맹점 주문페이지에 추가합니다.
* 자세한 내용은 [order_confirm.html 샘플](/sample/order_confirm.html)을 참고하시면 됩니다. 
```html
<!-- payple js 호출. 테스트/운영 선택 -->
<script src="https://testcpay.payple.kr/js/cpay.payple.1.0.1.js"></script> <!-- 테스트 -->
<script src="https://cpay.payple.kr/js/cpay.payple.1.0.1.js"></script> <!-- 운영 -->

<!-- 가맹점 주문페이지 '결제하기' 버튼 액션 -->
<script>
$(document).ready( function () {
  $('#payAction').on('click', function (event) {		
    var obj = new Object();

    obj.PCD_CPAY_VER = "1.0.1";					
    obj.PCD_PAY_TYPE = 'transfer';				
    obj.PCD_PAY_WORK = 'CERT';
		
    //-- PCD_PAYER_ID 는 소스상에 표시하지 마시고 반드시 Server Side Script 를 이용하여 불러오시기 바랍니다. --//
    obj.PCD_PAYER_ID = '<?=$payple_payer_id?>';			// [간편결제/정기결제] 결제자 고유ID (본인인증 된 결제회원 고유 KEY)
    //-------------------------------------------------------------------------------------//		
		
    obj.PCD_PAYER_NO = '<?=$buyer_no?>';  
    obj.PCD_PAYER_NAME = '<?=$buyer_name?>';			// 결제자 이름
    obj.PCD_PAYER_HP = '<?=$buyer_hp?>';			// 결제자 휴대폰 번호
    obj.PCD_PAYER_EMAIL = '<?=$buyer_email?>';			// 결제자 Email
    obj.PCD_PAY_GOODS = '<?=$buy_goods?>';			// 결제 상품
    obj.PCD_PAY_TOTAL = '<?=$buy_total?>';			// 결제 금액
    obj.PCD_PAY_OID = '<?=$order_num?>';			// 주문번호 (미입력 시 임의 생성)
    obj.PCD_REGULER_FLAG = '<?=$is_reguler?>';			// 정기결제 여부 (Y|N)
    obj.PCD_PAY_YEAR = '<?=$pay_year?>';			// [정기결제] 결제 구분 년도 (PCD_REGULER_FLAG : 'Y' 일때 필수)
    obj.PCD_PAY_MONTH = '<?=$pay_month?>';			// [정기결제] 결제 구분 월 (PCD_REGULER_FLAG : 'Y' 일때 필수)
    obj.PCD_TAXSAVE_FLAG = '<?=$is_taxsave?>';			// 현금영수증 발행 여부 (Y|N)
    obj.PCD_SIMPLE_FLAG = '<?=$simple_flag?>';			// 간편결제 여부 (Y|N)
    obj.PCD_PAYER_AUTHTYPE = 'pwd';				// [간편결제/정기결제] 본인인증 방식 (sms : 문자인증 | pwd : 패스워드 인증)
    obj.PCD_RST_URL = '/order_result.html';			// 결제(요청)결과 RETURN URL
    obj.payple_dir_path = '/pg/pay';				// cPayPayple 폴더 경로 (ex: /shop/cPayPayple 은 /shop 로 지정)
    obj.payple_auth_file = 'payple_payAuth.php';		// cPayPayple 폴더 의 payple_payAuth.html 대체파일 명

    PaypleCpayAuthCheck(obj);
		
    event.preventDefault();		
  });	
});
</script>
```

파라미터 ID | 설명 | 필수 | 비고
:----: | :----: | :----: | :----:
PCD_CPAY_VER | 결제창 버전 | O | 
PCD_PAY_TYPE | 결제수단<br>(transfer=계좌 / card=카드) | O | 
PCD_PAY_WORK | 결제요청 방식 | O |1. AUTH=인증만 진행<br>2. CERT= 가맹점 최종승인 후 인증+결제 진행<br>3. PAY: 가맹점 최종승인없이 인증+결제 진행 
PCD_PAYER_ID | 결제 키 | O | 해당 키를 통해 결제요청
PCD_PAYER_NO | 가맹점의 결제고객 고유번호 | O | 
PCD_PAYER_NAME | 결제고객 이름 | - | 
PCD_PAYER_HP | 결제고객 휴대폰번호 | - |  
PCD_PAYER_EMAIL | 결제고객 이메일 | - | 
PCD_PAY_GOODS | 상품명 | O | 
PCD_PAY_TOTAL | 결제금액 | O | 
PCD_PAY_OID | 주문번호 | - | 미입력시 임의생성 
PCD_REGULER_FLAG | 정기결제 여부 | - | 
PCD_PAY_YEAR | 정기결제 적용연도 | - | PCD_REGULER_FLAG : 'Y' 일때 필수
PCD_PAY_MONTH | 정기결제 적용월 | - | PCD_REGULER_FLAG : 'Y' 일때 필수
PCD_TAXSAVE_FLAG | 현금영수증 발행 여부<br> | O | Y=발행 / N=미발행
PCD_SIMPLE_FLAG | 간편결제 여부 | - | 
PCD_PAYER_AUTHTYPE | 간편결제 인증방식 | - | PCD_SIMPLE_FLAG : 'Y' 일때 필수<br>pwd : 결제비밀번호 / sms : 문자인증
PCD_RST_URL | 결제(요청)결과 RETURN URL | O | 
payple_dir_path | cPayPayple 폴더 경로 | O | 예시 : /shop/cPayPayple 은 /shop 로 지정
payple_auth_file | cPayPayple 폴더 의 payple_payAuth.html 대체파일 명 | O 


<br><br>
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

<br><br>
#### 1-2. 즉시 승인(PCD_PAY_WORK : PAY) 
* 가맹점의 최종 승인없이 즉시 결제를 진행하며 별도 Request 는 없습니다.  

<br><br><br>
### 2. 계좌등록 간편결제 
* 최초 1회 이후 결제는 REST Request 방식으로 진행합니다.
* (비밀번호 사용 선택시) 사용자는 기존에 등록한 계좌정보 확인 후 비밀번호 입력 단계로 진행합니다. 
* 비밀번호를 입력하면 결제가 완료되며, 마지막 현금영수증 발행 화면으로 이동합니다. 
![Alt text](/img/simple_01.png)
![Alt text](/img/simple_02.png)
![Alt text](/img/simple_03.png)
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
> 결제 키(PCD_PAYER_ID)로 요청 시 PCD_PAYER_NAME, PCD_PAYER_NAME, PCD_PAYER_HP, PCD_PAYER_BIRTH, PCD_PAY_BANK, PCD_PAY_BANKNUM 필요없음 

파라미터 ID | 설명 | 필수 | 비고
:----: | :----: | :----: | :----:
PCD_CST_ID | 가맹점 ID | O | 
PCD_CUST_KEY | 가맹점 식별을 위한 비밀키 | O | 
PCD_AUTH_KEY | 결제요청을 위한 Transaction 키 | O | 
PCD_PAY_TYPE | 결제수단<br>(transfer = 계좌 / card = 카드) | O | 
PCD_PAYER_NO | 가맹점의 결제고객 고유번호 | O | 
PCD_PAYER_ID | 결제 키<br>(해당 키를 통해 결제요청) | O | 
PCD_PAY_GOODS | 상품명 | O | 
PCD_PAY_TOTAL | 결제금액 | O | 
PCD_PAY_OID | 주문번호 | O | 
PCD_TAXSAVE_FLAG | 현금영수증 발행 여부<br>(Y=발행 / N=미발행) | O | 
PCD_TAXSAVE_TRADE | 현금영수증 발행 타입<br>(personal=소득공제 / company=지출증빙) |  |  
PCD_TAXSAVE_IDNUM | 현금영수증 발행 번호<br>(휴대폰번호, 사업자번호) |  | 
PCD_SIMPLE_FLAG | 간편결제 여부 | O | 
PCD_PAYER_EMAIL | 결제고객 이메일 | O |

<br><br><br>
### 3. 정기결제
* 최초 1회 이후 결제는 REST Request 방식으로 진행합니다.
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
> 결제 키(PCD_PAYER_ID)로 요청 시 PCD_PAYER_NAME, PCD_PAYER_NAME, PCD_PAYER_HP, PCD_PAYER_BIRTH, PCD_PAY_BANK, PCD_PAY_BANKNUM 필요없음 

파라미터 ID | 설명 | 필수 | 비고
:----: | :----: | :----: | :----:
PCD_CST_ID | 가맹점 ID | O | 
PCD_CUST_KEY | 가맹점 식별을 위한 비밀키 | O | 
PCD_AUTH_KEY | 결제요청을 위한 Transaction 키 | O | 
PCD_PAY_TYPE | 결제수단<br>(transfer = 계좌 / card = 카드) | O | 
PCD_PAYER_NO | 가맹점의 결제고객 고유번호 | O | 
PCD_PAYER_ID | 결제 키<br>(해당 키를 통해 결제요청) | O | 
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

<br><br><br>
### 4. 단건결제 
* 단건결제는 별도 Request가 없습니다. 

<br><br><br>
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
$PCD_REGULER_FLAG = (isset($_POST['PCD_REGULER_FLAG'])) ? $_POST['PCD_REGULER_FLAG'] : "";
$PCD_PAY_YEAR = (isset($_POST['PCD_PAY_YEAR'])) ? $_POST['PCD_PAY_YEAR'] : ""; 
$PCD_PAY_MONTH = (isset($_POST['PCD_PAY_MONTH'])) ? $_POST['PCD_PAY_MONTH'] : "";
$PCD_PAY_GOODS = (isset($_POST['PCD_PAY_GOODS'])) ? $_POST['PCD_PAY_GOODS'] : "";
$PCD_PAY_TOTAL = (isset($_POST['PCD_PAY_TOTAL'])) ? $_POST['PCD_PAY_TOTAL'] : "";
$PCD_PAY_TIME = (isset($_POST['PCD_PAY_TIME'])) ? $_POST['PCD_PAY_TIME'] : "";         
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
PCD_PAYER_ID | 결제 키 | NS9qNTgzU2xRNHR2RmFBWWFBTWk5UT09
PCD_PAYER_NO | 결제고객 고유번호 | 1234 
PCD_REGULER_FLAG | 정기결제 여부 | Y / N
PCD_PAY_YEAR | 과금연도<br>(정기결제) | 2018 
PCD_PAY_MONTH | 과금월<br>(정기결제) | 08
PCD_PAY_GOODS | 상품명 | 정기구독 
PCD_PAY_TOTAL | 결제금액 | 1000
PCD_PAY_TIME | 결제완료 시간 | 20180110152911
PCD_TAXSAVE_RST | 현금영수증 발행 결과 | Y / N 

<br><br><br>
## 결제결과 조회  
* 해당 API를 통해 가맹점에서는 언제든 결제결과를 수신 가능합니다.
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
   "PCD_PAYER_ID" => "NS9qNTgzU2xRNHR2RmFBWWFBTWk5UT09",
   "PCD_PAY_YEAR" => "2018",
   "PCD_PAY_MONTH" => "05",
   "PCD_PAY_GOODS": "간편상품",
   "PCD_PAY_TOTAL": "1000",
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
PCD_PAYER_ID | 결제 키 | NS9qNTgzU2xRNHR2RmFBWWFBTWk5UT09
PCD_PAY_YEAR | 과금연도<br>(정기결제) | 2018 
PCD_PAY_MONTH | 과금월<br>(정기결제) | 08
PCD_PAY_GOODS | 상품명 | 정기구독 
PCD_PAY_TOTAL | 결제금액 | 1000
PCD_PAY_BANK | 결제 은행코드 | 081
PCD_PAY_BANKNUM | 결제 계좌번호 | 2881204040404
PCD_PAY_TIME | 결제완료 시간 | 20180110152911
PCD_TAXSAVE_RST | 현금영수증 발행 결과 | Y / N 
PCD_REGULER_FLAG | 정기결제 여부 | Y / N

<br><br><br>
## 서비스가능 은행 및 점검시간 

은행명 | 코드 | 평일, 토요일 | 공휴일
:----: | :----: | :----: | :----:
국민은행 | 004 | 23:30 ~ 00:30 | 23:30 ~ 00:30 
농협 | 011 | 23:30 ~ 00:30 | 23:30 ~ 00:30 
신한은행 | 088 | 23:30 ~ 00:30 | 23:30 ~ 00:30
KEB하나은행 | 081 | 23:30 ~ 00:30 | 23:30 ~ 00:30
우체국 | 071 | 23:30 ~ 00:30 | 23:30 ~ 00:30 
새마을금고 | 045 | 23:30 ~ 00:30 | 23:30 ~ 00:30
대구은행 | 031 | 23:30 ~ 00:30 | 23:30 ~ 00:30 
광주은행 | 034 | 23:30 ~ 00:30 | 23:30 ~ 00:30 
경남은행 | 039 | 23:30 ~ 06:00 | 23:30 ~ 06:00 
산업은행 | 002 | 00:00 ~ 03:00 | 00:00 ~ 03:00 
신협 | 048 | 22:00 ~ 08:00 | 22:00 ~ 08:00 
수협 | 007 | 23:30 ~ 00:30 | 23:30 ~ 00:30
부산은행 | 032 | 23:30 ~ 00:30 | 23:30 ~ 00:30

> IBK 기업은행, 우리은행은 2018년 내 연결 예정입니다.

<br><br><br>
## 문의  
* 기술문의 : help@payple.kr 을 통해 보다 자세한 문의가 가능합니다.
* 가입문의 : 페이플 웹사이트 [가입문의하기](https://www.payple.kr) 를 통하시면 가장 빠르게 안내 받으실 수 있습니다. 
