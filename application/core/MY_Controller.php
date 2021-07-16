<?php if ( ! defined('BASEPATH')) exit('Vui lòng liên hệ nhatnv');
class MY_Controller extends CI_Controller
{
    public $_data; 
    public function __construct(){
        parent::__construct();
        $inputForm = $_REQUEST;
        array_walk_recursive($inputForm, function(&$inputForm) {
            $inputForm = trim(addslashes(strip_tags($inputForm)));
        });
        $_REQUEST = $inputForm;
        $this->load->helper(array('url','text', 'form', 'security', 'string'));
        $this->data['fview']        = new Ultility($this->lang);
        $this->data['baseUrl']	    = site_url();
        // $this->data['user_id'] = $this->getParamString('user_id');
        // $this->data['user_oid'] = $this->getParamString('user_oid');
        // $this->data['short_token'] = $this->getParamString('short_token');
    }

    private function checkIE()
    {
        @$ua = htmlentities(@$_SERVER['HTTP_USER_AGENT'], ENT_QUOTES, 'UTF-8');
        if (preg_match('~MSIE|Internet Explorer~i', $ua) || (strpos($ua, 'Trident/7.0') !== false && strpos($ua, 'rv:11.0') !== false && strpos($ua, 'Edge') !== false)) {
            redirect('ie-detect');
        } 
    }

    public function detect_number($number) {
        $regEx = '/^0(3\d{8}|7\d{8}|5\d{8}|8\d{8}|3\d{8}|9\d{8})$/';
        $match = preg_match($regEx, $number);

        $f_mobile = substr($number,0,2);
        if($f_mobile == '01')
        {
            $match = true;
        }
        return $match;
    }

    public function checkLogin()
    {
        if(in_array(@$this->session->userdata('isLogin'), array(null, '', '0')))
        {
            // die('123123');
            redirect('login');
        }
    }
	
    public function getParamStringAdmin($param)
    {
        return addslashes(isset($_REQUEST[$param]) ?  $this->str_equal(quotes_to_entities($_REQUEST[$param])) : '');
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
    
    public function generateCode($n){
        $n = rand(10e16, 10e20);
        return base_convert($n, 10, 36);      
    }
    
    public function getParamUrl($id)
    {
        return intval($this->security->xss_clean($this->uri->segment($id)));
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
        }else{
            return addslashes($this->security->xss_clean($this->uri->segment($id)));
        } 
        
    }

