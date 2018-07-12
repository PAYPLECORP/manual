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
   
    ##################################################### AuthKey REQ #####################################################

    $url = "https://testcpay.payple.kr/php/auth.php";   // TEST    
    
    //발급받은 비밀키. 유출에 주의하시기 바랍니다.
    $post_data = array (
        "cst_id" => "test",
        "custKey" => "abcd1234567890",
        "PCD_REGULER_FLAG" => "Y"
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
    $PayReqURL = $AuthResult->return_url;           // 정기결제요청 URL

    
    
    ##################################################### PAY REQ #####################################################
    
    // 정기결제 요청 데이터
    $PListNo = (isset($_POST['PListNo'])) ? $_POST['PListNo'] : "";
    $name = (isset($_POST['name'])) ? $_POST['name'] : "";
    $hp = (isset($_POST['name'])) ? $_POST['hp'] : "";
    $birth = (isset($_POST['birth'])) ? $_POST['birth'] : "";
    $email = (isset($_POST['email'])) ? $_POST['email'] : "";
    $bank = (isset($_POST['bank'])) ? $_POST['bank'] : "";
    $banknum = (isset($_POST['banknum'])) ? $_POST['banknum'] : "";
    $goods = (isset($_POST['goods'])) ? $_POST['goods'] : "";
    $year = (isset($_POST['year'])) ? $_POST['year'] : "";
    $month = (isset($_POST['month'])) ? $_POST['month'] : "";
    $total = (isset($_POST['total'])) ? $_POST['total'] : "";
    $oid = (isset($_POST['oid'])) ? $_POST['oid'] : "";
    $isTax = (isset($_POST['isTax'])) ? $_POST['isTax'] : "";
    $taxtrade = (isset($_POST['taxtrade'])) ? $_POST['taxtrade'] : "";
    $taxidnum = (isset($_POST['taxidnum'])) ? $_POST['taxidnum'] : "";
    
    
    ///////////////////////////////////////////////// 정기결제 요청 전송 /////////////////////////////////////////////////
    
    if (is_array($PListNo) && count($PListNo) > 0) {
        
        // DEFAULT SET
        $PCD_RESULT = array();
        $req_cnt = count($PListNo);             // 출금요청 건수
        $payed_cnt = 0;                         // 출금완료 건수
        $fail_cnt = 0;                          // 출금실패 건수
        $pay_type = "transfer";                 // 출금방법
        
        
        for ($i = 0; $i < $req_cnt; $i++) {
            
            $no = $PListNo[$i];                 // 결제자 고유번호 (가맹점 회원 번호)
            
            $payer_name = $name[$no];           // 결제자명
            $payer_hp = $hp[$no];               // 결제자 휴대폰번호
            $payer_birth = $birth[$no];         // 결제자 생년월일
            $payer_email = $email[$no];         // 결제자 Email
            $pay_bank = $bank[$no];             // 은행 코드
            $pay_banknum = $banknum[$no];       // 계좌번호
            $pay_goods = $goods[$no];           // 결제 상품명
            $pay_year = $year[$no];             // 결제구분 년도
            $pay_month = $month[$no];           // 결제구분 월
            $pay_total = $total[$no];           // 결제금액
            $pay_oid = $oid[$no];               // 주문번호
            $taxsave_flag = $isTax[$no];        // 현금영수증 발행 Y|N
            $taxsave_trade = $taxtrade[$no];    // personal:소득공제용, company:지출증빙
            $taxsave_idnum = $taxidnum[$no];    // 현금영수증 발행대상 번호
           
            
            $pay_data = array (
                "PCD_CST_ID" => "$cst_id",
                "PCD_CUST_KEY" => "$custKey",
                "PCD_AUTH_KEY" => "$AuthKey",
                "PCD_PAY_TYPE" => "$pay_type",
                "PCD_PAYER_NO" => "$no",
                "PCD_PAYER_NAME" => "$payer_name",
                "PCD_PAYER_HP" => "$payer_hp",
                "PCD_PAYER_BIRTH" => "$payer_birth",
                "PCD_PAY_BANK" => "$pay_bank",
                "PCD_PAY_BANKNUM" => "$pay_banknum",
                "PCD_PAY_GOODS" => "$pay_goods",
                "PCD_PAY_YEAR" => "$pay_year",
                "PCD_PAY_MONTH" => "$pay_month",
                "PCD_PAY_TOTAL" => $pay_total,
                "PCD_PAY_OID" => "$pay_oid",
                "PCD_TAXSAVE_FLAG" => "$taxsave_flag",
                "PCD_REGULER_FLAG" => "Y",
                "PCD_TAXSAVE_TRADE" => "$taxsave_trade",
                "PCD_TAXSAVE_IDNUM" => "$taxsave_idnum",
                "PCD_PAYER_EMAIL" => "$payer_email"
            );
            
            // content-type : application/json
            // json_encoding...
            $post_data = json_encode($post_data);
            
            //////////////////// cURL Data Send ////////////////////
            $ch = curl_init($PayReqURL);
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
            
            if (isset($PayResult->PCD_PAY_RST) && $PayResult->PCD_PAY_RST != '') {
            
            
                $pay_rst = $PayResult->PCD_PAY_RST;             // success | error
                $pay_msg = $PayResult->PCD_PAY_MSG;             // 출금이체완료 | 가맹점 건당 한도 초과.., 가맹점 월 한도 초과.., 등록된 계좌정보를 찾을 수 없습니다..., 최초 결제자 입니다. 본인인증 후 이용하세요...
                $pay_oid = $PayResult->PCD_PAY_OID;
                $pay_type = $PayResult->PCD_PAY_TYPE;           // 결제방법 (transfer)
                $payer_no = $PayResult->PCD_PAYER_NO;           // 결제자 고유번호 (가맹점 회원 회원번호)
                $payer_name = $PayResult->PCD_PAYER_NAME;       // 결제자 명
                $payer_hp = $PayResult->PCD_PAYER_HP;           // 결제자 휴대폰번호
                $payer_email = $PayResult->PCD_PAYER_EMAIL;     // 결제자 Email
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
                
            
            } else {
                
                $pay_rst = "error";                // success | error
                $pay_msg = "출금요청실패";            // 출금이체완료 | 가맹점 건당 한도 초과.., 가맹점 월 한도 초과.., 등록된 계좌정보를 찾을 수 없습니다..., 최초 결제자 입니다. 본인인증 후 이용하세요...
                $pay_oid = $pay_oid;               // 주문번호  
                $pay_type = $pay_type;             // 결제방법 (transfer)
                $payer_no = $no;                   // 결제자 고유번호 (가맹점 회원 회원번호)
                $payer_name = $payer_name;         // 결제자 명
                $payer_hp = $payer_hp;             // 결제자 휴대폰번호
                $payer_email = $payer_email;       // 결제자 Email
                $payer_birth = $payer_birth;       // 결제자 생년월일 8자리
                $pay_year = $pay_year;             // 결제구분 년
                $pay_month = $pay_month;           // 결제구분 월
                $pay_goods = $pay_goods;           // 결제 상품
                $pay_total = $pay_total;           // 결제 금액
                $pay_bank = $pay_bank;             // 결제 은행코드
                $pay_banknum = $pay_banknum;       // 결제 계좌번호
                $pay_time = "";                    // 결제완료 시간
                $taxsave_rst = "N";                // 현금영수증 발행결과 (Y|N)
                $reguler_flag = "Y";               // 정기결제 요청여부 (Y|N)
                
            }
            
            
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
                
            } else {

                // 출금실패 결과 처리
                
                $fail_cnt++;
            }
            
            // 
            $PCD_RESULT[$i] = array(
                "PCD_PAY_RST" => "$pay_rst",
                "PCD_PAY_MSG" => "$pay_msg",
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
            
            

        } // for ($i = 0; $i < count($PListNo); $i++) {..}
  
        if (count($PCD_RESULT) > 0) {
        
            /*
             * req_cnt : 출금요청 건수
             * payed_cnt : 출금완료 건수
             * fail_cnt : 출금실패 건수
             */
            
            $DATA = array(
                "result" => "success",
                "message" => "정기결제출금요청완료",
                "req_cnt" => "$req_cnt",
                "payed_cnt" => "$payed_cnt",
                "fail_cnt" => "$fail_cnt",
                
                "PCD_RESULT" => $PCD_RESULT
            );
            
            $JSON_DATA = json_encode($DATA);
            
            echo $JSON_DATA;
        
        } else {
            throw new Exception("[ERROR] 출금요청  건수 : 0");
        }
        exit;
            
    
    } // if (is_array($payer_name) && count($payer_name) > 0) {..}

    
    
} catch (Exception $e) {
 
    $errMsg = $e->getMessage();
    
    $message = ($errMsg != '') ? $errMsg : "정기결제요청 에러";
    
    $DATA = "{\"result\":\"error\", \"message\":\"$message\"}";
    
    echo $DATA;
    
}
?>