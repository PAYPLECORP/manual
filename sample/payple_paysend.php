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

    //발급받은 비밀키. 유출에 주의하시기 바랍니다.
    $PCD_CST_ID = "test";
    $PCD_CUST_KEY = "abcd1234567890";
    
    // 결제 요청 데이터
    $PCD_AUTH_KEY = (isset($_POST['PCD_AUTH_KEY'])) ? $_POST['PCD_AUTH_KEY'] : "";
    $PCD_PAYER_ID = (isset($_POST['PCD_PAYER_ID'])) ? $_POST['PCD_PAYER_ID'] : "";
    $PCD_PAY_REQKEY = (isset($_POST['PCD_PAY_REQKEY'])) ? $_POST['PCD_PAY_REQKEY'] : "";
    $PCD_PAY_COFURL = (isset($_POST['PCD_PAY_COFURL'])) ? $_POST['PCD_PAY_COFURL'] : "";
    
    if (!isset($_POST['PCD_AUTH_KEY']) || $PCD_AUTH_KEY == '') throw new Exception("가맹점인증KEY 값이 존재하지 않습니다.");
    if (!isset($_POST['PCD_PAYER_ID']) || $PCD_PAYER_ID == '') throw new Exception("결제자고유ID 값이 존재하지 않습니다.");
    if (!isset($_POST['PCD_PAY_REQKEY']) || $PCD_PAY_REQKEY == '') throw new Exception("결제요청 고유KEY 값이 존재하지 않습니다.");
    if (!isset($_POST['PCD_PAY_COFURL']) || $PCD_PAY_COFURL == '') throw new Exception("결제승인요청 URL 값이 존재하지 않습니다.");
    
    ///////////////////////////////////////////////// 정기결제 요청 전송 /////////////////////////////////////////////////
  
    $post_data = array (
        "PCD_CST_ID" => "$PCD_CST_ID",
        "PCD_CUST_KEY" => "$PCD_CUST_KEY",
        "PCD_AUTH_KEY" => "$PCD_AUTH_KEY",
        "PCD_PAYER_ID" => "$PCD_PAYER_ID",
        "PCD_PAY_REQKEY" => "$PCD_PAY_REQKEY"
    );
        

    
    // content-type : application/json
    // json_encoding...
    $post_data = json_encode($post_data);
    
    // cURL Header
    $CURLOPT_HTTPHEADER = array(
        "cache-control: no-cache",
        "content-type: application/json; charset=UTF-8"
    );
    
    $ch = curl_init($PCD_PAY_COFURL);
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
    
    ///////////////////////////////////////////////////////
    
    
    ///////////////////////////////////////////////// 정기결제 요청 전송 /////////////////////////////////////////////////
    // Converting To Object
    $PayResult = json_decode($PayBuffer);

    if (!isset($PayResult->PCD_PAY_RST)) {
        
        throw new Exception("결제승인 결과수신 실패");
    }
    
    if (isset($PayResult->PCD_PAY_RST) && $PayResult->PCD_PAY_RST != '') {
        
        
        $pay_rst = $PayResult->PCD_PAY_RST;             // success | error
        $pay_msg = $PayResult->PCD_PAY_MSG;             // 출금이체완료 | 가맹점 건당 한도 초과.., 가맹점 월 한도 초과.., 등록된 계좌정보를 찾을 수 없습니다..., 최초 결제자 입니다. 본인인증 후 이용하세요...
        $pay_reqkey = $PayResult->PCD_PAY_REQKEY;       // 결제요청 고유KEY
        $pay_oid = $PayResult->PCD_PAY_OID;
        $pay_type = $PayResult->PCD_PAY_TYPE;           // 결제방법 (transfer)
        $payer_no = $PayResult->PCD_PAYER_NO;           // 결제자 고유번호 (가맹점 회원 회원번호)
        $payer_name = $PayResult->PCD_PAYER_NAME;       // 결제자 명
        $payer_hp = $PayResult->PCD_PAYER_HP;           // 결제자 휴대폰번호
        $payer_email = $PayResult->PCD_PAYER_EMAIL;     // 결제자 Email
        $pay_year = $PayResult->PCD_PAY_YEAR;           // (정기결제) 과금 년도
        $pay_month = $PayResult->PCD_PAY_MONTH;         // (정기결제) 과금 월
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
        
        
        // 결제요청 결과 수신
        if ($pay_rst == 'success') {
            
            
            // 출금성공 결과 처리...
            
            // DB PROCESS
            /*
             INSERT INTO paylist
             (PListNo, pay_oid, payer_name, payer_hp, payer_email, payer_birth, pay_year, pay_month, pay_goods, pay_type, pay_total, pay_bank, pay_banknum, taxsaave_flag, taxsave_trade, taxsave_idnum ...)
             VALUES
             ('$No', '$pay_oid', '$payer_name', '$payer_hp', '$payer_email', '$payer_birth', '$pay_year', '$pay_month', '$pay_goods', '$pay_type', $pay_total, '$pay_bank', '$pay_banknum', '$taxsaave_flag', '$taxsave_trade', '$taxsave_idnum')
             */
            
            $payed_cnt++;
            
        }
        
        //
        $DATA = array(
            "PCD_PAY_RST" => "$pay_rst",
            "PCD_PAY_MSG" => "$pay_msg",
            "PCD_PAY_REQKEY" => "$pay_reqkey",
            "PCD_PAY_OID" => "$pay_oid",
            "PCD_PAY_TYPE" => "$pay_type",
            "PCD_PAYER_NO" => "$payer_no",
            "PCD_PAYER_NAME" => "$payer_name",
            "PCD_PAYER_HP" => "$payer_hp",
            "PCD_PAYER_EMAIL" => "$payer_email",
            "PCD_PAYER_BIRTH" => "$payer_birth",
            "PCD_PAY_YEAR" => "$pay_year",
            "PCD_PAY_MONTH" => "$pay_month",
            "PCD_PAY_GOODS" => "$pay_goods",
            "PCD_PAY_TOTAL" => "$pay_total",
            "PCD_PAY_BANK" => "$pay_bank",
            "PCD_PAY_BANKNUM" => "$pay_banknum",
            "PCD_PAY_TIME" => "$pay_time",
            "PCD_TAXSAVE_RST" => "$taxsave_rst"
        );
        
        
        
        $JSON_DATA = json_encode($DATA);
        
        echo $JSON_DATA;
        
        exit;
        
    } else {
        
       throw new Exception();
        
    }
    
    
    
} catch (Exception $e) {
    
    $errMsg = $e->getMessage();
    
    $message = ($errMsg != '') ? $errMsg : "결제승인요청 에러";
    if ($REMOTE_ADDR == '1.241.226.114') $message = $message . $e->getLine();
    
    $DATA = "{\"result\":\"error\", \"message\":\"$message\"}";
    
    echo $DATA;
    
}
?>
