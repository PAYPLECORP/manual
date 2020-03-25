# 페이플 - 계좌 간편결제, 정기결제  
페이플 계좌간편결제는 최초 1회 계좌등록 후 비밀번호만으로 결제 가능한 서비스입니다.<br> 
ARS 인증만으로 계좌등록과 결제가 완료되기 때문에 별도 앱설치, 보안카드, 공인인증서 등이 필요없습니다.<br>
그리고 페이플은 계좌간편결제 뿐 아니라 정기구독 서비스를 위한 계좌정기결제, PG의 실시간계좌이체를 대체하는 단건결제 서비스도 제공하고 있습니다. 

<br><br><br>
## 결제서비스별 흐름도 
페이플 각 결제서비스의 흐름도입니다. 동일한 조건으로 모든 결제서비스를 이용할 수 있습니다. <br>
![Alt text](/img/flow.jpg)


<br><br><br>
## 공통 정의사항 
* 인코딩 : UTF-8 <br>
* SSL 보안통신 필수 <br>
* 메세지 포맷 : JSON <br>
<br><br><br>
## 샘플 페이지 
참고 가능한 [샘플 페이지](/sample) 폴더입니다. 

<br><br><br>
# 결제요청 
## 1. 최초결제 - 공통 

### 1-1. 가맹점 인증 요청 파일 생성
<br>
#### 가맹점 인증 
* Payple 서비스를 이용하기 위해서는 가맹점 계약을 통한 인증이 필요하지만 계약 전 **_테스트 계정을 통해 개발진행이 가능합니다._** 계약 완료 후 Payple 에서 가맹점에 아래의 키를 발급합니다. 
  * cst_id (가맹점 식별을 위한 가맹점 ID)
  * custKey (API 통신전문 변조를 방지하기 위한 비밀키)
<br><br><br>
#### 호출정보

구분 | 테스트 | 운영
:----: | :----: | :----:
URL | https://testcpay.payple.kr/php/auth.php | https://cpay.payple.kr/php/auth.php
ID | cst_id : test | cst_id : 가맹점 운영 ID 
KEY | custKey : abcd1234567890 | custKey : ID 매칭 Key
비고 | - 인증은 진행되지만 출금은 되지 않습니다.<br>- 최소금액 1,000원 이상으로 테스트 해주세요. | - 실제 출금이 되며, 최소금액 1,000원부터 출금 가능합니다. 수수료도 발생합니다.<br>**- AWS(아마존웹서비스)에서 AUTH0004 오류 발생 시 가맹점 서버도메인의 REFERER 추가가 필요할 수 있습니다.**<br>**- 카페24, 가비아 등 서버호스팅 이용 시 호스팅사에 페이플 URL(테스트, 운영) 방화벽 오픈을 요청하셔야 할 수 있습니다.**   
* 호출을 위한 [각 언어별 샘플](/sample/language)을 확인해보세요. 

