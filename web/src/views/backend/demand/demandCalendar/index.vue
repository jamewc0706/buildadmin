<template>
    <el-calendar>
        <template #date-cell="{ data }">
            <el-row :class="data.isSelected ? 'is-selected' : 'sds'">
                {{ data.day.split('-').slice(1).join('-') }}
                <!-- {{ data.isSelected ? '✔️' : '' }} -->
            </el-row>
            <div v-for="(item, index) in textContent(data.day)" :key="index">
                <e-row>
                    <el-col class="center">
                        <el-row>
                            <el-col v-for="(val, idx) in item.demand_content" :key="idx">
                                <el-tag :key="idx" :type="val.type" class="mx-1 tag-w center" effect="dark">
                                    {{ val.desc }}
                                </el-tag>
                                <el-ul v-for="(value, tag) in val.demand" class="text">
                                    <el-li>
                                        {{ value }}
                                    </el-li>
                                </el-ul>
                            </el-col>
                        </el-row>
                    </el-col>
                </e-row>
            </div>
        </template>
    </el-calendar>
</template>
  
<script setup lang="ts">
import { reactive, onMounted } from "vue";
import { getPersonDemand } from '/@/api/backend/demand/demand'

const state = reactive({
    loading: true,
    //测试数据
    calendarData: [
        {
            "day": '2023-03-07',
            "demand_content": [
                {
                    "type": "info",
                    "desc": '待开始',
                    "demand": [
                        '需求1',
                        '需求2',
                    ]
                },
                {
                    "type": "",
                    "desc": '进行中',
                    "demand": [
                        '需求1',
                        '需求2',
                    ]
                },
                {
                    "type": "success",
                    "desc": '已完成',
                    "demand": [
                        '需求1',
                        '需求2',
                    ]
                }, {
                    "type": "danger",
                    "desc": '已延期',
                    "demand": [
                        '需求1',
                        '需求2',
                    ]
                }
            ]
        }
    ],
});

// 获取下拉框信息
const getIndex = () => {
    getPersonDemand()
        .then((res) => {
            // state.calendarData = res.data.calendar_data
            state.loading = false
        })
        .catch(() => {
            state.loading = false
        })
}

//处理日期获取后台数据动态渲染上去
const textContent = (date: any) => {
    //当前date是拿到上面日历组件当前的日期值 根据该值去筛选测试数据找到对应各个日期下对应的数据return出去
    console.log(date, 1111);
    return state.calendarData.filter((item) => {
        return date === item.day;
    });
};

onMounted(() => {
    getIndex()
})

</script>
  
  
<style scoped >
:deep .el-calendar__body {
    padding: 4px 20px 35px;
}

:deep .el-calendar-table thead th {
    color: #6411ff;
    font-weight: bold;
    font-size: 18px;
}


:deep .el-calendar-table .el-calendar-day:hover {
    background-color: #ebd8fa;
}

:deep .el-calendar {
    --el-calendar-selected-bg-color: #d9d8fa;
}

:deep .el-calendar {
    --el-calendar-cell-width: 200px;
}

:deep .el-calendar-table .el-calendar-day {
    box-sizing: border-box;
    padding: 5px;
    height: 100%;
    color: #000;
    width: auto;
}

.center {
    display: flex;
    justify-content: center;
    align-items: center;
}

:deep .el-calendar__header {
    justify-content: center;
}

.tag-w {
    width: 80px;
    margin-bottom: 5px;
    margin-top: 5px;
    justify-content: center;
    align-items: center;
}

.text {
    padding: 2px 0px 2px 10px;
    align-items: center;
    display: flex;
}
</style>
  
  