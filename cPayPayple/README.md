## cPayPayple  
#### 설명 
* PAYPLE 서버와의 통신을 위한 파일로 구성되어 있습니다. 
* 해당 폴더를 가맹점 Web Root 에 저장하고 각 파일을 가맹점 환경에 맞는 개발언어로 수정해주시면 됩니다. 
#### 파일별 설명 
* [payple_payAuth.html](payple_payAuth.html)
  * 가맹점 인증을 진행하는 페이지이며, PAYPLE 서버에서 호출하는 페이지 입니다.
  * 기본 PHP 로 적용되어 있으며, 가맹점 환경에 맞는 개발언어로 수정해주세요.
* ~~[payple_iframe.html](payple_iframe.html)~~
  * ~~가맹점의 웹사이트에 iframe 형태로 결제창을 로드하기 위해 필요한 페이지 입니다.~~
  * ~~가맹점에서 직접 호출하지 않으며, 가맹점 환경에 맞는 개발언어로 수정해주세요.~~
  * 업데이트된 1.0.1 버전에서는 payple_iframe.html 이 필요없습니다. 
* [payple_ResultRecv.html](payple_ResultRecv.html)
  * 결제완료되었으나 가맹점에서 결과데이터 미수신 시 PAYPLE 서버에서 자동으로 결과데이터를 전송하는 페이지 입니다.
  * 기본 PHP 로 적용되어 있으며 가맹점 환경에 맞는 개발언어로 수정해주세요.
