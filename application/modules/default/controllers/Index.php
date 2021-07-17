<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Index extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->data['controller'] = 'index';
        $this->load->model('Mstaff');
        $this->load->model('Mcheckin');
        $this->load->model('Mtimekeeping');
    }

    public function index()
    {
        $this->load->view('temp/timekeeping/index', $this->data);
    }

    public function ajaxTimeKeeping()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('staff_id', 'Mã nhân viên', 'required',
            array(
                'required' => '%s không được bỏ trống',
            )
        );
        $this->form_validation->set_rules('checkin_date', 'Ngày làm việc', 'required',
            array(
                'required' => '%s không được bỏ trống',
            )
        );
        $this->form_validation->set_rules('checkin_time', 'Giờ bắt đầu làm việc', 'required',
            array(
                'required' => '%s không được bỏ trống',
            )
        );
        if (!$this->form_validation->run()) {
            $errors = $this->showErrorValidation();
            die(json_encode([
                'status' => 422,
                'content' => $errors[0],
                'data' => (object)[]
            ]));
        }
        $staff = $this->Mstaff->find(['staff_id' => $this->getParamInt('staff_id')]);
        if (count($staff) == 0){
            die(json_encode([
                'status' => 422,
                'content' => 'Mã nhân viên không tồn tại',
                'data' => (object)[]
            ]));
        }
        $staffId = $this->getParamInt('staff_id');
        $timeCheckin = $this->getParamString('checkin_time').' '.$this->getParamString('checkin_date');
//        die($timeCheckin);
        $findCheckin = $this->Mcheckin->findCheckin($staffId, $this->getParamString('checkin_date'));
//        die(json_encode($findCheckin));
        if (count($findCheckin) != 0){
            $checkIn = $this->Mcheckin->updateCheckin($timeCheckin, [
                    'staff_id' => $staffId,
                    'time_checkin' => [
                        '$gt' => new \MongoDB\BSON\UTCDateTime(strtotime($this->getParamString('checkin_date') . ' 00:00:00') * 1000),
                        '$lte' => new \MongoDB\BSON\UTCDateTime(strtotime($this->getParamString('checkin_date') . ' 23:59:59') * 1000),
                    ],
                ]
            );
            die(json_encode([
                'status'=>200,
                'content'=>'Thành công',
                'data'=>$checkIn
            ]));
        }
        $params = [
            'staff_id' => $staffId,
            'time_checkin' => $timeCheckin
        ];
        $checkIn = $this->Mcheckin->insertCheckin($params);
        $month = date('m-Y', strtotime($this->getParamString('checkin_date')));
        $find = $this->Mtimekeeping->find(['staff_id' => $staffId, 'month' => $month]);
        if (count($find) == 0){
            $timeKeeping = $this->Mtimekeeping->insertTimeKeeping($staffId, $month);
        }
        else $timeKeeping = $this->Mtimekeeping->updateTimeKeeping($staffId, $month, $find[0]['amount']+1);
        die(json_encode([
            'status'=>200,
            'content'=>'Thành công',
            'data'=>$timeKeeping
        ]));
    }
}
