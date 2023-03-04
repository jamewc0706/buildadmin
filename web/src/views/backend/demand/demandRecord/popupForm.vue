<template>
    <!-- 对话框表单 -->
    <el-dialog
class="ba-operate-dialog" :close-on-click-modal="false"
        :model-value="['add', 'edit'].includes(baTable.form.operate!)" @close="baTable.toggleForm" width="50%">
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ baTable.form.operate ? t(baTable.form.operate) : '' }}
            </div>
        </template>
        <el-scrollbar v-loading="baTable.form.loading" class="ba-table-form-scrollbar">
            <div
class="ba-operate-form" :class="'ba-' + baTable.form.operate + '-form'"
                :style="'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'">
                <el-form
v-if="!baTable.form.loading" ref="formRef" @submit.prevent=""
                    @keyup.enter="baTable.onSubmit(formRef)" :model="baTable.form.items" label-position="right"
                    :label-width="baTable.form.labelWidth + 'px'" :rules="rules">
                    <FormItem
:label="t('demand.demandRecord.project_id')" type="select" v-model="baTable.form.items!.project_id"
                        prop="project_id" :data="{ content: projectList }"
                        :placeholder="t('Please select field', { field: t('demand.demandRecord.project_id') })" />
                    <FormItem
:label="t('demand.demandRecord.link')" type="select" v-model="baTable.form.items!.link" prop="link"
                        :data="{
                            content: {
                                1: '界面',
                                2: '交互',
                                3: '拼接',
                                4: '动效',
                            }
                        }" :placeholder="t('Please input field', { field: t('demand.demandRecord.link') })" />
                    <FormItem
:label="t('demand.demandRecord.asset_name')" type="string" v-model="baTable.form.items!.asset_name"
                        prop="asset_name" :placeholder="t('Please input field', { field: t('demand.demandRecord.asset_name') })" />
                    <FormItem
:label="t('demand.demandRecord.demand_name')" type="string" v-model="baTable.form.items!.demand_name"
                        prop="demand_name"
                        :placeholder="t('Please input field', { field: t('demand.demandRecord.demand_name') })" />
                    <FormItem
:label="t('demand.demandRecord.send_bag_date')" type="date"
                        v-model="baTable.form.items!.send_bag_date" prop="send_bag_date"
                        :placeholder="t('Please select field', { field: t('demand.demandRecord.send_bag_date') })" />
                    <FormItem
:label="t('demand.demandRecord.receive_bag_date')" type="date"
                        v-model="baTable.form.items!.receive_bag_date" prop="receive_bag_date"
                        :placeholder="t('Please select field', { field: t('demand.demandRecord.receive_bag_date') })" />
                    <FormItem
:label="t('demand.demandRecord.production_start_date')" type="date"
                        v-model="baTable.form.items!.production_start_date" prop="production_start_date"
                        :placeholder="t('Please select field', { field: t('demand.demandRecord.production_start_date') })" />
                    <FormItem
:label="t('demand.demandRecord.production_end_date')" type="date"
                        v-model="baTable.form.items!.production_end_date" prop="production_end_date"
                        :placeholder="t('Please select field', { field: t('demand.demandRecord.production_end_date') })" />
                    <FormItem
:label="t('demand.demandRecord.cost')" type="string" v-model="baTable.form.items!.cost" prop="cost"
                        :placeholder="t('Please input field', { field: t('demand.demandRecord.cost') })" />
                    <FormItem
:label="t('demand.demandRecord.contact_person')" type="string"
                        v-model="baTable.form.items!.contact_person" prop="contact_person"
                        :placeholder="t('Please input field', { field: t('demand.demandRecord.contact_person') })" />
                </el-form>
            </div>
        </el-scrollbar>
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                <el-button @click="baTable.toggleForm('')">{{ t('Cancel') }}</el-button>
                <el-button v-blur :loading="baTable.form.submitLoading" @click="baTable.onSubmit(formRef)" type="primary">
                    {{ baTable.form.operateIds && baTable.form.operateIds.length > 1 ? t('Save and edit next item') : t('Save') }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { reactive, ref, inject } from 'vue'
import { useI18n } from 'vue-i18n'
import type baTableClass from '/@/utils/baTable'
import FormItem from '/@/components/formItem/index.vue'
import type { ElForm, FormItemRule } from 'element-plus'
import { buildValidatorData } from '/@/utils/validate'

const formRef = ref<InstanceType<typeof ElForm>>()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

interface Props {
    projectList: anyObj
}

const props = withDefaults(defineProps<Props>(), {
    projectList: () => {
        return {}
    },
})

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    project_id: [buildValidatorData({ name: 'required', title: t('demand.demandRecord.project_id') })],
    link: [buildValidatorData({ name: 'required', title: t('demand.demandRecord.link') })],
    send_bag_date: [buildValidatorData({ name: 'date', title: t('demand.demandRecord.send_bag_date') })],
    receive_bag_date: [buildValidatorData({ name: 'date', title: t('demand.demandRecord.receive_bag_date') })],
    production_start_date: [buildValidatorData({ name: 'date', title: t('demand.demandRecord.production_start_date') })],
    production_end_date: [buildValidatorData({ name: 'date', title: t('demand.demandRecord.production_end_date') })],
    create_time: [buildValidatorData({ name: 'date', title: t('demand.demandRecord.create_time') })],
})
</script>

<style scoped lang="scss"></style>
