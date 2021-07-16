<?php


class Mstaff extends MY_Model
{
    protected $collection = 'staffs';

    public function __construct()
    {
        parent::__construct();
    }

    public function find($cond)
    {
        return $this->mongo_db->where($cond)->get($this->collection);
    }
}