    public function multiRequest($data, $options = array()) {
 
        $curly = array();
        $result = array();
        $mh = curl_multi_init();

        foreach ($data as $id => $d) {

            $curly[$id] = curl_init();
            $url = (is_array($d) && !empty($d['url'])) ? $d['url'] : $d;
			curl_setopt($curly[$id], CURLOPT_URL, $url);
			curl_setopt($curly[$id], CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($curly[$id], CURLOPT_HTTPHEADER,         array(
				"Accept-Encoding: gzip, deflate",
				"Cache-Control: no-cache",
				"Connection: keep-alive",
				"Sec-Fetch-Mode: cors",
				"User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36",
				"accept: application/json",
				"cache-control: no-cache"
			));
			curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($curly[$id], CURLOPT_ENCODING, "");
			curl_setopt($curly[$id], CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			
			
            if (!empty($options)) {
                curl_setopt_array($curly[$id], $options);
            }
            curl_multi_add_handle($mh, $curly[$id]);
        }

        $running = null;
        do {
            curl_multi_exec($mh, $running);
        } while($running > 0);

        foreach($curly as $id => $c) {
            $result[$id] = curl_multi_getcontent($c);
            curl_multi_remove_handle($mh, $c);
        }
        curl_multi_close($mh);

        return $result;
    }

    public function showErrorValidation()
    {
        $stringError = validation_errors();
        preg_match_all('/<p>(.*?)<\/p>/', $stringError, $matches);
        return $matches[1];
    }
    
    public function validateMobile($mobile) {
        $regEx = '/^0+[3 5 8 7 9]+[0-9]{8}+$/mi';
        $match = preg_match($regEx, $mobile);
        return (bool)$match;
    }

    public function loanFirst()
    {

        $this->session->unset_userdata(['user_id','user_oid','short_token','user']);
        $this->session->set_userdata(['open-by-mobile'=>true]);
        if(!empty($this->getParamString('source'))) $this->session->set_userdata(['open-by-mobile'=>false]); //nếu mở trên web thì có source web , còn không thì nó là mở trên mobile
        $userId = $this->getParamInt('user_id');
        $shortToken = $this->getParamString('short_token');
        $userOid = $this->getParamString('user_oid');
        if (empty($userId) || empty($shortToken)){
            return [
              'status'=>204,
              'content'=>'Thông tin đăng nhập không đủ',
              'data'=>[
                  'controller'=>'error',
                  'action'=>'error_info',
                  'content'=>'Bạn phải sử dụng app Vinalife, hoặc đăng nhập trang CTV và sử dụng tính năng vay tiền để tham gia chương trình này',
                  'redirect_to'=>$this->getParamString('source') == "web_ctv" ? BASE_URL_CTV : ""
              ]
            ];
        };
        $user = $this->mongo_db->where([
                'user_id' => $userId,
                'short_token' => $shortToken,
                '_id'=>new \MongoDB\BSON\ObjectId($this->getParamString('user_oid'))
            ])->get('vne_users')[0] ?? null;

        if(is_null($user)){
            return [
                'status'=>204,
                'content'=>'Thông tin đăng nhập không đủ',
                'data'=>[
                    'controller'=>'error',
                    'action'=>'error_info',
                    'content'=>'Bạn phải sử dụng app Vinalife, hoặc đăng nhập trang CTV và sử dụng tính năng vay tiền để tham gia chương trình này',
                    'redirect_to'=>$this->getParamString('source') == "web_ctv" ? BASE_URL_CTV : ""
                ]
            ];
        }

        $status = 200;

        if(!isset($user['image_passport']->status) || $user['image_passport']->status!="SUCCESS"){
            $status = 204;
            $content = "Bạn vui lòng chờ CMT/CCCD/Hộ chiếu được phê duyệt để tiếp tục";
        }

        if(empty($user['image_passport']->passport_front) || empty($user['image_passport']->passport_behind)){
            $status = 204;
            $content = "Bạn phải sử dụng app Vinalife, hoặc đăng nhập trang CTV và cập nhật thông tin chứng minh thư/CCCD/Hộ chiếu để tham gia chương trình này";
        }

        if($status==204) return [
            'status'=>204,
            'content'=>'Thông tin đăng nhập không đủ',
            'data'=>[
                'controller'=>'error',
                'action'=>'error_info',
                'content'=>$content,
                'redirect_to'=>$this->getParamString('source') == "web_ctv" ? BASE_URL_CTV : ""
            ]
        ];

        $user = [
            'user_oid'=>$userOid,
            'user_id'=>$userId,
            'short_token'=>$shortToken,
            'image_passport'=>[
                'passport_front' => BASE_URL_IMAGE_GOOGLE_STORAGE.$user['image_passport']->passport_front,
                'passport_behind' => BASE_URL_IMAGE_GOOGLE_STORAGE.$user['image_passport']->passport_behind
            ],
            'passport'=>$user->passport ?? [],
            'fullname'=>$user->fullname ?? "",
            'mobile' => $user->mobile ?? "",
            'province_id'=>$user->province_id ?? "",
            'district_id'=>$user->district_id ?? "",
            'email'=>$user->email ?? "",
            'address'=>$user->address ?? ""
        ];

        $this->session->set_userdata([
           'user'=>$user,
        ]);

        $findLoan = $this->mongo_db->where([
            'user_id'=>$userId,
        ])->where_in('status',['NEW','APPROVED'])->limit(1)->get('vne_ocb_loans')[0] ?? null;
        
        if(!is_null($findLoan)) return [
            'status'=>302,
            'content'=>'Đang tồn tại khoản vay',
            'data'=>[
                'redirect_to'=>site_url('ocb-loan-status').'?user_oid='.$userOid
            ]
        ];

        return [
            'status'=>200,
            'content'=>'Validate thành công',
            'data'=>[]
        ];
    }

    public function response(int $status = 500,string $content = "Service Error",array $data=[]){
        return json_encode([
           'status'=>$status,
           'content'=>$content,
           'data'=>(object)$data
        ]);
    }
}