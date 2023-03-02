<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 项目组管理
 *
 */
class ProjectTeam extends Backend
{
    /**
     * ProjectTeam模型对象
     * @var \app\admin\model\ProjectTeam
     */
    protected $model = null;
    
    protected $preExcludeFields = ['id', 'createtime'];

    protected $quickSearchField = ['id'];

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\ProjectTeam;
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}