```
* 가맹점 인증요청 - Request 
* obj.payple_auth_file = '/pg/auth'; 에 설정 할 가맹점인증요청 파일 (ex: auth)을 생성합니다.

```html
POST /php/auth.php HTTP/1.1
Host: testcpay.payple.kr
Content-Type: application/json
<!-- AWS 이용 가맹점인 경우 REFERER 추가 -->
Referer: http://가맹점 도메인 
<!-- End : AWS 이용 가맹점인 경우 REFERER 추가 -->
Cache-Control: no-cache
{
  "cst_id": "test",
  "custKey": "abcd1234567890"
}
```
* 가맹점인증요청 결과 - Response
```html
{
  "result": "success",
  "result_msg": "사용자 인증완료",
  "cst_id": "UFVNNVZpZk4reWo5UFRualUwcGV4dz09",
  "custKey": "T3JzRkp5L1FTcEFDa1FQdHo5Um1UZz09",
  "AuthKey": "a688ccb3555c25cd722483f03e23065c3d0251701ad6da895eb2d830bc06e34d",
  "PCD_PAY_HOST": "https://testcpay.payple.kr",
  "PCD_PAY_URL": "/php/?ACT_=PAYM",
  "return_url": "https://testcpay.payple.kr/php/?ACT_=PAYM"
}
```

<br><br><br>
### 1-2. 결제창 호출
* 페이플은 자바스크립트만을 이용해 모든 결제절차를 진행합니다. <br><br> 
![Alt text](/img/onetime_01.png) <br><br>
* 간편결제, 정기결제에서 최초결제없이 **계좌등록만 하기 위해서는 obj.PCD_PAY_WORK = 'AUTH'** 로 세팅하시면 됩니다.<br><br>
![Alt text](/img/auth.png) <br><br>
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
        
        var pay_work = "PAY";
        var payple_payer_id = "d0toSS9sT084bVJSNThScnFXQm9Gdz09";     
        var buyer_no = "2335";
        var buyer_name = "홍길동";
        var buyer_hp = "01012345678";
        var buyer_email = "dev@payple.kr";
        var buy_goods = "휴대폰";
        var buy_total = "1000";
	var pay_istax = "";
	var pay_taxtotal = "10";
        var order_num = "test0553941001540967923";
        var is_reguler = "N";
        var pay_year = "";
        var pay_month = "";
        var is_taxsave = "N";
        
        var obj = new Object();
        
        obj.PCD_CPAY_VER = "1.0.1";
        obj.PCD_PAY_TYPE = 'transfer';
        obj.PCD_PAY_WORK = pay_work;
        obj.PCD_PAYER_AUTHTYPE = 'pwd';
        /* (필수) 가맹점 인증요청 파일 (Node.JS : auth => [app.js] app.post('/pg/auth', ...) */
	/* auth 파일 생성은 1.1 참조 */ 
        obj.payple_auth_file = '/pg/auth'; // 절대경로 포함 파일명 (예: /절대경로/payple_auth_file)
	/* End : 가맹점 인증요청 파일 */
	
        /* 결과를 콜백 함수로 받고자 하는 경우 함수 설정 추가 */
        obj.callbackFunction = getResult;  // getResult : 콜백 함수명
        /* End : 결과를 콜백 함수로 받고자 하는 경우 함수 설정 추가 */
        
	/* 결과를 콜백 함수가 아닌 URL로 받고자 하는 경우 */
        obj.PCD_RST_URL = '/order_result.html';
        /* End : 결과를 콜백 함수가 아닌 URL로 받고자 하는 경우 */
	
        /*
         * 1. 간편결제
         */
        //-- PCD_PAYER_ID 는 소스상에 표시하지 마시고 반드시 Server Side Script 를 이용하여 불러오시기 바랍니다. --//
        obj.PCD_PAYER_ID = payple_payer_id;           
        //-------------------------------------------------------------------------------------//          
        obj.PCD_PAYER_NO = buyer_no;  
        obj.PCD_PAYER_EMAIL = buyer_email;
        obj.PCD_PAY_GOODS = buy_goods;
        obj.PCD_PAY_TOTAL = buy_total;
	obj.PCD_PAY_ISTAX = pay_istax;
	obj.PCD_PAY_TAXTOTAL = pay_taxtotal;
        obj.PCD_PAY_OID = order_num;     
        obj.PCD_REGULER_FLAG = is_reguler;
        obj.PCD_PAY_YEAR = pay_year; 
        obj.PCD_PAY_MONTH = pay_month;
        obj.PCD_TAXSAVE_FLAG = is_taxsave; 
        obj.PCD_SIMPLE_FLAG = 'Y'; 
	/*
         * End : 1. 간편결제
         */
        
        /*
         * 2. 단건결제
         */
        obj.PCD_PAYER_NO = buyer_no;
        obj.PCD_PAYER_NAME = buyer_name;
        obj.PCD_PAYER_HP = buyer_hp;
        obj.PCD_PAYER_EMAIL = buyer_email;
        obj.PCD_PAY_GOODS = buy_goods; 
        obj.PCD_PAY_TOTAL = buy_total;
	obj.PCD_PAY_ISTAX = pay_istax;
	obj.PCD_PAY_TAXTOTAL = pay_taxtotal;
        obj.PCD_PAY_OID = order_num; 
        obj.PCD_REGULER_FLAG = is_reguler;
        obj.PCD_PAY_YEAR = pay_year;       
        obj.PCD_PAY_MONTH = pay_month;
        obj.PCD_TAXSAVE_FLAG = is_taxsave;        
	/*
         * End : 2. 단건결제 
         */
	
        PaypleCpayAuthCheck(obj);
            
        event.preventDefault();     
    });   
});
</script>
```

파라미터 ID | 설명 | 필수 | 비고
:----: | :----: | :----: | ----
PCD_CPAY_VER | 결제창 버전 | O | 
PCD_PAY_TYPE | 결제수단 | O | 
PCD_PAY_WORK | 결제요청 방식 | O | - AUTH : 계좌등록만 진행<br>- CERT : 가맹점 최종승인 후 계좌등록+결제 진행<br>- PAY : 가맹점 최종승인없이 계좌등록+결제 진행 
PCD_PAYER_AUTHTYPE | 간편결제 인증방식 | - | - PCD_SIMPLE_FLAG : 'Y' 일때 필수<br>- pwd : 결제비밀번호
PCD_RST_URL | 결제(요청)결과 RETURN URL | O | - 결제결과를 콜백 함수가 아닌 URL로 수신할 경우만 해당<br>- 모바일에서 팝업방식은 상대경로, 다이렉트 방식은 절대경로로 설정  
PCD_PAYER_ID | 결제 키 | O | 해당 키를 통해 결제요청
PCD_PAYER_NO | 가맹점의 결제고객 고유번호 | O | maxlength=10
PCD_PAYER_NAME | 결제고객 이름 | - | 
PCD_PAYER_HP | 결제고객 휴대폰번호 | - |  
PCD_PAYER_EMAIL | 결제고객 이메일 | - | 
PCD_PAY_GOODS | 상품명 | O | 
PCD_PAY_TOTAL | 결제금액 | O | 
PCD_PAY_ISTAX | 과세설정 | - | Default: Y, 비과세: N | 
PCD_PAY_TAXTOTAL | 복합과세 부가세 | - | 복합과세 주문건(과세+면세)에 필요한 항목이며 가맹점에서 전송한 값을 부가세로 설정합니다. | 
PCD_PAY_OID | 주문번호 | - | 미입력시 임의생성 
PCD_REGULER_FLAG | 정기결제 여부 | - | 
PCD_PAY_YEAR | 정기결제 적용연도 | - | PCD_REGULER_FLAG : 'Y' 일때 필수
PCD_PAY_MONTH | 정기결제 적용월 | - | PCD_REGULER_FLAG : 'Y' 일때 필수
PCD_TAXSAVE_FLAG | 현금영수증 발행 여부<br> | O | Y=발행 / N=미발행
PCD_SIMPLE_FLAG | 간편결제 여부 | - | 

