<?php

namespace app\admin\controller\project;

use app\common\controller\Backend;

/**
 * 项目管理
 *
 */
class Project extends Backend
{
    /**
     * Project模型对象
     * @var \app\admin\model\Project
     */
    protected $model = null;
    
    protected $preExcludeFields = ['id', 'createtime'];

    protected $quickSearchField = ['id'];

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\Project;
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}