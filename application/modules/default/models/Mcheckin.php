<?php


class Mcheckin extends MY_Model
{
    protected $collection = 'check_in';

    public function __construct()
    {
        parent::__construct();
    }

    public function find($cond)
    {
        return $this->mongo_db->where($cond)->get($this->collection);
    }

    public function insertCheckin(array $params)
    {
        $checkin = $this->mongo_db->insert($this->collection, [
            "staff_id" => (int)$params['staff_id'],
            "time_checkin" => new \MongoDB\BSON\UTCDateTime(strtotime($params['time_checkin'])*1000),
            "created" => new \MongoDB\BSON\UTCDateTime(time()*1000),
        ]);
        return $checkin;
    }

    public function updateCheckin($timeCheckin,$cond)
    {
        $update = $this->mongo_db->set([
            'time_checkin' => new \MongoDB\BSON\UTCDateTime(strtotime($timeCheckin)*1000),
            'updated_at' => new \MongoDB\BSON\UTCDateTime(time() * 1000)
        ])->where($cond)->update($this->collection);
        return $update;
    }

    public function findCheckin($staffId, $timeCheckin)
    {
        $pipeline = [
            [
                '$match' => [
                    'staff_id' => $staffId,
                    'time_checkin' => [
                        '$gt' => new \MongoDB\BSON\UTCDateTime(strtotime($timeCheckin . ' 00:00:00') * 1000),
                        '$lte' => new \MongoDB\BSON\UTCDateTime(strtotime($timeCheckin . ' 23:59:59') * 1000),
                    ],
                ],
            ],
        ];
        return $this->mongo_db->aggregate($this->collection, $pipeline);
    }
}