<br><br>
#### 1-3. 결제생성 후 승인(PCD_PAY_WORK : CERT) 
* 가맹점의 최종 승인 후에 결제를 진행하며 REST Request 방식으로 진행합니다. 
* Request 예시 
```html
<!-- 결제 승인요청 --> 
POST /php/PayConfirmAct.php?ACT_=PAYM HTTTP/1.1
Host: testcpay.payple.kr
Content-Type: application/json
Cache-Control: no-cache
{
  "PCD_CST_ID": "test",	// 또는 인코딩된 "PCD_CST_ID": "UFVNNVZpZk4reWo5UFRualUwcGV4dz09"
  "PCD_CUST_KEY": "abcd1234567890", // 또는 인코딩된 "PCD_CUST_KEY": "T3JzRkp5L1FTcEFDa1FQdHo5Um1UZz09"	
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
#### 1-4. 즉시 승인(PCD_PAY_WORK : PAY) 
* 가맹점의 최종 승인없이 즉시 결제를 진행하며 별도 Request 는 없습니다.  

<br><br><br>
### 2. 이후결제 - 계좌등록 간편결제(PCD_PAYER_AUTHTYPE : pwd)
* 최초 1회 이후결제를 위해서는 [1. 최초결제 - 공통](#1-최초결제---공통)과 동일한 스크립트를 사용합니다.
* 사용자는 기존에 등록한 계좌정보 확인 후 비밀번호 입력 단계로 진행합니다. 
* 비밀번호를 입력하면 결제가 완료되며, 마지막 현금영수증 발행 화면으로 이동합니다. 
![Alt text](/img/simple_01.png)
![Alt text](/img/simple_02.png)
![Alt text](/img/simple_03.png)

<br><br><br>
### 3. 이후결제 - 단건결제 
* 단건결제도 최초 1회 이후결제를 위해서는 [1. 최초결제 - 공통](#1-최초결제---공통)과 동일한 스크립트를 사용합니다. 
* 사용자는 매번 결제정보를 입력해야하며, 최초결제 시 ARS 인증, 이후결제 시에는 SMS 인증으로 결제를 진행합니다. 

<br><br><br>
### 4. 이후결제 - 정기결제
* 정기결제는 최초 1회 이후 결제 시 별도 UI가 필요없기 때문에 REST Request 방식으로 진행합니다.
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
   "PCD_CST_ID": "test",	// 또는 인코딩된 "PCD_CST_ID": "UFVNNVZpZk4reWo5UFRualUwcGV4dz09"
   "PCD_CUST_KEY": "abcd1234567890", // 또는 인코딩된 "PCD_CUST_KEY": "T3JzRkp5L1FTcEFDa1FQdHo5Um1UZz09"
   "PCD_AUTH_KEY": "a688ccb3555c25cd722483f03e23065c3d0251701ad6da895eb2d830bc06e34d",
   "PCD_PAY_TYPE": "transfer",							
   "PCD_PAYER_NO": "2324",
   "PCD_PAYER_ID": "NS9qNTgzU2xRNHR2RmFBWWFBTWk5UT09",
   "PCD_PAY_GOODS": "정기구독",
   "PCD_PAY_YEAR": "2018",	
   "PCD_PAY_MONTH": "04",	
   "PCD_PAY_TOTAL": "1000",
   “PCD_PAY_ISTAX”: “Y”,
   “PCD_PAY_TAXTOTAL”: 10,
   "PCD_PAY_OID": "test201804000001",
   "PCD_TAXSAVE_FLAG": "Y",
   "PCD_TAXSAVE_TRADE": "personal",
   "PCD_TAXSAVE_IDNUM": "01022224444",
   "PCD_REGULER_FLAG": "Y",
   "PCD_PAYER_EMAIL": "dev@payple.kr"
}
```

파라미터 ID | 설명 | 필수 | 비고
:----: | :----: | :----: | :----:
PCD_CST_ID | 가맹점 ID | O | 
PCD_CUST_KEY | 가맹점 식별을 위한 비밀키 | O | 
PCD_AUTH_KEY | 결제요청을 위한 Transaction 키 | O | 
PCD_PAY_TYPE | 결제수단 | O | 
PCD_PAYER_NO | 가맹점의 결제고객 고유번호 | O | 
PCD_PAYER_ID | 결제 키 | O | 해당 키를 통해 결제요청
PCD_PAY_GOODS | 상품명 | O | 
PCD_PAY_YEAR | 과금연도 | O | 
PCD_PAY_MONTH | 과금월 | O | 
PCD_PAY_TOTAL | 결제금액 | O | 
PCD_PAY_ISTAX | - | C | 1 | Y | 과세설정(Default: Y, 비과세: N) | 
PCD_PAY_TAXTOTAL | - | N | 20 | 10 | 복합과세 주문건(과세+면세)에 필요한 항목이며 가맹점에서 전송한 값을 부가세로 설정합니다. | 
PCD_PAY_OID | 주문번호 | O | 
PCD_TAXSAVE_FLAG | 현금영수증 발행 여부 | O | Y=발행 / N=미발행
PCD_TAXSAVE_TRADE | 현금영수증 발행 타입 | - | personal=소득공제 / company=지출증빙
PCD_TAXSAVE_IDNUM | 현금영수증 발행 번호 | - | 휴대폰번호, 사업자번호
PCD_REGULER_FLAG | 정기결제 여부 | O | 
PCD_PAYER_EMAIL | 결제고객 이메일 | O | 

