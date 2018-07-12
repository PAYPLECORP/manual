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
