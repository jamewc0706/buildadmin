<?php

namespace app\admin\controller\demand;

use think\facade\Db;
use app\common\controller\Backend;
use think\db\exception\PDOException;
use think\exception\ValidateException;
use Exception;

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

    public function getSelect()
    {
        $model = new \app\admin\model\Project;
        $project_info = $model->order('id desc')->select()->toArray();
        $project_list = $admin_list = [];
        foreach ($project_info as $item) {
            $project_list[$item['id']] = $item['name'];
        }

        $model = new \app\admin\model\Admin();
        $admin_info = $model->order('id desc')->select()->toArray();
        foreach ($admin_info as $item) {
            $admin_list[$item['id']] = $item['nickname'];
        }

        $this->success('', [
            'project_list' => $project_list,
            'admin_list' => $admin_list,
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
        $project_list = array_column($project_list, null, 'id');
        foreach ($list as &$l) {
            $count = Db::name('demand_person_record')->where('project_id',$l['id'])->where('status',0)->count();
            $l['project_name'] = isset($project_list[$l['id']]) ? $project_list[$l['id']]['name'] : "-";
            if($count > 0){
                $l['status'] = 1;
            }
        }

        $this->success('', [
            'list'   => $list,
            'total'  => $total,
            'remark' => get_route_remark(),
        ]);
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data = $this->excludeFields($data);
            if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                $data[$this->dataLimitField] = $this->auth->id;
            }
            //处理制造结束时间
            if(isset($data['production_end_date']) && $data['production_end_date']!=''){
                $production_end_date = strtotime($data['production_end_date']) + (60 * 60 * 24) - 1;
                $data['production_end_date'] = date('Y-m-d H:i:s',$production_end_date);
            }

            $result = false;
            Db::startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate;
                        if ($this->modelSceneValidate) $validate->scene('add');
                        $validate->check($data);
                    }
                }
                $result = $this->model->save($data);
                Db::commit();
            } catch (ValidateException | PDOException | Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Added successfully'));
            } else {
                $this->error(__('No rows were added'));
            }
        }

        $this->error(__('Parameter error'));
    }

    /**
     * 编辑
     */
    public function edit()
    {
        $id  = $this->request->param($this->model->getPk());
        $row = $this->model->find($id);
        if (!$row) {
            $this->error(__('Record not found'));
        }

        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds && !in_array($row[$this->dataLimitField], $dataLimitAdminIds)) {
            $this->error(__('You have no permission'));
        }

        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data   = $this->excludeFields($data);
            $result = false;

            Db::startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate;
                        if ($this->modelSceneValidate) $validate->scene('edit');
                        $validate->check($data);
                    }
                }
                $result = $row->save($data);
                Db::commit();
            } catch (ValidateException | PDOException | Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Update successful'));
            } else {
                $this->error(__('No rows updated'));
            }
        }

        $this->success('', [
            'row' => $row
        ]);
    }

        /**
     * 指派
     */
    public function assign()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $model = new \app\admin\model\DemandPersonRecord;
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }
            $insertData = [
                $this->dataLimitField => $this->auth->id,
                'project_id' => $data['id'],
                'production_end_date' => $data['production_end_date']." 23:59:59",
                'production_start_date' => $data['production_start_date']. " 00:00:00",
                'producer_id' => $data['producer_id'],
                'cost' => $data['person_cost'],
                'status' => 0
            ];

            $err_msg = $this->check($insertData);
            if(!empty($err_msg)){
                $this->error($err_msg);
            }

            $insertData[$this->dataLimitField] = $this->auth->id;
            $result = false;
            Db::startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate;
                        if ($this->modelSceneValidate) $validate->scene('add');
                        $validate->check($data);
                    }
                }
                $result = $model->save($insertData);
                Db::commit();
            } catch (ValidateException | PDOException | Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Added successfully'));
            } else {
                $this->error(__('No rows were added'));
            }
        }

        $this->error(__('Parameter error'));
    }

    public function check($data){
        $err_msg = "";
        $person_total_cost = Db::name('demand_person_record')->where('project_id',$data['project_id'])->where('status',0)->sum('cost');
        $remand_info = Db::name('demand_record')->where('id',$data['project_id'])->find();
        $person_total_cost += $data['cost']; //全部人的人天
        $total_cost = $remand_info['cost'];  //需求的人天
        $person_start_date = strtotime($data['production_start_date']); //个人指派开始时间
        $person_end_date = strtotime($data['production_end_date']);  //个人指派结束时间
        $production_start_date = strtotime($remand_info['production_start_date']);
        $production_end_date = strtotime($remand_info['production_end_date']);

        //未分配时间
        if($production_start_date == '' || $production_end_date == ''){
            return "指派失败!超过该需求未分配时间";
        }

        //未分配人天
        if($total_cost == 0){
            return "指派失败!超过该需求未分配人天";
        }

        // 超过限制的人天
        if ($person_total_cost > $total_cost){
            return "指派失败!超过该需求人天[{$total_cost}人天]";
        }
        // var_dump(date('Y-m-d H:i:s',$person_start_date));
        // var_dump(date('Y-m-d H:i:s',$person_end_date));
        // var_dump(date('Y-m-d H:i:s',$production_start_date));
        // var_dump(date('Y-m-d H:i:s',$production_end_date));
        // var_dump($person_start_date < $production_start_date);
        // var_dump($person_start_date > $production_end_date);
        // var_dump($person_end_date < $production_start_date);
        // var_dump($person_end_date > $production_end_date);
        //判断是否在需求范围内
        if($person_start_date < $production_start_date || $person_start_date > $production_end_date || $person_end_date < $production_start_date || $person_end_date > $production_end_date){
            return "指派失败!指派时间应该在{$remand_info['production_start_date']}-{$remand_info['production_end_date']}内";
        }

        $day = ceil(($person_end_date - $person_start_date) / (60 * 60 * 24)); 

        if($data['cost'] > $day){
            return "指派失败!指派人天:{$data['cost']},指派时间范围换算人天:{$day}";
        }

        return  '';
    }
}
