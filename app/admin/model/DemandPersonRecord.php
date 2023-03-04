<?php

namespace app\admin\model;

use think\Model;

/**
 * DemandPersonRecord
 */
class DemandPersonRecord extends Model
{
    // 表名
    protected $name = 'demand_person_record';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;

}