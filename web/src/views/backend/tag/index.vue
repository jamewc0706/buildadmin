<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader :buttons="['refresh', 'add']"
            :quick-search-placeholder="t('quick Search Placeholder', { fields: t('tag.tag.quick Search Fields') })" />

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef" />

        <!-- 表单 -->
        <PopupForm :adminList="state.adminList" />
    </div>
</template>

<script setup lang="ts">
import { ref, provide, onMounted, reactive } from 'vue'
import baTableClass from '/@/utils/baTable'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { getSelect } from '/@/api/backend/tag'

const { t } = useI18n()
const tableRef = ref()
const optButtons = defaultOptButtons(['edit'])
const baTable = new baTableClass(
    new baTableApi('/admin/tag.tag/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('tag.tag.id'), prop: 'id', align: 'center', width: 100, operator: '=', sortable: 'custom' },
            { label: t('tag.tag.admin_name'), prop: 'admin_name', width: 120, align: 'center', operator: '=', sortable: false },
            {
                label: t('tag.tag.tag'), width: 120, prop: 'tag', align: 'center', render: 'tag', operator: '=', sortable: 'custom', replaceValue: {
                    1: '外延固定',
                    2: '外延灵活',
                    3: '非外延灵活',
                }
            },
            { label: t('operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: {},
    }
)

const state: {
    adminList: anyObj,
    loading: boolean
} = reactive({
    adminList: {},
    loading: true
})

// 获取下拉框信息
const getAllSelect = () => {
    getSelect()
        .then((res) => {
            state.adminList = res.data.admin_list || {}
            state.loading = false
        })
        .catch(() => {
            state.loading = false
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
    name: 'tag',
})
</script>

<style scoped lang="scss"></style>