* Response 예시 
```html
{
  "PCD_PAY_RST" => "success|error",
  "PCD_PAY_MSG" => "완료|실패..",
  "PCD_PAY_OID" => "test201804000001",
  "PCD_PAY_TYPE" => "transfer",
  "PCD_PAYER_NO" => "2324",
  "PCD_PAYER_ID" => "NS9qNTgzU2xRNHR2RmFBWWFBTWk5UT09",
  "PCD_PAY_GOODS" => "정기구독",
  "PCD_PAY_TOTAL" => 1000,
  “PCD_PAY_ISTAX”: “Y”,
  “PCD_PAY_TAXTOTAL”: 10,
  "PCD_PAY_BANK" => "011",
  "PCD_PAY_BANKNAME" => "농협",
  "PCD_PAY_BANKNUM" => "460-********-121",
  "PCD_PAY_TIME" => "20180423130201",
  "PCD_REGULER_FLAG" => "Y",
  "PCD_PAY_YEAR" => 2018,
  "PCD_PAY_MONTH" => 04,
  "PCD_TAXSAVE_RST" => "Y",
  "PCD_PAYER_EMAIL" => "dev@payple.kr"
}
```
* Response 파라미터 설명

파라미터 ID | 설명 | 예시
:----: | :----: | :----: 
PCD_PAY_RST | 정기결제 요청 결과 | success / error 
PCD_PAY_MSG | 링크생성 요청 결과 메세지 | 출금이체완료 / 실패 
PCD_PAY_OID | 주문번호 | test201804000001
PCD_PAY_TYPE | 결제수단 | transfer 
PCD_PAYER_NO | 가맹점의 결제고객 고유번호 | 2324
PCD_PAY_GOODS | 상품명 | 정기구독  
PCD_PAY_TOTAL | 결제금액 | 1000 
PCD_PAY_ISTAX | - | C | 1 | Y | 과세설정(Default: Y, 비과세: N)
PCD_PAY_TAXTOTAL | - | N | 20 | 10 | 복합과세 주문건(과세+면세)에 필요한 항목이며 가맹점에서 전송한 값을 부가세로 설정합니다.
PCD_PAY_BANK | 은행코드 | 011
PCD_PAY_BANKNAME | 은행명 | 농협
PCD_PAY_BANKNUM | 계좌번호 | 460- ******** -121
PCD_PAY_TIME | 결제시간 | 20180423130201
PCD_REGULER_FLAG | 정기결제 여부 | Y / N
PCD_PAY_YEAR | 과금연도<br>(정기결제) | 2018 
PCD_PAY_MONTH | 과금월<br>(정기결제) | 08
PCD_TAXSAVE_RST | 현금영수증 발행 결과 | Y / N
PCD_PAYER_EMAIL | 결제고객 이메일 | dev@payple.kr

<br><br><br>
### 5. 링크결제 - 링크생성 
* 링크결제의 링크생성은 별도 UI 없이 REST Request 방식으로 진행됩니다. 
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
  "PCD_PAY_WORK": "LINKREG"
}

