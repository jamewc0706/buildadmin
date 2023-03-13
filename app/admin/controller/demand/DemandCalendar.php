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
        $demand_contents = [];
        // {
        //     "day": '2023-03-07',
        //     "demand_content": [
        //         {
        //             "type": "info",
        //             "desc": '待开始',
        //             "demand": [
        //                 '需求1',
        //                 '需求2',
        //             ]
        //         },
        //         {
        //             "type": "warning",
        //             "desc": '进行中',
        //             "demand": [
        //                 '需求1',
        //                 '需求2',
        //             ]
        //         },
        //         {
        //             "type": "success",
        //             "desc": '已完成',
        //             "demand": [
        //                 '需求1',
        //                 '需求2',
        //             ]
        //         }, {
        //             "type": "danger",
        //             "desc": '已延期',
        //             "demand": [
        //                 '需求1',
        //                 '需求2',
        //             ]
        //         }
        //     ]
        // }
        $info = Db::name('person_demand_schedule')->where('producer_id',$this->auth->id)->select();
        $demand_info = Db::name('demand_record')->select()->toArray();
        $demand_info = array_column($demand_info,null,'id');
        $person_demand_info = Db::name('demand_person_record')->select()->toArray();
        $person_demand_info = array_column($person_demand_info,null,'demand_id');
        foreach ($info as $item) {
            $demand_content[$item['date']][] =  [
                'label' => isset($demand_info[$item['demand_id']]) ? $demand_info[$item['demand_id']]['demand_name'] . "-人天:{$item['cost']}" : '未知需求名',
                'status' => isset($person_demand_info[$item['demand_id']]) ? $person_demand_info[$item['demand_id']]['status'] : 0,
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