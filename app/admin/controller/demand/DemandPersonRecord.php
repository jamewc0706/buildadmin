<?php

namespace app\admin\controller\demand;

use app\common\controller\Backend;

/**
 * 个人需求管理
 *
 */
class DemandPersonRecord extends Backend
{
    /**
     * DemandPersonRecord模型对象
     * @var \app\admin\model\DemandPersonRecord
     */
    protected $model = null;
    
    protected $preExcludeFields = ['id', 'create_time'];

    protected $quickSearchField = ['id'];

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\DemandPersonRecord;
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
        array_push($where,[
            'demand_person_record.producer_id',
            '=',
            $this->auth->id
        ]);
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

        $this->success('', [
            'list'   => $list,
            'total'  => $total,
            'remark' => get_route_remark(),
        ]);
    }

    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}