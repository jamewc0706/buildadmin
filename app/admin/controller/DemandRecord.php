<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 需求管理
 *
 */
class DemandRecord extends Backend
{
    /**
     * DemandRecord模型对象
     * @var \app\admin\model\DemandRecord
     */
    protected $model = null;
    
    protected $preExcludeFields = ['id', 'create_time'];

    protected $quickSearchField = ['id'];

        /**
     * 开启数据限制
     */
    protected $dataLimit = 'allAuthAndOthers';

    /**
     * 数据限制字段，数据表内必须存在此字段
     */
    protected $dataLimitField = 'admin_id';

    /**
     * 数据限制开启时自动填充字段值为当前管理员id
     */
    protected $dataLimitFieldAutoFill = true;

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\DemandRecord;
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */

     public function getSelect(){
        $model = new \app\admin\model\Project;
        $project_list      = $model->order('id desc')->select()->toArray();
        $list        = [];
        foreach ($project_list as $item) {
            $list[$item['id']] = $item['name'];
        }

        $this->success('', [
            'project_list'          => $list,
        ]);
    }

    /**
     * 查看
     */
    public function index()
    {
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->param('select')) {
            $this->select();
        }
        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->field($this->indexField)
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->paginate($limit)
            ->toArray();
        $list = $res['data'];
        $total = $res['total'];
        $model = new \app\admin\model\Project;
        $project_list = $model->order('id desc')->select()->toArray();
        $project_list = array_column($project_list,null,'id');
        foreach($list as &$l)
        {
            $l['project_name'] = isset($project_list[$l['id']]) ? $project_list[$l['id']]['name']: "-";
        }

        $this->success('', [
            'list'   => $list,
            'total'  => $total,
            'remark' => get_route_remark(),
        ]);
    }
}