# PAYPLE 계좌간편결제 
PAYPLE 은 대부분의 온라인상점에서 제공하는 '실시간계좌이체'의 불편함을 해결하는 계좌출금 기반의 결제서비스 입니다.<br>
ARS 인증만으로 결제가 완료되기 때문에 별도 앱설치, 보안카드, 공인인증서 등이 필요없습니다.<br>
그리고 PAYPLE 은 단건결제 뿐 아니라 다양한 계좌기반의 결제서비스를 제공하고 있습니다. 
![Alt text](/img/payple.png)
## 공통 정의사항 
인코딩 : UTF-8 <br>
SSL 보안통신 필수 <br>
메세지 포맷 : JSON <br>
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
비고 | 인증은 진행되지만 출금은 되지 않습니다. | 실제 출금이 되며, 최소금액 1,000원부터 출금 가능합니다. 수수료도 발생합니다. 

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
  <input type="hidden" name="PCD_CST_ID" id="PCD_CST_ID">  <!-- 가맹점 ID -->
  <input type="hidden" name="PCD_CUST_KEY" id="PCD_CUST_KEY">  <!-- 가맹점 고유키 -->
  <input type="hidden" name="PCD_AUTH_KEY" id="PCD_AUTH_KEY">  <!-- 결제용 인증키 -->
  <input type="hidden" name="PCD_PAY_REQKEY" id="PCD_PAY_REQKEY">  <!-- 결제요청 고유KEY (결제요청 완료시 RETURN) -->
  <input type="hidden" name="PCD_PAY_COFURL" id="PCD_PAY_COFURL">  <!-- 결제승인요청 URL -->
  <input type="hidden" name="PCD_PAY_TYPE" id="PCD_PAY_TYPE" value="transfer">  <!-- 결제 방식 (transfer | card) -->
  <input type="hidden" name="PCD_PAY_WORK" id="PCD_PAY_WORK" value="CERT">  <!-- 결제요청 업무구분 (CERT: 최종승인후결제, PAY: 승인없이결제 ) -->
  <input type="hidden" name="PCD_PAYER_ID" id="PCD_PAYER_ID">  <!-- 결제자 고유ID (결제요청 완료시 RETURN) -->
  <input type="hidden" name="PCD_PAYER_NO" id="PCD_PAYER_NO" value="<?=$buyer_no?>">  <!-- 결제자 고유번호 -->
  <input type="hidden" name="PCD_PAYER_NAME" id="PCD_PAYER_NAME" value="<?=$buyer_name?>">  <!-- 결제자 명 -->
  <input type="hidden" name="PCD_PAYER_HP" id="PCD_PAYER_HP" value="<?=$buyer_hp?>">  <!-- 결제자 휴대폰 번호 -->
  <input type="hidden" name="PCD_PAYER_EMAIL" id="PCD_PAYER_EMAIL" value="<?=$buyer_email?>">  <!-- 결제자 Email -->
  <input type="hidden" name="PCD_PAY_GOODS" id="PCD_PAY_GOODS" value="<?=$buy_goods?>">  <!-- 결제 상품 -->
  <input type="hidden" name="PCD_PAY_YEAR" id="PCD_PAY_YEAR" value="<?=$pay_year?>">  <!-- 결제구분 년도 -->
  <input type="hidden" name="PCD_PAY_MONTH" id="PCD_PAY_MONTH" value="<?=$pay_month?>">  <!-- 결제구분 월 -->
  <input type="hidden" name="PCD_PAY_TOTAL" id="PCD_PAY_TOTAL" value="<?=$buy_total?>">  <!-- 결제 금액 -->
  <input type="hidden" name="PCD_PAY_OID" id="PCD_PAY_OID" value="<?=$order_num?>">  <!-- 주문번호 -->
  <input type="hidden" name="PCD_TAXSAVE_FLAG" id="PCD_TAXSAVE_FLAG" value="<?=$is_taxsave?>">  <!-- 현금영수증 발행 -->
  <input type="hidden" name="PCD_REGULER_FLAG" id="PCD_REGULER_FLAG" value="<?=$is_reguler?>">  <!-- 정기결제 -->
  <input type="hidden" name="PCD_PAY_BANK" id="PCD_PAY_BANK">  <!-- 결제은행 -->
  <input type="hidden" name="PCD_PAY_BANKNUM" id="PCD_PAY_BANKNUM">  <!-- 결제계좌번호 -->
  <input type="hidden" name="PCD_PAY_TIME" id="PCD_PAY_TIME">  <!-- 결제시간 -->	
  <input type="hidden" name="PCD_RST_URL" id="PCD_RST_URL" value="/order_result.html">  <!-- 결제내용 RETUN URL -->
  <input type="hidden" name="PCD_PAY_RST" id="PCD_PAY_RST">  <!-- 결제성공 여부 (Y|N) -->
  <input type="hidden" name="PCD_PAY_MSG" id="PCD_PAY_MSG">  <!-- 결제결과 메세지 -->
  <input type="hidden" name="PCD_TAXSAVE_RST" id="PCD_TAXSAVE_RST">  <!-- 현금영수증 발행 결과 -->
  <input type="hidden" name="REMOTE_IP" id="REMOTE_IP">  <!-- 결제자 접속 IP -->
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
PCD_PAY_COFURL | 결제생성후 승인을 위한 URL | O 
PCD_PAY_TYPE | 결제수단(transfer=계좌 / card=카드) | O |  
PCD_PAY_WORK | 결제요청방식(CERT=결제생성 후 승인 / PAY=즉시승인) | O |  
PCD_PAYER_ID | 결제고객 고유 ID | | O 
PCD_PAYER_NO | 결제고객 고유 번호 | O | 
PCD_PAYER_NAME | 결제고객명 | O | 
PCD_PAYER_HP | 결제고객 휴대폰번호 | O |  
PCD_PAYER_EMAIL | 결제고객 이메일 | O |  
PCD_PAY_GOODS | 상품명 | O |  
PCD_PAY_YEAR | 정기결제 적용연도(정기결제만 해당) | O |  
PCD_PAY_MONTH | 정기결제 적용월(정기결제만 해당) | O |  
PCD_PAY_TOTAL | 결제금액 | O |  
PCD_PAY_OID | 주문번호 | O |  
PCD_TAXSAVE_FLAG | 현금영수증 발행여부(Y=발행 / N=미발행) | O |  
PCD_REGULER_FLAG | 정기결제 여부(Y=정기 / N=단건) | O | 
PCD_PAY_BANK | 결제 은행 |  | O 
PCD_PAY_BANKNUM | 결제 계좌번호 |  | O 
PCD_PAY_TIME | 결제완료시간(예: 20180110152911) |  | O 
PCD_RST_URL | 가맹점 결제완료 페이지 경로(형식은 가맹점 URL을 제외한 /결과받을경로/파일명) | O |  
PCD_RST_URL | 가맹점 결제완료 페이지 경로(형식은 가맹점 URL을 제외한 /결과받을경로/파일명) | O |  
PCD_PAY_RST | 결제 성공여부(Y / N) |  | O 
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
