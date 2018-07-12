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
<pre><code>
<meta charset="UTF-8">
</code></pre>
