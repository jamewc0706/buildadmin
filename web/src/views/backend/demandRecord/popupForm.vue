<template>
    <!-- 对话框表单 -->
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="['add', 'edit'].includes(baTable.form.operate!)"
        @close="baTable.toggleForm"
        width="50%"
    >
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ baTable.form.operate ? t(baTable.form.operate) : '' }}
            </div>
        </template>
        <el-scrollbar v-loading="baTable.form.loading" class="ba-table-form-scrollbar">
            <div
                class="ba-operate-form"
                :class="'ba-' + baTable.form.operate + '-form'"
                :style="'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'"
            >
                <el-form
                    v-if="!baTable.form.loading"
                    ref="formRef"
                    @submit.prevent=""
                    @keyup.enter="baTable.onSubmit(formRef)"
                    :model="baTable.form.items"
                    label-position="right"
                    :label-width="baTable.form.labelWidth + 'px'"
                    :rules="rules"
                >
                    <FormItem :label="t('demandRecord.project_id')" type="select" v-model="baTable.form.items!.project_id" prop="project_id" :data="{ content: {} }" :placeholder="t('Please select field', { field: t('demandRecord.project_id') })" />
                    <FormItem :label="t('demandRecord.link')" type="string" v-model="baTable.form.items!.link" prop="link" :placeholder="t('Please input field', { field: t('demandRecord.link') })" />
                    <FormItem :label="t('demandRecord.asset_name')" type="string" v-model="baTable.form.items!.asset_name" prop="asset_name" :placeholder="t('Please input field', { field: t('demandRecord.asset_name') })" />
                    <FormItem :label="t('demandRecord.demand_name')" type="string" v-model="baTable.form.items!.demand_name" prop="demand_name" :placeholder="t('Please input field', { field: t('demandRecord.demand_name') })" />
                    <FormItem :label="t('demandRecord.status')" type="number" prop="status" :input-attr="{ step: 1 }" v-model.number="baTable.form.items!.status" :placeholder="t('Please input field', { field: t('demandRecord.status') })" />
                    <FormItem :label="t('demandRecord.send_bag_date')" type="datetime" v-model="baTable.form.items!.send_bag_date" prop="send_bag_date" :placeholder="t('Please select field', { field: t('demandRecord.send_bag_date') })" />
                    <FormItem :label="t('demandRecord.receive_bag_date')" type="datetime" v-model="baTable.form.items!.receive_bag_date" prop="receive_bag_date" :placeholder="t('Please select field', { field: t('demandRecord.receive_bag_date') })" />
                    <FormItem :label="t('demandRecord.production_start_date')" type="datetime" v-model="baTable.form.items!.production_start_date" prop="production_start_date" :placeholder="t('Please select field', { field: t('demandRecord.production_start_date') })" />
                    <FormItem :label="t('demandRecord.production_end_date')" type="datetime" v-model="baTable.form.items!.production_end_date" prop="production_end_date" :placeholder="t('Please select field', { field: t('demandRecord.production_end_date') })" />
                    <FormItem :label="t('demandRecord.estimatd_cost')" type="string" v-model="baTable.form.items!.estimatd_cost" prop="estimatd_cost" :placeholder="t('Please input field', { field: t('demandRecord.estimatd_cost') })" />
                    <FormItem :label="t('demandRecord.production_person')" type="string" v-model="baTable.form.items!.production_person" prop="production_person" :placeholder="t('Please input field', { field: t('demandRecord.production_person') })" />
                    <FormItem :label="t('demandRecord.contact_persion')" type="string" v-model="baTable.form.items!.contact_persion" prop="contact_persion" :placeholder="t('Please input field', { field: t('demandRecord.contact_persion') })" />
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

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    project_id: [buildValidatorData({ name: 'required', title: t('demandRecord.project_id') })],
    status: [buildValidatorData({ name: 'number', title: t('demandRecord.status') })],
    send_bag_date: [buildValidatorData({ name: 'date', title: t('demandRecord.send_bag_date') })],
    receive_bag_date: [buildValidatorData({ name: 'date', title: t('demandRecord.receive_bag_date') })],
    production_start_date: [buildValidatorData({ name: 'date', title: t('demandRecord.production_start_date') })],
    production_end_date: [buildValidatorData({ name: 'date', title: t('demandRecord.production_end_date') })],
    create_time: [buildValidatorData({ name: 'date', title: t('demandRecord.create_time') })],
})
</script>

<style scoped lang="scss"></style>
