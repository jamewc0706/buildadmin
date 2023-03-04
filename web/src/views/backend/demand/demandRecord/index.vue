<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('quick Search Placeholder', { fields: t('demand.demandRecord.quick Search Fields') })" />

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef" />

        <!-- 表单 -->
        <PopupForm :project-list="state.projectList" />

        <!-- 指派表单 -->
        <AssignPopupForm :assignData="state.assignData" :modalConfig="state.modalConfig" :admin-list="state.adminList"
            :onAssignSubmit="onAssignSubmit" :hideAssignModal="hideAssignModal" />
    </div>
</template>

<script setup lang="ts">
import { ref, provide, onMounted, reactive } from 'vue'
import baTableClass from '/@/utils/baTable'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { getSelect, assign } from '/@/api/backend/demand/demandRecord'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import AssignPopupForm from './assignPopupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { concat, cloneDeep } from 'lodash-es'

let optButtons: OptButton[] = [
    {
        render: 'tipButton',
        name: 'assign',
        title: 'assign',
        text: '',
        type: 'primary',
        icon: 'fa fa-send',
        class: 'table-row-edit',
        disabledTip: false,
        click: (row: TableRow) => {
            handleAssignModal(row)
        },
    },
]

optButtons = concat(optButtons, defaultOptButtons(['edit', 'delete']))
const state: {
    state: {}
    projectList: anyObj,
    adminList: anyObj,
    assignData: {
        id: string,
        person_cost: string,
        production_person: string
    },
    modalConfig: {
        visible: boolean,
        title: string,
        submitLoading: boolean
    },
    loading: boolean,
    assignRef: any
} = reactive({
    projectList: {},
    adminList: {},
    modalConfig: {
        visible: false, // 弹窗是否显示
        title: '', // 弹窗标题
        submitLoading: false
    },
    assignData: {
        id: '',
        person_cost: '',
        production_person: '',
        production_start_date: '',
        production_end_date: '',
    },
    loading: true
})

const { t } = useI18n()
const tableRef = ref()
const baTable = new baTableClass(
    new baTableApi('/admin/demand.demandRecord/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('demand.demandRecord.id'), prop: 'id', align: 'center', width: 100, operator: 'RANGE', sortable: 'custom' },
            { label: t('demand.demandRecord.project_id'), prop: 'project_name', width: 140, align: 'center', render: 'tags', operator: false, sortable: false, replaceValue: {} },
            {
                label: t('demand.demandRecord.link'), render: 'tag', prop: 'link', align: 'center', width: 70, operatorPlaceholder: t('Fuzzy query'), operator: '=', sortable: false, replaceValue: {
                    1: '界面',
                    2: '交互',
                    3: '拼接',
                    4: '动效',
                }
            },
            { label: t('demand.demandRecord.asset_name'), prop: 'asset_name', width: 90, align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('demand.demandRecord.demand_name'), prop: 'demand_name', width: 90, align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            {
                label: t('demand.demandRecord.status'), render: 'tag', prop: 'status', align: 'center', width: 70, operator: 'RANGE', sortable: false, replaceValue: {
                    0: '待开始',
                    1: '已分配',
                    2: '结束',
                }
            },
            { label: t('demand.demandRecord.send_bag_date'), prop: 'send_bag_date', align: 'center', operator: '=', sortable: 'custom', width: 160 },
            { label: t('demand.demandRecord.receive_bag_date'), prop: 'receive_bag_date', align: 'center', operator: '=', sortable: 'custom', width: 160 },
            { label: t('demand.demandRecord.production_start_date'), prop: 'production_start_date', align: 'center', operator: '=', sortable: 'custom', width: 160 },
            { label: t('demand.demandRecord.production_end_date'), prop: 'production_end_date', align: 'center', operator: '=', sortable: 'custom', width: 160 },
            { label: t('demand.demandRecord.cost'), prop: 'cost', align: 'center', width: 120, operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('demand.demandRecord.contact_person'), prop: 'contact_person', width: 120, align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('demand.demandRecord.create_time'), prop: 'create_time', align: 'center', render: 'datetime', operator: 'RANGE', sortable: 'custom', width: 160, timeFormat: 'yyyy-mm-dd hh:MM:ss' },
            { label: t('operate'), align: 'center', width: 160, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: { project_id: '' },
    }
)

// 获取下拉框信息
const getAllSelect = () => {
    getSelect()
        .then((res) => {
            state.projectList = res.data.project_list
            state.adminList = res.data.admin_list
            state.loading = false
        })
        .catch(() => {
            state.loading = false
        })
}

/** 关闭指派弹窗 */
const handleAssignModal = (row: TableRow) => {
    if (!row) return
    // 数据来自表格数据,未重新请求api,深克隆,不然可能会影响表格
    let rowClone = cloneDeep(row)
    state.modalConfig.visible = true
    state.modalConfig.title = '当前指派需求ID:' + rowClone.id
    state.assignData.id = rowClone.id
}

/** 打开指派弹窗 */
const hideAssignModal = () => {
    state.modalConfig.visible = false
    state.modalConfig.title = ''
    state.assignData = {
        id: '',
        person_cost: '',
        production_person: ''
    }
}

const onAssignSubmit = () => {
    console.log('指派需求参数', state.assignData)
    state.modalConfig.submitLoading = true;
    assign(state.assignData)
        .then((res) => {
            console.log('res', res);
            state.modalConfig.submitLoading = false
            hideAssignModal()
            baTable.mount()
            baTable.getIndex()?.then(() => {
                baTable.initSort()
                baTable.dragSort()
            })
        })
        .catch((err) => {
            console.log(err)
        })
        .finally(() => {
            state.modalConfig.submitLoading = false
        })
}

provide('baTable', baTable)

onMounted(() => {
    getAllSelect()
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
