<?php


class Mtimekeeping extends MY_Model
{
    protected $collection = 'time_keeping';

    public function __construct()
    {
        parent::__construct();
    }

    public function find($cond)
    {
        return $this->mongo_db->where($cond)->get($this->collection);
    }

    public function updateTimeKeeping($staffId, $month, $amount)
    {
        $update = $this->mongo_db->set([
                    'amount' => $amount,
                    'updated_at' => new \MongoDB\BSON\UTCDateTime(time() * 1000)
        ])->where(['staff_id' => (int)$staffId, 'month' => $month])->update($this->collection);
        return $update;
    }

    public function insertTimeKeeping($staffId, $month)
    {
        $insert = $this->mongo_db->insert($this->collection, [
            'staff_id' => (int)$staffId,
            'month' => $month,
            'amount' => 1,
            'created_at' => new \MongoDB\BSON\UTCDateTime(time() * 1000)
        ]);
        return $insert;
    }
}