<!-- 링크생성 요청  -->
POST PCD_PAY_URL HTTP/1.1
Host: PCD_PAY_HOST
Content-Type: application/json
Cache-Control: no-cache
{
  "PCD_CST_ID": 인코딩된 "PCD_CST_ID": "UFVNNVZpZk4reWo5UFRualUwcGV4dz09"
  "PCD_CUST_KEY": 인코딩된 "PCD_CUST_KEY": "T3JzRkp5L1FTcEFDa1FQdHo5Um1UZz09"
  "PCD_AUTH_KEY" : "a688ccb3555c25cd722483f03e23065c3d0251701ad6da895eb2d830bc06e34d",
  "PCD_PAY_WORK" : "LINKREG",
  "PCD_PAY_TYPE" : "transfer",
  "PCD_PAY_GOODS" : "테스트상품",
  "PCD_PAY_TOTAL" : 150000,
  "PCD_REGULER_FLAG" : "Y",
  "PCD_PAY_YEAR" : 2018,
  "PCD_PAY_MONTH" : 11,
  "PCD_TAXSAVE_FLAG" : "Y"
}
```

* Request 파라미터 설명 

파라미터 ID | 설명 | 필수 | 비고
:----: | :----: | :----: | :----:
PCD_CST_ID | 가맹점 ID | O | 
PCD_CUST_KEY | 가맹점 식별을 위한 비밀키 | O | 
PCD_AUTH_KEY | 결제요청을 위한 Transaction 키 | O | 
PCD_PAY_WORK | 업무구분 | O | 링크결제 
PCD_PAY_TYPE | 결제수단 | O | 계좌출금 
PCD_PAY_GOODS | 상품명 | O | 
PCD_PAY_TOTAL | 결제금액 | O | 
PCD_REGULER_FLAG | 정기결제 여부 | - | Y=정기결제 / N=단건결제  
PCD_PAY_YEAR | 정기결제 과금연도 | - | PCD_REGULER_FLAG : 'Y' 일때 필수
PCD_PAY_MONTH | 정기결제 과금월 | - | PCD_REGULER_FLAG : 'Y' 일때 필수
PCD_TAXSAVE_FLAG | 현금영수증 발행 여부 | O | Y=발행 / N=미발행

* Response 예시 
```html
{
  "PCD_LINK_RST" => "success|error",
  "PCD_LINK_MSG" => "링크생성완료|실패..",
  "PCD_PAY_TYPE" => "transfer",
  "PCD_PAY_GOODS" => "테스트상품",
  "PCD_PAY_TOTAL" => 150000,
  "PCD_REGULER_FLAG" => "Y",
  "PCD_PAY_YEAR" => 2018,
  "PCD_PAY_MONTH" => 11,
  "PCD_TAXSAVE_RST" => "Y"
}
```
* Response 파라미터 설명

파라미터 ID | 설명 | 예시
:----: | :----: | :----: 
PCD_LINK_RST | 링크생성 요청 결과 | success / error 
PCD_LINK_MSG | 링크생성 요청 결과 메세지 | 링크생성완료 / 실패 
PCD_PAY_TYPE | 결제수단 | 계좌출금 
PCD_PAY_GOODS | 상품명 | 링크결제 상품  
PCD_PAY_TOTAL | 결제금액 | 1000 
PCD_REGULER_FLAG | 정기결제 여부 | Y / N
PCD_PAY_YEAR | 과금연도<br>(정기결제) | 2018 
PCD_PAY_MONTH | 과금월<br>(정기결제) | 08
PCD_TAXSAVE_RST | 현금영수증 발행 결과 | Y / N

<br><br><br>
### 6. 현금영수증 - 발행
* 현금영수증 발행 REST API 입니다.
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
  "PCD_PAY_WORK": "TSREG"
}

<!-- 현금영수증 발행요청  -->
POST PCD_PAY_URL HTTP/1.1
Host: PCD_PAY_HOST
Content-Type: application/json
Cache-Control: no-cache
{
  "PCD_CST_ID": "test",	// 또는 인코딩된 "PCD_CST_ID": "UFVNNVZpZk4reWo5UFRualUwcGV4dz09"
  "PCD_CUST_KEY": "abcd1234567890", // 또는 인코딩된 "PCD_CUST_KEY": "T3JzRkp5L1FTcEFDa1FQdHo5Um1UZz09"
  "PCD_AUTH_KEY" : "a688ccb3555c25cd722483f03e23065c3d0251701ad6da895eb2d830bc06e34d",
  "PCD_PAYER_ID" : "NS9qNTgzU2xRNHR2RmFBWWFBTWk5UT09",
  "PCD_PAY_OID" : "test201804000001",
  "PCD_REGULER_FLAG" : "Y",
  "PCD_TAXSAVE_TRADEUSE" : "personal",
  "PCD_TAXSAVE_IDENTINUM" : "01023456789",
}
```

파라미터 ID | 설명 | 필수 | 비고
:----: | :----: | :----: | :----:
PCD_CST_ID | 가맹점 ID | O | 
PCD_CUST_KEY | 가맹점 식별을 위한 비밀키 | O | 
PCD_AUTH_KEY | 결제요청을 위한 Transaction 키 | O | 
PCD_PAYER_ID | 결제 키 | O | 해당 키를 통해 결제요청
PCD_PAY_OID | 주문번호 | O | 
PCD_REGULER_FLAG | 정기결제 여부 | - | 
PCD_TAXSAVE_TRADEUSE | 현금영수증 발행 구분 | - | personal=소득공제 / company=지출증빙<br>미입력시 결제내역 정보 이용 
PCD_TAXSAVE_IDENTINUM | 현금영수증 발행대상 번호 | - | 휴대폰번호, 사업자번호<br>미입력시 결제내역 정보 이용 

* Response 예시 
```html
{
  "PCD_PAY_RST" => "success",
  "PCD_PAY_MSG" => "현금영수증 발행 완료",
  "PCD_PAYER_ID" => "NS9qNTgzU2xRNHR2RmFBWWFBTWk5UT09",
  "PCD_PAY_OID" => "test201804000001",
  "PCD_REGULER_FLAG" => "Y",
  "PCD_TAXSAVE_AMOUNT" => 15000,
  "PCD_TAXSAVE_MGTNUM" => "test15424392310644"
}
```
* Response 파라미터 설명

