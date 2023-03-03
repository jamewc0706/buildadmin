<?php

namespace app\admin\model;

use think\Model;

/**
 * Project
 */
class Project extends Model
{
    // 表名
    protected $name = 'project';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'createtime';
}