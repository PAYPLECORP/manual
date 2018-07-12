<!-- 결과조회 샘플 -->
<?php
/*
 * 외부에서 직접 접속하여 실행되지 않도록 프로그래밍 하여 주시기 바랍니다.
 * cst_id, custKey, AuthKey 등 접속용 key 는 절대 외부에 노출되지 않도록
 * 서버 사이드 스크립트(server-side script) 내부에서 사용되어야 합니다.
 */

header("Expires: Mon 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d, M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0; pre-check=0", false);
header("Pragma: no-cache");
header("Content-type: application/json; charset=utf-8");

try {
    
    $reguler_flag = (isset($_POST['is_reguler'])) ? $_POST['is_reguler'] : "";
    $pay_oid = (isset($_POST['pay_oid'])) ? $_POST['pay_oid'] : "";
    $pay_year = (isset($_POST['pay_year'])) ? preg_replace("/([^0-9]+)/", "", $_POST['pay_year']) : "";
    $pay_month = (isset($_POST['pay_month'])) ? preg_replace("/([^0-9]+)/", "", $_POST['pay_month']) : "";
    $pay_date = (isset($_POST['pay_date'])) ? preg_replace("/([^0-9]+)/", "", $_POST['pay_date']) : "";
    
    ##################################################### AuthKey REQ #####################################################        
        
    $url = "https://testcpay.payple.kr/php/auth.php";   // TEST
    //$url = "https://cpay.payple.kr/php/auth.php";       // REAL
    
    
    //발급받은 비밀키. 유출에 주의하시기 바랍니다.
    $post_data = array (
        "custKey" => "abcd1234567890",
        "cst_id" => "test",
        "PCD_PAYCHK_FLAG" => "Y"
    );

    
    // content-type : application/json
    // json_encoding...
    $post_data = json_encode($post_data);
    
    // cURL Header
    $CURLOPT_HTTPHEADER = array(
        "cache-control: no-cache",
        "content-type: application/json; charset=UTF-8"
    );
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    
    if ($REMOTE_ADDR != '127.0.0.1') {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSLVERSION, 3);
    }
    
    curl_setopt($ch, CURLOPT_REFERER, $SERVER_NAME);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $CURLOPT_HTTPHEADER);
    
    ob_start();
    $AuthRes = curl_exec($ch);
    $AuthBuffer = ob_get_contents();
    ob_end_clean();
    
    // Converting To Object
    $AuthResult = json_decode($AuthBuffer);
    
    if (!isset($AuthResult->result)) throw new Exception("인증요청 실패");
    
    if ($AuthResult->result != 'success') throw new Exception($AuthResult->result_msg);
    
    $cst_id = $AuthResult->cst_id;                  // 가맹점 ID
    $custKey = $AuthResult->custKey;                // 가맹점 키
    $AuthKey = $AuthResult->AuthKey;                // 인증 키
    $PayInfoURL = $AuthResult->return_url;           // 결제내역조회 URL
    

    ///////////////////////////////////////////////// 결제내역 요청 전송 /////////////////////////////////////////////////

    $pay_type = "transfer";                             // 결제방법
    $pay_year = "";                                     // 결제구분 년도
    $pay_month = "";                                    // 결제구분 월
    $pay_oid = $pay_oid;                                // 주문번호
    $reguler_flag = $reguler_flag;                      // 정기결제 여부 Y|N
    $pay_date = $pay_date;                              // 결제일자
        
    $pay_data = array (
        "PCD_CST_ID" => "$cst_id",
        "PCD_CUST_KEY" => "$custKey",
        "PCD_AUTH_KEY" => "$AuthKey",
        "PCD_PAYCHK_FLAG" => "Y",
        "PCD_PAY_TYPE" => "$pay_type",
        "PCD_PAY_YEAR" => "$pay_year",
        "PCD_PAY_MONTH" => "$pay_month",
        "PCD_PAY_OID" => "$pay_oid",
        "PCD_REGULER_FLAG" => "$reguler_flag",
        "PCD_PAY_DATE" => "$pay_date"
    );

    // content-type : application/json
    // json_encoding...
    $post_data = json_encode($pay_data);
    
    //////////////////// cURL Data Send ////////////////////
    $ch = curl_init($PayInfoURL);
    curl_setopt($ch, CURLOPT_POST, true);
    
    if ($REMOTE_ADDR != '127.0.0.1') {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSLVERSION, 3);
    }
    
    curl_setopt($ch, CURLOPT_REFERER, $SERVER_NAME);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $CURLOPT_HTTPHEADER);
    
    ob_start();
    $PayRes = curl_exec($ch);
    $PayBuffer = ob_get_contents();
    ob_end_clean();

    ///////////////////////////////////////////////// 결제내역 조회 결과 /////////////////////////////////////////////////
    // Converting To Object
    $PayResult = json_decode($PayBuffer);
        
    $pay_rst = $PayResult->PCD_PAY_RST;             // success | error
    $pay_msg = $PayResult->PCD_PAY_MSG;             // 출금이체완료 | 가맹점 건당 한도 초과.., 가맹점 월 한도 초과.., 등록된 계좌정보를 찾을 수 없습니다..., 최초 결제자 입니다. 본인인증 후 이용하세요...
    $pay_oid = $PayResult->PCD_PAY_OID;             // 주문번호
    $pay_type = $PayResult->PCD_PAY_TYPE;           // 결제방법 (transfer)
    $payer_no = $PayResult->PCD_PAYER_NO;           // 결제자고유번호
    $payer_name = $PayResult->PCD_PAYER_NAME;       // 결제자 명
    $payer_hp = $PayResult->PCD_PAYER_HP;           // 결제자 휴대폰번호
    $payer_birth = $PayResult->PCD_PAYER_BIRTH;     // 결제자 생년월일 8자리
    $pay_year = $PayResult->PCD_PAY_YEAR;           // 결제구분 년
    $pay_month = $PayResult->PCD_PAY_MONTH;         // 결제구분 월
    $pay_goods = $PayResult->PCD_PAY_GOODS;         // 결제 상품
    $pay_total = $PayResult->PCD_PAY_TOTAL;         // 결제 금액
    $pay_bank = $PayResult->PCD_PAY_BANK;           // 결제 은행코드
    $pay_banknum = $PayResult->PCD_PAY_BANKNUM;     // 결제 계좌번호
    $pay_time = $PayResult->PCD_PAY_TIME;           // 결제완료 시간
    $taxsave_rst = $PayResult->PCD_TAXSAVE_RST;     // 현금영수증 발행결과 (Y|N)
    $reguler_flag = $PayResult->PCD_REGULER_FLAG;   // 정기결제 요청여부 (Y|N)

    $PCD_RESULT = array(
        "PCD_PAY_RST" => "$pay_rst",
        "PCD_PAY_MSG" => "$pay_msg",
        "PCD_PAY_OID" => "$pay_oid",
        "PCD_PAY_TYPE" => "$pay_type",
        "PCD_PAYER_NO" => "$payer_no",
        "PCD_PAYER_NAME" => "$payer_name",
        "PCD_PAYER_HP" => "$payer_hp",
        "PCD_PAYER_BIRTH" => "$payer_birth",
        "PCD_PAY_YEAR" => "$pay_year",
        "PCD_PAY_MONTH" => "$pay_month",
        "PCD_PAY_GOODS" => "$pay_goods",
        "PCD_PAY_TOTAL" => "$pay_total",
        "PCD_PAY_BANK" => "$pay_bank",
        "PCD_PAY_BANKNUM" => "$pay_banknum",
        "PCD_PAY_TIME" => "$pay_time",
        "PCD_TAXSAVE_RST" => "$taxsave_rst",
        "PCD_REGULER_FLAG" => "$reguler_flag"
    );

    
    $JSON_DATA = json_encode($PCD_RESULT);
    
    echo $JSON_DATA;
    exit;
    
    
} catch (Exception $e) {
    
    $errMsg = $e->getMessage();
    
    $message = ($errMsg != '') ? $errMsg : "결제내역 조회 에러";
    
    $PCD_RESULT = array(
        "PCD_PAY_RST" => "error",
        "PCD_PAY_MSG" => "$message",
        "PCD_PAY_OID" => "$pay_oid",
        "PCD_PAY_TYPE" => "",
        "PCD_PAYER_NAME" => "",
        "PCD_PAYER_HP" => "",
        "PCD_PAYER_BIRTH" => "",
        "PCD_PAY_YEAR" => "$pay_year",
        "PCD_PAY_MONTH" => "$pay_month",
        "PCD_PAY_GOODS" => "",
        "PCD_PAY_TOTAL" => "",
        "PCD_PAY_BANK" => "",
        "PCD_PAY_BANKNUM" => "",
        "PCD_PAY_TIME" => "$pay_date",
        "PCD_TAXSAVE_RST" => "",
        "PCD_REGULER_FLAG" => "$reguler_flag"
    );
    
    $JSON_DATA = json_encode($PCD_RESULT);
    
    echo $JSON_DATA;
    exit;
    
}
?>
