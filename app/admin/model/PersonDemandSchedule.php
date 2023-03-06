<?php

namespace app\admin\model;

use think\Model;

/**
 * DemandPersonRecord
 */
class PersonDemandSchedule extends Model
{
    // 表名
    protected $name = 'person_demand_schedule';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;

}