파라미터 ID | 설명 | 예시
:----: | :----: | :----: 
PCD_PAY_RST | 현금영수증 발행 결과 | success / error 
PCD_PAY_MSG | 현금영수증 발행 결과 메세지 | 현금영수증 발행 완료 / 실패
PCD_PAYER_ID | 결제 키 | NS9qNTgzU2xRNHR2RmFBWWFBTWk5UT09
PCD_PAY_OID | 주문번호 | test201804000001
PCD_REGULER_FLAG | 정기결제 여부 | Y / N
PCD_TAXSAVE_AMOUNT | 현금영수증 발행 금액 | 15000 
PCD_TAXSAVE_MGTNUM | 국세청 발행 번호 | test15424392310644

<br><br><br>
### 7. 현금영수증 - 취소 
* 현금영수증 취소 REST API 입니다.
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
  "PCD_PAY_WORK": "TSCANCEL"
}

<!-- 현금영수증 취소요청  -->
POST PCD_PAY_URL HTTP/1.1
Host: PCD_PAY_HOST
Content-Type: application/json
Cache-Control: no-cache
{
  "PCD_CST_ID": "test",	// 또는 인코딩된 "PCD_CST_ID": "UFVNNVZpZk4reWo5UFRualUwcGV4dz09"
  "PCD_CUST_KEY": "abcd1234567890", // 또는 인코딩된 "PCD_CUST_KEY": "T3JzRkp5L1FTcEFDa1FQdHo5Um1UZz09"
  "PCD_AUTH_KEY" : "a688ccb3555c25cd722483f03e23065c3d0251701ad6da895eb2d830bc06e34d",
  "PCD_PAYER_ID" : "NS9qNTgzU2xRNHR2RmFBWWFBTWk5UT09",
  "PCD_PAY_OID" : "test201804000001",
  "PCD_REGULER_FLAG" : "Y"
}
```

파라미터 ID | 설명 | 필수 | 비고
:----: | :----: | :----: | :----:
PCD_CST_ID | 가맹점 ID | O | 
PCD_CUST_KEY | 가맹점 식별을 위한 비밀키 | O | 
PCD_AUTH_KEY | 결제요청을 위한 Transaction 키 | O | 
PCD_PAYER_ID | 결제 키 | O | 해당 키를 통해 결제요청
PCD_PAY_OID | 주문번호 | O | 
PCD_REGULER_FLAG | 정기결제 여부 | - | 

* Response 예시 
```html
{
  "PCD_PAY_RST" => "success",
  "PCD_PAY_MSG" => "현금영수증 발행취소 완료",
  "PCD_PAYER_ID" => "NS9qNTgzU2xRNHR2RmFBWWFBTWk5UT09",
  "PCD_PAY_OID" => "test201804000001",
  "PCD_REGULER_FLAG" => "Y",
  "PCD_TAXSAVE_AMOUNT" => 15000,
  "PCD_TAXSAVE_MGTNUM" => "test15424392310644"
}
```
* Response 파라미터 설명

파라미터 ID | 설명 | 예시
:----: | :----: | :----: 
PCD_PAY_RST | 현금영수증 발행 결과 | success / error 
PCD_PAY_MSG | 현금영수증 발행 결과 메세지 | 현금영수증 발행취소 완료 / 실패
PCD_PAYER_ID | 결제 키 | NS9qNTgzU2xRNHR2RmFBWWFBTWk5UT09
PCD_PAY_OID | 주문번호 | test201804000001
PCD_REGULER_FLAG | 정기결제 여부 | Y / N
PCD_TAXSAVE_AMOUNT | 현금영수증 발행 금액 | 15000 
PCD_TAXSAVE_MGTNUM | 국세청 발행 번호 | test15424392310644

<br><br><br>
### 8. 기 등록계좌 해지 
* 결제 후 은행에 등록된 계좌를 해지하는 REST API 입니다.
* 해지된 사용자가 다시 결제할 때는 ARS 인증을 진행하고 계좌를 재등록합니다. 
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
  "PCD_PAY_WORK": "PUSERDEL"
}

<!-- 계좌 해지요청  -->
POST PCD_PAY_URL HTTP/1.1
Host: PCD_PAY_HOST
Content-Type: application/json
Cache-Control: no-cache
{
  "PCD_CST_ID": "test",	// 또는 인코딩된 "PCD_CST_ID": "UFVNNVZpZk4reWo5UFRualUwcGV4dz09"
  "PCD_CUST_KEY": "abcd1234567890", // 또는 인코딩된 "PCD_CUST_KEY": "T3JzRkp5L1FTcEFDa1FQdHo5Um1UZz09"
  "PCD_AUTH_KEY" : "a688ccb3555c25cd722483f03e23065c3d0251701ad6da895eb2d830bc06e34d",
  "PCD_PAYER_ID" : "NS9qNTgzU2xRNHR2RmFBWWFBTWk5UT09",					
  "PCD_PAYER_NO" : "2324"
}
```

파라미터 ID | 설명 | 필수 | 비고
:----: | :----: | :----: | :----:
PCD_CST_ID | 가맹점 ID | O | 
PCD_CUST_KEY | 가맹점 식별을 위한 비밀키 | O | 
PCD_AUTH_KEY | 결제요청을 위한 Transaction 키 | O | 
PCD_PAYER_ID | 결제 키 | O | 해당 키를 통해 결제요청
PCD_PAYER_NO | 가맹점의 결제고객 고유번호 | - | 

