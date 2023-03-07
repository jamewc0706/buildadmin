<?php

namespace app\admin\controller\demand;

use app\common\controller\Backend;
use think\facade\Db;

/**
 * 个人需求管理
 *
 */
class DemandCalendar extends Backend
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

    public function getPersonDemand()
    {
        $calendar_data = [];
        $info = Db::name('person_demand_schedule')->where('producer_id',$this->auth->id)->select();
        $demand_info = Db::name('demand_record')->select()->toArray();
        $demand_info = array_column($demand_info,null,'id');
        foreach ($info as $item) {
            $demand_content[$item['date']][] =  [
                'label' => isset($demand_info[$item['demand_id']]) ? $demand_info[$item['demand_id']]['demand_name'] . "-人天:{$item['cost']}" : '未知需求名',
            ];
        }

        foreach($info as $val) {
            $calendar_data[] = [
                'day' => $val['date'],
                'demand_content' => $demand_content[$val['date']] ?? []
            ];
        }

        $this->success('', [
            'calendar_data' => $calendar_data,
        ]);
    }

    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}