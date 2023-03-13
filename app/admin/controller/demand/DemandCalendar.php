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
        $all_info = [];
        $info = Db::name('person_demand_schedule')->where('producer_id', $this->auth->id)->select();
        $demand_info = Db::name('demand_record')->select()->toArray();
        $demand_info = array_column($demand_info, null, 'id');
        $person_demand_info = Db::name('demand_person_record')->select()->toArray();
        $person_demand_info = array_column($person_demand_info, null, 'demand_id');

        foreach($info as $val){
            $all_info[$val['date']][] = $val;
        }
        
        foreach ($all_info as $date => $item) {
            $tag_1 = [
                'class' => 'mx-1 tag-1 center',
                "type" => "info",
                "desc" => '未开始',
                'demand' => []
            ];

            $tag_2 = [
                'class' => 'mx-1 tag-2 center',
                "type" => "info",
                "desc" => '进行中',
                'demand' => []
            ];

            $tag_3 = [
                'class' => 'mx-1 tag-3 center',
                "type" => "info",
                "desc" => '已完成',
                'demand' => []
            ];

            $tag_4 = [
                'class' => 'mx-1 tag-4 center',
                "type" => "info",
                "desc" => '请假',
                'demand' => []
            ];

            $tag_5 = [
                'class' => 'mx-1 tag-5 center',
                "type" => "info",
                "desc" => '暂停中',
                'demand' => []
            ];

            $tag_6 = [
                'class' => 'mx-1 tag-6 center',
                "type" => "info",
                "desc" => '已回收',
                'demand' => []
            ];
                        
            $tag_7 = [
                'class' => 'mx-1 tag-7 center',
                "type" => "info",
                "desc" => '延期',
                'demand' => []
            ];

            foreach($item as $v){
                $status = $person_demand_info[$v['demand_id']]['status'];

                if($status == 1){
                    $tag_1['demand'][] = $demand_info[$v['demand_id']]['demand_name'];
                }

                if($status == 2){
                    $tag_2['demand'][] = $demand_info[$v['demand_id']]['demand_name'];
                }

                if($status == 3){
                    $tag_3['demand'][] = $demand_info[$v['demand_id']]['demand_name'];
                }

                if($status == 4){
                    $tag_4['demand'][] = $demand_info[$v['demand_id']]['demand_name'];
                }

                if($status == 5){
                    $tag_5['demand'][] = $demand_info[$v['demand_id']]['demand_name'];
                }

                if($status == 6){
                    $tag_6['demand'][] = $demand_info[$v['demand_id']]['demand_name'];
                }

                if($status == 7){
                    $tag_7['demand'][] = $demand_info[$v['demand_id']]['demand_name'];
                }
            }

            $item = [
                'day' => $date,
            ];

            if(!empty($tag_1['demand'])){
                $item['demand_content'][] = $tag_1;
            }

            if(!empty($tag_2['demand'])){
                $item['demand_content'][] = $tag_2;
            }

            if(!empty($tag_3['demand'])){
                $item['demand_content'][] = $tag_3;
            }

            if(!empty($tag_4['demand'])){
                $item['demand_content'][] = $tag_4;
            }

            if(!empty($tag_5['demand'])){
                $item['demand_content'][] = $tag_5;
            }

            if(!empty($tag_6['demand'])){
                $item['demand_content'][] = $tag_6;
            }


            if(!empty($tag_7['demand'])){
                $item['demand_content'][] = $tag_7;
            }

            $calendar_data[] = $item;
        }

        $this->success('', [
            'calendar_data' => $calendar_data,
        ]);
    }

    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}