* Response 예시 
```html
{
  "PCD_PAY_RST" => "success",
  "PCD_PAY_MSG" => "계좌해지완료",
  "PCD_PAY_TYPE" => "transfer",
  "PCD_PAY_WORK" => "PUSERDEL",
  "PCD_PAYER_ID" => "NS9qNTgzU2xRNHR2RmFBWWFBTWk5UT09",
  "PCD_PAYER_NO" => "2324"
}
```
* Response 파라미터 설명

파라미터 ID | 설명 | 예시
:----: | :----: | :----: 
PCD_PAY_RST | 계좌해지 요청 결과 | success / error 
PCD_PAY_MSG | 계좌해지 요청 결과 메세지 | 계좌해지완료 / 실패
PCD_PAY_TYPE | 결제수단 | transfer 
PCD_PAY_WORK | 업무구분 | PUSERDEL (계좌해지) 
PCD_PAYER_ID | 결제 키 | NS9qNTgzU2xRNHR2RmFBWWFBTWk5UT09
PCD_PAYER_NO | 가맹점의 결제고객 고유번호 | 2324

<br><br><br>
### 9. 등록계좌 조회  
* 해당 REST API를 통해 가맹점에서는 언제든 등록된 계좌정보를 수신 가능합니다.
* Request 예시 
```html
POST /php/auth.php HTTP/1.1
Host: testcpay.payple.kr
Content-Type: application/json
Cache-Control: no-cache
{
  "cst_id": "test",
  "custKey": "abcd1234567890",
  "PCD_PAY_WORK": "PUSERINFO"
}

POST /php/cPayUser/api/cPayUserAct.php?ACT_=PUSERINFO HTTP/1.1
Host: testcpay.payple.kr
Content-Type: application/json
Cache-Control: no-cache
{
   "PCD_CST_ID": RES.cst_id,                           
   "PCD_CUST_KEY": RES.custKey,                        
   "PCD_AUTH_KEY": RES.AuthKey,                        
   "PCD_PAYER_ID": "d0toSS9sT084bVJSNThScnFXQm9Gdz09", 
   "PCD_PAYER_NO": 122323                             
}
```
* Request 파라미터 설명 

파라미터 ID | 설명 | 필수 | 비고
:----: | :----: | :----: | :----:
PCD_CST_ID | 가맹점 ID | O | 가맹점 인증요청 시 리턴받은 cst_id
PCD_CUST_KEY | 가맹점 식별을 위한 비밀키 | O | 가맹점 인증요청 시 리턴받은 custKey
PCD_AUTH_KEY | 결제요청을 위한 Transaction 키 | O | 가맹점 인증요청 시 리턴받은 AuthKey
PCD_PAYER_ID | 결제(빌링) KEY | O | 
PCD_PAYER_NO | 사용자 필드, 결과에 그대로 리턴 | O | 

* Response 예시 
```html
{
   "PCD_PAY_RST": "success",
   "PCD_PAY_CODE": "0000",
   "PCD_PAY_MSG": "계좌조회 성공",
   "PCD_PAY_TYPE": "transfer",
   "PCD_PAY_BANKACCTYPE": "개인",
   "PCD_PAYER_ID" => "NS9qNTgzU2xRNHR2RmFBWWFBTWk5UT09",
   "PCD_PAYER_NAME": "홍길동",
   "PCD_PAYER_HP": "010-****-3333",
   "PCD_PAY_BANK": "081",
   "PCD_PAY_BANKNAME": "KEB하나은행",
   "PCD_PAY_BANKNUM": "123-********-021"
}
```
* Response 파라미터 설명 

파라미터 ID | 설명 | 예시
:----: | :----: | :----:
PCD_PAY_RST | 요청결과 | success / error 
PCD_PAY_CODE | 요청결과 코드 | 0000 : 성공 / 실패시 실패코드 
PCD_PAY_MSG | 요청결과 메세지 | 계좌조회 성공 등 
PCD_PAY_TYPE | 결제수단 | transfer
PCD_PAYER_ID | 결제 키 | NS9qNTgzU2xRNHR2RmFBWWFBTWk5UT09
PCD_PAYER_NAME | 예금주명 | 홍길동 
PCD_PAYER_HP | 휴대폰번호 | 010- * * * * -3333
PCD_PAY_BANK | 은행코드 | 081
PCD_PAY_BANKNAME | 은행명 | KEB하나은행
PCD_PAY_BANKNUM | 계좌번호 | 460- ******** -121

