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

    // 获取项目以及员工下拉框
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

    // 获取日期下拉框
    public function getDateSelect()
    {
        $date_select_list = [];
        $id = $this->request->get('id');
        if (!$id) {
            $this->success('', [
                'date_select_list' => $date_select_list,
            ]);
        }

        $date_arr = $this->getDateRangeArr($id);
        $date_map = [
            0 => '星期天',
            1 => '星期一',
            2 => '星期二',
            3 => '星期三',
            4 => '星期四',
            5 => '星期五',
            6 => '星期六',
        ];

        if ($date_arr) {
            foreach ($date_arr as $item) {
                $date_select_list[$item] = $item . "-" . $date_map[date('w', strtotime($item))];
            }
        }

        $this->success('', [
            'date_select_list' => $date_select_list,
        ]);
    }

    public function getDateRangeArr($id){
        $date_arr = [];
        $demand_info = $this->model->where('id', $id)->find();
        if ($demand_info['production_start_date'] != '' && $demand_info['production_end_date'] != '') {
            $start_date = date('Y-m-d', strtotime($demand_info['production_start_date']));
            $end_date = date('Y-m-d', strtotime($demand_info['production_end_date']));
            $date_arr = $this->createDateRangeArray($start_date, $end_date);
        }
        return $date_arr;
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
            $count = Db::name('demand_person_record')->where('demand_id', $l['id'])->where('status', 0)->count();
            $l['project_name'] = isset($project_list[$l['id']]) ? $project_list[$l['id']]['name'] : "-";
            if ($count > 0) {
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
            if (isset($data['production_end_date']) && $data['production_end_date'] != '') {
                $production_end_date = strtotime($data['production_end_date']) + (60 * 60 * 24) - 1;
                $data['production_end_date'] = date('Y-m-d H:i:s', $production_end_date);
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

            // 组装参数
            $assembleData = $this->assembleData($data);

            // 校验参数
            $err_msg = $this->check($assembleData,$data);
            if (!empty($err_msg)) {
                $this->error($err_msg);
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
                $result = $model->save($assembleData);
                $json_ext = json_decode($assembleData['json_ext'],true) ?? [];
                $items = json_decode($json_ext['items'],true) ?? [];   
                //批量添加
                Db::table('person_demand_schedule')->insertAll($items);

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

    //组装
    public function assembleData($data)
    {
        $demand_id = $data['id'];
        $producer_id = $data['producer_id'];
        $date_list = $data['date_list'];
        $extra_content = trim($data['extra_content']) ?? '';
        $mark_arr = $info = $items = [];

        if (!empty($extra_content)) {
            // 中文逗号处理
            $extra_content = trim($extra_content);
            $arr = explode("\n", $extra_content);
            foreach($arr as $mark){
                if(!empty($mark)){
                    $item = explode('=',$mark);
                    $date = $item[0] ?? '';
                    $cost = $item[1] ?? 0;
                    $mark_arr[$date] = $cost;

                    if(!in_array($date,$date_list)){
                        $this->error(__("日期:{$date}不存在可分配日期内"));
                    }

                }
            }
        }

        if(count($mark_arr) > count($date_list)){
            $this->error(__('备注日期个数超出选择的分配日期的个数'));
        }

        foreach ($date_list as  $date) {
            //每天的工时默认是1
            $cost = $type = $actual_cost = 1;
            // 如果特殊标注存在 工时用特殊标注的
            if(isset($mark_arr[$date])){
                $cost = $mark_arr[$date];
                $actual_cost = $mark_arr[$date];

                //非加班情况下  不允许人天超1天
                if($cost > 1 && $cost != 2){
                    $this->error(__('非加班情况,分配的人天不能超过最大值'));
                }

                //加班
                if($cost == 2){
                    $type = 2;
                }
            }

            $item = [
                'demand_id' => $demand_id,
                'producer_id' => $producer_id,
                'date' => $date,
                'cost' => $cost,
                'actual_cost' => $actual_cost,
                'status' => 1,
                'type' => $type,
                'admin_id' => $this->auth->id,
                'operator' => $this->auth->username
            ];

            $items[] = $item;
        }

        $json_ext = json_encode([
            'date_list' => $date_list,
            'extra_content' => $extra_content,
            'items' => json_encode($items)
        ]);

        $info = [
            'demand_id' => $demand_id, //需求id
            'status' => 1, //状态
            'admin_id' => $this->auth->id, //添加人id
            'producer_id' => $producer_id, //制作人id
            'create_time' => time(),  //创建时间,
            'json_ext' => $json_ext
        ];

        return $info;
    }

    public function check($assembleData, $data)
    {
        if(empty($data['date_list'])){
            return  "排期时间不能为空";
        }

        if(empty($data['producer_id'])){
            return  "制作人不能为空";
        }

        if(empty($data['id'])){
            return "需求id不能为空";
        }

        $json_ext = json_decode($assembleData['json_ext'],true) ?? [];
        $items = json_decode($json_ext['items'],true) ?? [];
        $add_cost = $demand_all_cost = $person_demand_cost = 0;
        $demand_info = Db::name('demand_record')->where('id', $assembleData['demand_id'])->find();
        //检查
        foreach($items as $item){
            if($item['cost'] <= 0 ){
                return "日期:{$item['date']}-{$item['cost']} 工时分配错误";
            }

            //校验排期冲突
            $cost_info = $this->getUserPlanCostByDate($item['date'],$data['producer_id']);
            if ( ($cost_info['all_cost'] + $item['cost']) > 1 ) {
                return "日期:{$item['date']} 已超出个人当天可安排的人天";
            }

            // 校验时间范围
            $date_range = $this->getDateRangeArr($item['demand_id']);
            if(!in_array($item['date'],$date_range)){
                return "日期:{$item['date']} 不存在可分配范围内";
            }

            $add_cost += $item['actual_cost'];
        }

        $demand_cost_info = $this->getDemandCost($assembleData['demand_id']);
        if ( ($add_cost + $demand_cost_info['all_actual_cost']) > $demand_info['cost'] ) {
            return "已超出该需求{$demand_info['demand_name']}的总人天";
        }

        return  '';
    }

    //获取该日期下该制造人的人天
    public function getUserPlanCostByDate($date,$producer_id){
        $all_actual_cost = $all_cost = 0;
        $info = Db::name('person_demand_schedule')->where('date', $date)->where('producer_id',$producer_id)->select();
        if($info){
            foreach($info as $val){
                $cost = $val['cost'];
                $actual_cost = $val['actual_cost'];
                $all_cost += $cost;
                $all_actual_cost += $actual_cost;
            }
        }
        return [
            'all_actual_cost' => $all_actual_cost,
            'all_cost' => $all_cost
        ];
    }

    //获取该需求下的人天
    public function getDemandCost($demand_id){
        $all_actual_cost = $all_cost = 0;
        $info = Db::name('person_demand_schedule')->where('demand_id',$demand_id)->select();
        if($info){
            foreach($info as $val){
                $cost = $val['cost'];
                $actual_cost = $val['actual_cost'];
                $all_cost += $cost;
                $all_actual_cost += $actual_cost;
            }
        }
        return [
            'all_actual_cost' => $all_actual_cost,
            'all_cost' => $all_cost
        ];
    }
}
