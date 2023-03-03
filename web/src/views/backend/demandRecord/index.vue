<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('quick Search Placeholder', { fields: t('demandRecord.quick Search Fields') })"
        />

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef" />

        <!-- 表单 -->
        <PopupForm :project-list="state.projectList"/>
    </div>
</template>

<script setup lang="ts">
import { ref, provide, onMounted, reactive } from 'vue'
import baTableClass from '/@/utils/baTable'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { getSelect } from '/@/api/backend/demandRecord/config'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'

const state: {
    projectList: anyObj,
    loading: boolean
} = reactive({
    projectList: {},
    loading: true
})

const getConfig = () => {
    getSelect()
        .then((res) => {
            state.projectList = res.data.project_list
            state.loading = false
        })
        .catch(() => {
            state.loading = false
        })
}

const { t } = useI18n()
const tableRef = ref()
const optButtons = defaultOptButtons(['edit', 'delete'])
const baTable = new baTableClass(
    new baTableApi('/admin/DemandRecord/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('demandRecord.id'), prop: 'id', align: 'center', width: 70, operator: 'RANGE', sortable: 'custom' },
            { label: t('demandRecord.project_id'), prop: 'project_name',width: 140 , align: 'center', render: 'tags', operator: false, sortable: false, replaceValue: {} },
            { label: t('demandRecord.link'), render:'tag', prop: 'link', align: 'center', width: 70, operatorPlaceholder: t('Fuzzy query'), operator: '=', sortable: false ,replaceValue : {
                1: '界面',
                2: '交互',
                3: '拼接',
                4: '动效',
            }},
            { label: t('demandRecord.asset_name'), prop: 'asset_name', width: 90, align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('demandRecord.demand_name'), prop: 'demand_name',width: 90, align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('demandRecord.status'), render:'tag',prop: 'status', align: 'center', width: 70, operator: 'RANGE', sortable: false, replaceValue : {
                0: '待开始',
                1: '进行中',
                2: '结束',
            } },
            { label: t('demandRecord.send_bag_date'), prop: 'send_bag_date', align: 'center', operator: '=', sortable: 'custom', width: 160 },
            { label: t('demandRecord.receive_bag_date'), prop: 'receive_bag_date', align: 'center', operator: '=', sortable: 'custom', width: 160 },
            { label: t('demandRecord.production_start_date'), prop: 'production_start_date', align: 'center', operator: '=', sortable: 'custom', width: 160 },
            { label: t('demandRecord.production_end_date'), prop: 'production_end_date', align: 'center', operator: '=', sortable: 'custom', width: 160 },
            { label: t('demandRecord.cost'), prop: 'cost', align: 'center',width: 120, operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('demandRecord.contact_person'), prop: 'contact_person', width: 120, align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('demandRecord.create_time'), prop: 'create_time',width: 120, align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom', width: 160, timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            { label: t('operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: { project_id: '' },
    }
)

provide('baTable', baTable)

onMounted(() => {
    getConfig()
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getIndex()?.then(() => {
        baTable.initSort()
        baTable.dragSort()
    })
})
</script>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
    name: 'demandRecord',
})
</script>

<style scoped lang="scss"></style>
