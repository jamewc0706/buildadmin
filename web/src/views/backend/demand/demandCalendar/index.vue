<template>
    <el-calendar>
        <template #date-cell="{ data }">
            <el-row :class="data.isSelected ? 'is-selected' : 'sds'">
                {{ data.day.split('-').slice(1).join('-') }}
            </el-row>
            <div v-for="(item, index) in textContent(data.day)" :key="index">
                <e-row>
                    <el-col class="center">
                        <el-row>
                            <el-col v-for="(val, idx) in item.demand_content" :key="idx">
                                <el-tag :key="idx" :type="val.type" :class="val.class" effect="dark">
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
    ],
    value: []
});

// 获取下拉框信息
const getIndex = () => {
    getPersonDemand()
        .then((res) => {
            state.calendarData = res.data.calendar_data
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

.tag-1 {
    width: 80px;
    margin-bottom: 5px;
    margin-top: 5px;
    justify-content: center;
    align-items: center;
    background-color: #52aeef;
    border-color: #52aeef;
}

.tag-2 {
    width: 80px;
    margin-bottom: 5px;
    margin-top: 5px;
    justify-content: center;
    align-items: center;
    background-color: #fa8002;
    border-color: #fa8002;
}

.tag-3 {
    width: 80px;
    margin-bottom: 5px;
    margin-top: 5px;
    justify-content: center;
    align-items: center;
    background-color: #32cd33;
    border-color: #32cd33;
}

.tag-4 {
    width: 80px;
    margin-bottom: 5px;
    margin-top: 5px;
    justify-content: center;
    align-items: center;
    background-color: #da70d5;
    border-color: #da70d5;
}

.tag-5 {
    width: 80px;
    margin-bottom: 5px;
    margin-top: 5px;
    justify-content: center;
    align-items: center;
    background-color: #818a87;
    border-color: #818a87;
}

.tag-6 {
    width: 80px;
    margin-bottom: 5px;
    margin-top: 5px;
    justify-content: center;
    align-items: center;
    background-color: #6b5acd;
    border-color: #6b5acd;
}

.tag-7 {
    width: 80px;
    margin-bottom: 5px;
    margin-top: 5px;
    justify-content: center;
    align-items: center;
    background-color: #fe0000;
    border-color: #fe0000;
}

.text {
    padding: 2px 0px 2px 10px;
    align-items: center;
    display: flex;
}
</style>
  
  