<br><br><br>
## 결제결과 수신  
> 결제결과를 콜백 함수로 수신하는 경우에는 아래 절차가 필요없습니다. 
* 콜백 함수를 이용하지 않는 경우에는 아래 소스코드를 가맹점 결제완료 페이지에 추가하고 가맹점 환경에 맞는 개발언어로 수정해주세요.
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
$PCD_PAY_BANK = (isset($_POST['PCD_PAY_BANK'])) ? $_POST['PCD_PAY_BANK'] : "";
$PCD_PAY_BANKNAME = (isset($_POST['PCD_PAY_BANKNAME'])) ? $_POST['PCD_PAY_BANKNAME'] : "";
$PCD_PAY_BANKNUM = (isset($_POST['PCD_PAY_BANKNUM'])) ? $_POST['PCD_PAY_BANKNUM'] : "";
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
PCD_PAY_TYPE | 결제수단 | 
PCD_PAY_WORK | 결제요청방식 | CERT / PAY 
PCD_PAYER_ID | 결제 키 | NS9qNTgzU2xRNHR2RmFBWWFBTWk5UT09
PCD_PAYER_NO | 결제고객 고유번호 | 1234 
PCD_REGULER_FLAG | 정기결제 여부 | Y / N
PCD_PAY_YEAR | 과금연도<br>(정기결제) | 2018 
PCD_PAY_MONTH | 과금월<br>(정기결제) | 08
PCD_PAY_GOODS | 상품명 | 정기구독 
PCD_PAY_TOTAL | 결제금액 | 1000
PCD_PAY_BANK | 은행코드 | 011
PCD_PAY_BANKNAME | 은행명 | 농협
PCD_PAY_BANKNUM | 계좌번호 | 460- ******** -121
PCD_PAY_TIME | 결제완료 시간 | 20180110152911
PCD_TAXSAVE_RST | 현금영수증 발행 결과 | Y / N 

<br><br><br>
## 결제결과 조회  
* 해당 REST API를 통해 가맹점에서는 언제든 결제결과를 수신 가능합니다.
* 문제가 발생해서 결제결과를 수신받지 못하는 경우 결제결과 조회 API를 통해 결과값을 조회할 수 있습니다. 일시적인 서버 통신 장애 등으로 인한   불편사항을 줄일 수 있으므로 해당 API를 사용하시길 권유드립니다.

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
   "PCD_CST_ID": "test",	// 또는 인코딩된 "PCD_CST_ID": "UFVNNVZpZk4reWo5UFRualUwcGV4dz09"
   "PCD_CUST_KEY": "abcd1234567890", // 또는 인코딩된 "PCD_CUST_KEY": "T3JzRkp5L1FTcEFDa1FQdHo5Um1UZz09"
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
PCD_PAY_TYPE | 결제수단 | O | 
PCD_REGULER_FLAG | 정기결제 여부 | - | 정기결제
PCD_PAY_YEAR | 정기결제 과금연도 | - | 정기결제
PCD_PAY_MONTH | 정기결제 과금월 | - | 정기결제
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
   "PCD_PAY_BANK": "011",
   "PCD_PAY_BANKNAME": "농협",
   "PCD_PAY_BANKNUM: "460- ******** -121",
   "PCD_PAY_TIME" => "20180423130201",
   "PCD_TAXSAVE_RST": "Y",
   "PCD_REGULER_FLAG": "Y"
}
```
* Response 파라미터 설명 

파라미터 ID | 설명 | 예시
:----: | :----: | :----:
PCD_PAY_RST | 결제요청 결과 | success / error 
PCD_PAY_MSG | 결제요청 결과 메세지 | 출금이체완료 / 실패 등 
PCD_PAY_OID | 주문번호 | test201804000001
PCD_PAY_TYPE | 결제수단 | 
PCD_PAYER_NO | 결제고객 고유번호 | 1234 
PCD_PAYER_ID | 결제 키 | NS9qNTgzU2xRNHR2RmFBWWFBTWk5UT09
PCD_PAY_YEAR | 과금연도<br>(정기결제) | 2018 
PCD_PAY_MONTH | 과금월<br>(정기결제) | 08
PCD_PAY_GOODS | 상품명 | 정기구독 
PCD_PAY_TOTAL | 결제금액 | 1000
PCD_PAY_BANK | 은행코드 | 011
PCD_PAY_BANKNAME | 은행명 | 농협
PCD_PAY_BANKNUM | 계좌번호 | 460- ******** -121
PCD_PAY_BANK | 결제 은행코드 | 081
PCD_PAY_BANKNUM | 결제 계좌번호 | 2881204040404
PCD_PAY_TIME | 결제완료 시간 | 20180110152911
PCD_TAXSAVE_RST | 현금영수증 발행 결과 | Y / N 
PCD_REGULER_FLAG | 정기결제 여부 | Y / N

<br><br><br>
## 계좌이체 환불
계좌이체 금액을 환불할 수 있는 API입니다. 건마다 수수료가 발생되기 때문에 가맹점에서 서비스를 원하실 경우 페이플 고객센터(help@payple.kr)로 별도 신청해 주세요.

<br><br><br>
## 서비스가능 은행 및 점검시간 

은행명 | 코드 | 평일, 토요일 | 공휴일
:----: | :----: | :----: | :----:
국민은행 | 004 | 23:30 ~ 00:30 | 23:30 ~ 00:30 
농협 | 011 | 23:30 ~ 00:30 | 23:30 ~ 00:30 
신한은행 | 088 | 23:30 ~ 00:30 | 23:30 ~ 00:30
우리은행 | 020 | 23:30 ~ 00:30 | 23:30 ~ 00:30
기업은행 | 003 | 23:30 ~ 00:30 | 23:30 ~ 00:30
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

> 우리은행 오픈되었습니다.('19년 04월)

<br><br><br>
## 문의  
* 기술문의 : dev@payple.kr 을 통해 보다 자세한 문의가 가능합니다.
* 가입문의 : 페이플 웹사이트 [가입문의하기](https://www.payple.kr) 를 통하시면 가장 빠르게 안내 받으실 수 있습니다. 
