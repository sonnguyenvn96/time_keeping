<?php

Class Ultility
{
    protected $lang;

    public function __construct($lang)
    {
        $this->lang=$lang;
    }

    public function getVerifyTypeName($type = '')
    {
        switch($type)
        {
            case 'email':
                return $this->lang->line('email');
            break;
            default:
                return $this->lang->line('mobile');
        }
    }

    public function getFileType($str = 'docx')
    {
        switch ($str) {
            case 'doc':
                return "word";
                break;
            case 'docx':
                return "word";
                break;
            case 'xls':
                return "excel";
                break;
            case 'xlsx':
                return "excel";
                break;
            case 'ppt':
                return "powerpoint";
                break;
            case 'pptx':
                return "powerpoint";
                break;
            case 'pdf':
                return "pdf";
                break;
            default:
                return "alt";
                break;
        }
    }

    public function getNameTypeDocumentMeeting($number = 1)
    {
        switch ($number) {
            case 1:
                return $this->lang->line('document_meeting');
                break;
            case 2:
                return $this->lang->line('meeting_report');
                break;
            case 3:
                return $this->lang->line('related_document');
                break;
            default:
                return "Khác";
                break;
        }
    }

    public function returnNameTypeVote($str)
    {
        switch ($str) {
            case 'approval':
                return $this->lang->line('approval');
                break;
            case 'disapproval':
                return $this->lang->line('disapproval');
                break;
            default:
                return $this->lang->line('disapproval');
                break;
        }
    }

    public function returnViewTypeElector($fullname, $sum_stocks, $stocksAmountCompany)
    {
        if($sum_stocks != 0 && $stocksAmountCompany != 0)
        {
            $percent = round(($sum_stocks / $stocksAmountCompany) * 100, 2);;
        }else{
            $percent = 0;
        }
        
        return '   - '.$fullname. ' đạt '.number_format($sum_stocks).' CP chiếm <span class="font-weight-bold text-success">'.$percent.'%</span><br>';
    }

    public function returnViewTypeVote($data_vote, $sum, $stocksAmountCompany)
    {
        if($sum != 0 && $stocksAmountCompany != 0)
        {
            $percent = round(($sum / $stocksAmountCompany) * 100, 2);
        }else{
            $percent = 0;
        }
        
        switch ($data_vote) {
            case 'approval':
                return 'Đồng ý: '.number_format($sum).' CP chiếm <span class="font-weight-bold text-success">'.$percent.'%</span><br>';
                break;
            case 'disapproval':
                return 'Phản đối: '.number_format($sum).' CP chiếm <span class="font-weight-bold text-danger">'.$percent.'%</span><br>';
                break;
            default:
                return 'Không ý kiến: '.number_format($sum).' CP chiếm <span class="font-weight-bold text-warning">'.$percent.'%</span>';
                break;
        }
    }

    public function returnViewMyVote($action = '')
    {
        switch ($action) {
            case 'approval':
                return $this->lang->line('your_voted').': <b><span class="font-weight-bold text-success">'.$this->lang->line('approval').'</span></b><br>';
                break;
            case 'disapproval':
                return $this->lang->line('your_voted').': <b><span class="font-weight-bold text-danger">'.$this->lang->line('disapproval').'</span></b><br>';
                break;
            case 'neutral':
                return $this->lang->line('your_voted').': <b><span class="font-weight-bold text-danger">'.$this->lang->line('neutral').'</span></b><br>';
                break;
            default:
                return '<span class="font-weight-bold">Cổ đông không chọn đáp án</span>';
                break;
        }
    }
                                                    
                                                    

    public function getNameShareHolderRoles($str = '')
    {
        switch ($str) {
            case 1 :
            case 2:
                return $this->lang->line('role_election');
                break;
            case 3:
                return $this->lang->line('role_secretary');
                break;
            default:
                return $this->lang->line('role_other');
                break;
        }
    }

    public function getCheckedAddFilter($filterData, $id){

        if(in_array($id, $filterData))
        {
            return 'checked=""';
        }
    }

    public function getStatusAddFilter($filterData, $id){

        if(in_array($id, $filterData))
        {
            return 0;
        }
        return 1;
    }

    public function getSelectedInput($name_select = '', $name = ' ')
    {
        if($name_select == $name)
            return 'checked=""';
    }


    private function time_tmp($times, $datetime) {

        $time = time() - $times;
        switch ($time) {
            case ($time == 0);
                $time = "Vừa mới đây";
                break;
            case ($time < 60);
                $time = 'khoảng '.$time . " giây trước";
                break;
            case ($time > 60 && $time < 3600);
                $time = 'khoảng '.ceil($time / 60) . " phút trước";
                break;
            case ($time > 3600 && $time < 3600 * 24);
                $time = 'khoảng '.ceil($time / 3600) . " giờ trước";
                break;
            case (ceil($time / (3600 * 24)) > 365);
                $time = $datetime;
                break;
            case ($time > 3600 * 24);
                $time = 'khoảng '.ceil($time / (3600 * 24)) . " ngày trước";
                break;
        }
        return $time;
    }

    public function bad_words($str) {
    	$chars = array('địt','Địt','ĐỊT','dit','đéo','Đéo','ĐÉO','lồn','Lồn','LỒN','lon','Buồi','buồi','buồi.','BUỒI','bUồi','buỒi','buồI','buoi','cặc','Cặc','CẶC','dái','Dái','DÁI','Cứt','cứt','CỨT','ỉa','Ỉa','đái','Đái','ỈA','vl','vkl','loz','lin','liz','vll','me');
      	foreach ($chars as $key => $arr)
    		$str = preg_replace( "/(^|\b)".$arr."(\b|!|\?|\.|,|$)/i", "***", $str ); 
    		//$str = wordwrap($str, 23, " ", true);
    	return $str;
    }
    public function cut_str($str,$len){
    	$arrstr = explode(" ",$str);
    	$detail = "";
        $return = "";
    	if(count($arrstr) <= $len)
    		$len = count($arrstr);
    	else
    		{
    		  $detail  = " ...";
    		}
    	for($i=0;$i<$len;$i++)
        {
            $return .= $arrstr[$i]." ";
        }
    		
    	return $return.'...';
    }
    
}