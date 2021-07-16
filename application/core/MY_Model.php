<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
//require 'vendor/autoload.php';

Class MY_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('string', 'text');
    }

    private function str_equal($str)
    {
        return str_replace('=', '%3D', $str);
    }
    public function getParamStringAdmin($param)
    {
        return addslashes(isset($_REQUEST[$param]) ?  $this->str_equal($_REQUEST[$param]) : '');
    }
    
	public function getParamString($param)
    {
        return addslashes(isset($_REQUEST[$param]) ?  $this->security->xss_clean($_REQUEST[$param]) : '');
    }
    
    public function getParamArray($param)
    {
        return isset($_REQUEST[$param]) ?  $this->security->xss_clean($_REQUEST[$param]) : '';
    }
    
    public function getParamInt($param)
    {
        return intval(isset($_REQUEST[$param]) ?  $this->security->xss_clean($_REQUEST[$param]) : '');
    }
    
    public function getParamUrl($id)
    {
        return intval(MY_Model::decode_id($this->security->xss_clean($this->uri->segment($id))));
    }

    public function checkUseCouponCode($coupon_code){
        if( @$coupon_code == '2020' ){
            die(json_encode(array(
                'status' => 201,
                'content' => 'Mã giảm giá '.$coupon_code.' chỉ dành cho khách hàng khi gia hạn gói bảo hiểm!',
                'data' => '0'
            )));
        }
    }

    public function funccheckcoupon($params) {
        try {
            $getUserID = $this->getUserID($params['_id']);
            if ($getUserID['status'] != 200) {
                return array(
                    'status' => 201,
                    'content' => 'Tài khoản không tồn tại',
                    'data' => (object) array()
                );
            }
            $user_id = $getUserID['user_id'];

            $codeReferDB = strtoupper(trim($params['coupon_code']));
            if( strpos($codeReferDB, 'DB_') !== false && in_array($params['package_id'],array(15,20))  ) {
                $store = @$this->mongo_db->where(array('store_code' => $codeReferDB))->get('tbl_store_insurance')[0];
                if (!in_array($store, array('', null))) {
                    return array(
                        'status' => 200,
                        'content' => 'Bạn đã áp dụng mã giới thiệu của Điểm Bán',
                        'data' => 0,
                        'user' => array(
                            'fullname' => @$store['store_name'],
                            'mobile' => @$store['mobile']
                        )
                    );
                }
            }

            // check user là MCV branch_id = 108 thì ko được sử dụng coupon
            $dataUser = @$this->mongo_db->where(array('user_id' => new MongoInt64($user_id)))->get('tbl_user')[0];
            $check = $this->mongo_db->where(array('mobile' => $dataUser['mobile'], 'branch_id' => new MongoInt64(108) ))->count('tbl_vass_staff_dvkd');
            if( $check > 0 ){
                return array(
                    'status' => 201,
                    'content' => 'Tài khoản thuộc chi nhánh MCV không được sử dụng mã giảm giá',
                    'data' => (object) array()
                );
            }
            // check type_loan nếu = 0 mua chính họ và type_loan khác 0,1 thì báo lỗi
            if (!in_array($params['type_loan'], array(0, 1))) {
                return array(
                    'status' => 201,
                    'content' => 'Kiểu hồ sơ không đúng',
                    'data' => (object) array()
                );
            }
            // check người mua bảo hiểm là user bric thì KO sử dụng mã giảm giá
            // 16/10 Dungdc sửa lại : tất cả các đối tác đều không được sử dụng mã giảm giá ví dụ Brics, Ola, P5, Kts...
            $count_brics = $this->mongo_db->select(array('user_id'))->where(array('type_user' => new MongoInt64(2), 'user_id' => new MongoInt64($user_id)))->count('tbl_user');
            if ($count_brics > 0) {
                return array(
                    'status' => 201,
                    'content' => 'Thành viên của đối tác của LIAN không thể sử dụng mã giảm giá',
                    'data' => (object) array()
                );
            }

            // check DL, CTV và người được họ giới thiệu KHÔNG ĐƯỢC sử dụng mã giảm giá
            $check_agency = $this->mongo_db->select(array('agency', 'user_referer'))->where(array('user_id' => new MongoInt64($user_id)))->get('tbl_user')[0];
            if ($check_agency['agency']['active'] == 1) {
                // CTV, DL
                if ($check_agency['agency']['type'] == 'DL') {
                    $agency_type = 'Đại Lý';
                } elseif ($check_agency['agency']['type'] == 'CTV') {
                    $agency_type = 'Cộng Tác Viên';
                }

                return array(
                    'status' => 201,
                    'content' => $agency_type . ' không thể sử dụng tính năng này',
                    'data' => (object) array()
                );
            } else {
                // check user_referer
                if (!in_array($check_agency['user_referer'], array(null, ''))) {

                    $check_agency_referer = $this->mongo_db->select(array('agency'))->where(array('user_id' => new MongoInt64($check_agency['user_referer'])))->get('tbl_user')[0];
                    if ($check_agency_referer['agency']['active'] == 1) {
                        // Người giới thiệu của user là CTV, DL
                        return array(
                            'status' => 201,
                            'content' => 'Bạn không thể sử dụng tính năng này',
                            'data' => (object) array()
                        );
                    }

                }
            }

            // check người mua bảo hiểm là end user
            $count_enduser = $this->mongo_db->select(array('user_id'))->where(array('type_user' => new MongoInt64(1), 'user_id' => new MongoInt64($user_id)))->where_ne('agency.active', new MongoInt64(1))->count('tbl_user');
            if ($count_enduser > 0) {
                $count = $this->mongo_db->where(array('code_referer' => $params['coupon_code'], 'user_id' => new MongoInt64($user_id)))->count('tbl_user');
                if ($count > 0 && $params['type_loan'] == 0) {
                    return array(
                        'status' => 201,
                        'content' => 'Bạn không thể dùng mã giới thiệu của chính bạn',
                        'data' => '0'
                    );
                } else {
                    $mobile_db = @$this->mongo_db->select(array('mobile'))->where(array('user_id' => new MongoInt64($user_id)))->get('tbl_user')[0]['mobile'];
                    if ($count > 0 && $params['type_loan'] == 1 && $params['mobile'] == @$mobile_db) {
                        return array(
                            'status' => 201,
                            'content' => 'Bạn không thể dùng mã giới thiệu của chính bạn (02)',
                            'data' => '0'
                        );
                    }
                }
            }
            // đại lý Vass thì luôn luôn đc sử dụng coupon méo cần check --- lol
            // check xem có phải là coupon gói xe máy ko abc
            if( $params['coupon_code'] == '6868' ){
                if( !in_array(@$params['package_id'],array('',null)) ){
                    if( $params['package_id'] != 15 ){
                        return array(
                            'status' => 201,
                            'content' => 'Mã 6868 chỉ áp dụng cho gói BH TNDS Mô tô - Xe máy, Loại xe trên 50cc và có mua BH Tự nguyện cho người ngồi trên xe!',
                            'data' => (object) array()
                        );
                    }else{
                        if( $params['motor_type'] != 2 ){
                            return array(
                                'status' => 201,
                                'content' => 'Mã 6868 chỉ áp dụng cho gói BH TNDS Mô tô - Xe máy, Loại xe trên 50cc và có mua BH Tự nguyện cho người ngồi trên xe!',
                                'data' => (object) array()
                            );
                        }
                    }
                }
                $percent_sale = 20;
                return array(
                    'status' => 200,
                    'content' => 'Bạn đã áp dụng mã dành cho gói Bảo hiểm TNDS Mô tô - Xe máy',
                    'data' => $percent_sale,
                    'user' => array(
                        'fullname' => 'VASS',
                        'mobile' => 'VASS'
                    )
                );
            }

            /*
                04/11/2020 Dungdc thêm mã giảm giá 10% cho sự kiện lian tròn 2 tuổi
                áp dụng với user thường khi gia hạn
                nếu loan_id truyền lên null thì có nghĩa là mua mới
            */
            if( $params['coupon_code'] == '2020' ){
                $percent_sale = 10;
                $this->mongo_db->insert('tbl_log_check_coupon', array(
                    'user_id' => new MongoInt64($user_id),
                    'coupon_code' => $params['coupon_code'],
                    'percent_sale' => $percent_sale,
                    'created' => new MongoDate(time()),
                    'params' => $params
                ));
                if( in_array(@$params['loan_id'],array('',null)) ){
                    return array(
                        'status' => 201,
                        'content' => 'Mã giảm giá này chỉ áp dụng khi gia hạn gói bảo hiểm!',
                        'data' => 0
                    );
                }
                return array(
                    'status' => 200,
                    'content' => 'Bạn đã áp dụng thành công mã giảm giá '.$percent_sale.'% khi gia hạn gói bảo hiểm!',
                    'data' => $percent_sale,
                    'user' => array(
                        'fullname' => 'VASS',
                        'mobile' => 'VASS'
                    )
                );
            }

            $count2 = $this->mongo_db->where(array('code_referer' => $params['coupon_code']))->count('tbl_user');
            if ($count2 > 0) {
                $COUPON = @$this->mongo_db->select(array('content'))->where(array('type' => 'COUPON'))->get('tbl_option')[0]['content'];
                $user = @$this->mongo_db->select(array('mobile', 'fullname'))->where(array('code_referer' => $params['coupon_code']))->get('tbl_user')[0];
                return array(
                    'status' => 200,
                    'content' => 'Áp dụng mã giới thiệu thành công. Người giới thiệu ' . (@$user['fullname'] ? $user['fullname'] : @$user['mobile']) . '.',
                    'data' => @$COUPON['sale'] ? $COUPON['sale'] : '0',
                    'user' => array(
                        'fullname' => @$user['fullname'] ? $user['fullname'] : '',
                        'mobile' => @$user['mobile'] ? $user['mobile'] : ''
                    )
                );
            } else {
                return array(
                    'status' => 201,
                    'content' => 'Mã giới thiệu không tồn tại',
                    'data' => '0'
                );
            }
        } catch (Exception $e) {
            return array(
                'status' => 201,
                'content' => $e->getMessage(),
                'data' => (object) array()
            );
        }
    }
	
	public function getParamGet($param,$type)
	{
		if($type == '1')
        {
            return intval($this->security->xss_clean($_GET[$param]));
        }else{
            return addslashes($this->security->xss_clean($_GET[$param]));
        } 
	}

    public function get_ascii_none_replace($st) {

        $vietChar = 'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ|é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ|ó|ò|ỏ|õ|ọ|ơ|ớ|ờ|ở|ỡ|ợ|ô|ố|ồ|ổ|ỗ|ộ|ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự|í|ì|ỉ|ĩ|ị|ý|ỳ|ỷ|ỹ|ỵ|đ|Á|À|Ả|Ã|Ạ|Ă|Ắ|Ằ|Ẳ|Ẵ|Ặ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ|É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ|Ó|Ò|Ỏ|Õ|Ọ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự|Í|Ì|Ỉ|Ĩ|Ị|Ý|Ỳ|Ỷ|Ỹ|Ỵ|Đ';
        $engChar = 'a|a|a|a|a|a|a|a|a|a|a|a|a|a|a|a|a|e|e|e|e|e|e|e|e|e|e|e|o|o|o|o|o|o|o|o|o|o|o|o|o|o|o|o|o|u|u|u|u|u|u|u|u|u|u|u|i|i|i|i|i|y|y|y|y|y|d|A|A|A|A|A|A|A|A|A|A|A|A|A|A|A|A|A|E|E|E|E|E|E|E|E|E|E|E|O|O|O|O|O|O|O|O|O|O|O|O|O|O|O|O|O|U|U|U|U|U|U|U|U|U|U|U|I|I|I|I|I|Y|Y|Y|Y|Y|D';

        $arrVietChar = explode("|", $vietChar);
        $arrEngChar = explode("|", $engChar);
        $ftc = strtolower(str_replace($arrVietChar, $arrEngChar, (string) $st));
        $ftc_ = str_replace($arrVietChar, $arrEngChar, $ftc);

        return $ftc_;
    }

    public function valid_email($str) {
        return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? 1 : 0;
    }
	
    public function getParamUri($id,$param)
    {
        if($param == '1')
        {
            return intval($this->security->xss_clean($this->uri->segment($id)));
        }else if($param == '3')
        {
            $param_ = $this->security->xss_clean($this->uri->segment($id));
            if(in_array($param_, array(null, '', '0')))
            {
                return 0;
            }
            $data = explode('-',addslashes($param_));
            return $data[1];
        }else if($param == '4')
        {
            $param_ = $this->security->xss_clean($this->uri->segment($id));
            if(in_array($param_, array(null, '', '0')))
            {
                return 0;
            }
            $data = explode('-',addslashes($param_));
            return $data;
        }else{
            return addslashes($this->security->xss_clean($this->uri->segment($id)));
        } 
    }
	
    public function generateCode($n){
        $n = rand(10e16, 10e20);
        return base_convert($n, 10, 36);      
    }
    
    public function microtime_float()
    {
        $microtime = microtime();
        $comps = explode(' ', $microtime);
        return sprintf('%d%03d', $comps[1], $comps[0] * 1000);
    }

    public function generateCodeNumber($length) {
        $chars = "01234567890123456789";
        $code = "";
        $clen = strlen($chars) - 1;
        while (strlen($code) < $length) {
            $code .= $chars[mt_rand(0, $clen)];
        }
        $salt = $code;
        return $salt;
    }

    public function generateCodeAllChar($length) {
        $chars = "01234567890123456789QWERTYUIOPLKJHGFDSAZXCVBNM";
        $code = "";
        $clen = strlen($chars) - 1;
        while (strlen($code) < $length) {
            $code .= $chars[mt_rand(0, $clen)];
        }
        $salt = $code;
        return $salt;
    }

    public function generaCodeRandom($number,$length=8){
        $number=(string)$number;
        if($length-strlen($number)<=2) $length= strlen($number)+2;
        $ranges = range(0,$length-1);
        $positions=[];
        $texts='ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lengthText=strlen($texts);
        for($i=0;$i<$length-strlen($number);$i++){
            $string = $texts[rand(0,$lengthText-1)];
            $pos = array_rand($ranges);

            $positions[$pos]=$string;
            unset($ranges[$pos]);
        }
        $letter=[];
        $posNumber=0;
        for($i=0;$i<$length;$i++){
            if(in_array($i,array_keys($positions))){
                $letter[]=$positions[$i];
            }else{
                $letter[] = $number[$posNumber];
                $posNumber++;
            }
        }

        return implode("",$letter);
    }

    public function payment($payment_method = 1, $order_id = 0, $code_gift = '', $amount = 0, $order_info, $ip_client,$urlCancel='',$other=[]){

        $action = '';
        switch ($payment_method){
            case 2:
                $action = 'create-payment-visa';
                break;
            case 3:
                $action='create-buy-by-gift';
                break;
            case 4:
                $action = 'create-buy-by-card';
                break;
            case 1:
            default:
                $action='create-payment-atm';
                break;
        }


        $secret = SECRET_PAYMENT;
        $data_result = "access_key=".ACCESS_PAYMENT
            . "&amount=" . $amount
            . "&order_id=" . $order_id
            . "&order_info=" . $order_info
            . "&ip_client=" . $ip_client;

        if($payment_method==3){
            $data_result = "access_key=".ACCESS_PAYMENT
                . "&amount=" . $amount
                . "&order_id=" . $order_id
                . "&code_gift=" . $code_gift
                . "&order_info=" . $order_info
                . "&ip_client=" . $ip_client;
        }
        $signatureServer = hash_hmac("sha256", $data_result, $secret);
        $curl = curl_init();
        $content = 'order_id='.$order_id
            .'&code_gift='.$code_gift
            .'&amount='.$amount
            .'&order_info='.urlencode($order_info)
            .'&ip_client='.$ip_client
            .'&signature='.$signatureServer
            .'&url_cancel='.$urlCancel
            .'&ref_code='.$other['ref_code']
            .'&user_id='.$other['user_id']
            .'&user_oid='.$other['user_oid']
            .'&short_token='.$other['short_token'];
        curl_setopt_array($curl, array(
            CURLOPT_URL => URL_PAYMENT.$action,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $content,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
            ),
        ));

        $response = curl_exec($curl);
        $this->mongo_db->insert('vne_log_call_partner',[
            'response'=>$response,
            'type'=>'LOG_CALL_PAYMENT',
            'data'=>[
                'url'=>URL_PAYMENT.$action
            ]
        ]);
        // die($response);

        curl_close($curl);
        return json_decode($response,true);
    }

}

