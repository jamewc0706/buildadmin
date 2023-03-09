<?php

namespace app\admin\controller\project;

use app\common\controller\Backend;
use think\facade\Db;
use think\db\exception\PDOException;
use think\exception\ValidateException;
use Exception;

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
            ->paginate($limit);
        $project_group_list = [];
        $project_group = Db::table('project_group')->select()->toArray();
        $admin_info = Db::table('admin')->select()->toArray();
        $admin_info = array_column($admin_info,null,'id');
        foreach($project_group as $val){
            $project_group_list[$val['project_id']][] = $admin_info[$val['admin_id']]['nickname'];
        }

        foreach($res->items() as &$v){
            $group_leader_list = $project_group_list[$v['id']] ?? [];
            $v['group_leader_list'] = implode(',',$group_leader_list);
        }

        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
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

                $items = [];
                if(isset($data['group_leader']) && !empty($data['group_leader'])){
                    foreach($data['group_leader'] as $val){
                        if($val){
                            $items[] = [
                                'project_id' => $row['id'],
                                'admin_id' => $val,
                                'operator' => $this->auth->nickname
                            ];
                        }
                    }
                    if($items){
                    Db::table('project_group') -> where('project_id', $row['id']) -> delete();

                    //批量添加
                    Db::table('project_group')->insertAll($items);
                    }
                }
                Db::commit();
            } catch (ValidateException|PDOException|Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Update successful'));
            } else {
                $this->error(__('No rows updated'));
            }

        }

        $project_group_list = [];
        $project_group = Db::table('project_group')->where('project_id',$row['id'])->select()->toArray();
        foreach($project_group as $v){
            $project_group_list[$v['admin_id']] = $v['admin_id'];
        }

        $row['group_leader'] = $project_group_list;

        $this->success('', [
            'row' => $row
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

                if(isset($data['group_leader']) && !empty($data['group_leader'])){
                    $items = [];
                    foreach($data['group_leader'] as $val){
                        $items[] = [
                            'project_id' => $this->model->id,
                            'admin_id' => $val,
                            'operator' => $this->auth->nickname
                        ];
                    }
                    if($items){
                        //批量添加
                        Db::table('project_group')->insertAll($items);
                    }
                }

                Db::commit();
            } catch (ValidateException|PDOException|Exception $e) {
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
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */


    /**
     * 删除
     * @param array $ids
     */
    public function del(array $ids = [])
    {
        if (!$this->request->isDelete() || !$ids) {
            $this->error(__('Parameter error'));
        }

        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds) {
            $this->model->where($this->dataLimitField, 'in', $dataLimitAdminIds);
        }

        $pk    = $this->model->getPk();
        $data  = $this->model->where($pk, 'in', $ids)->select();
        $count = 0;
        Db::startTrans();
        try {
            foreach ($data as $v) {
                $count += $v->delete();
                Db::table('project_group') -> where('project_id', $v['id']) -> delete();
            }
            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success(__('Deleted successfully'));
        } else {
            $this->error(__('No rows were deleted'));
        }
    }
}