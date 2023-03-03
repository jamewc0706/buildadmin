<?php

namespace app\admin\controller;

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
            $l['project_name'] = isset($project_list[$l['id']]) ? $project_list[$l['id']]['name'] : "-";
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

            //  添加余下人天
            if (isset($data['cost'])) {
                $data['balance'] = $data['cost'];
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

            //  编辑余下人天
            if (isset($data['cost'])) {
                $data['balance'] = $data['cost'];
            }

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
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }
                            $this->success(__('Added successfully'));

            // $data = $this->excludeFields($data);
            // if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            //     $data[$this->dataLimitField] = $this->auth->id;
            // }

            // //  添加余下人天
            // if (isset($data['cost'])) {
            //     $data['balance'] = $data['cost'];
            // }

            // $result = false;
            // Db::startTrans();
            // try {
            //     // 模型验证
            //     if ($this->modelValidate) {
            //         $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
            //         if (class_exists($validate)) {
            //             $validate = new $validate;
            //             if ($this->modelSceneValidate) $validate->scene('add');
            //             $validate->check($data);
            //         }
            //     }
            //     $result = $this->model->save($data);
            //     Db::commit();
            // } catch (ValidateException | PDOException | Exception $e) {
            //     Db::rollback();
            //     $this->error($e->getMessage());
            // }
            // if ($result !== false) {
            //     $this->success(__('Added successfully'));
            // } else {
            //     $this->error(__('No rows were added'));
            // }
        }

        $this->error(__('Parameter error'));
    }

}
