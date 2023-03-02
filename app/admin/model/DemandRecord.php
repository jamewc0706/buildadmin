<?php

namespace app\admin\model;

use think\Model;

/**
 * DemandRecord
 */
class DemandRecord extends Model
{
    // 表名
    protected $name = 'demand_record';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;

}