<template>
    <!-- 对话框表单 -->
    <el-dialog class="ba-operate-dialog" :close-on-click-modal="false" :model-value="modalConfig.visible"
        @close="hideAssignModal" width="50%">
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ modalConfig.title }}
            </div>
        </template>
        <el-scrollbar v-loading="baTable.form.loading" class="ba-table-form-scrollbar">
            <div class="ba-operate-form" :class="'ba-' + baTable.form.operate + '-form'"
                :style="'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'">
                <el-form ref="formRef" @submit.prevent="" @keyup.enter="onAssignSubmit()" :model="baTable.form.items"
                    label-position="right" :label-width="baTable.form.labelWidth + 'px'" :rules="rules">
                    <FormItem :label="t('demand.demandRecord.producer_id')" type="select" v-model="assignData.producer_id"
                        prop="assignData.producer_id" :data="{ content: adminList }"
                        :placeholder="t('Please select field', { field: t('demand.demandRecord.producer_id') })" />
                    <FormItem :label="t('demand.demandRecord.person_cost')" type="string" v-model="assignData.person_cost"
                        prop="assignData.person_cost"
                        :placeholder="t('Please input field', { field: t('demand.demandRecord.person_cost') })" />
                    <FormItem :label="t('demand.demandRecord.production_start_date')" type="date"
                        v-model="assignData.production_start_date" prop="production_start_date"
                        :placeholder="t('Please select field', { field: t('demand.demandRecord.production_start_date') })" />
                    <FormItem :label="t('demand.demandRecord.production_end_date')" type="date"
                        v-model="assignData.production_end_date" prop="production_end_date"
                        :placeholder="t('Please select field', { field: t('demand.demandRecord.production_end_date') })" />
                </el-form>
            </div>
        </el-scrollbar>
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                <el-button @click="hideAssignModal">{{ t('Cancel') }}</el-button>
                <el-button v-blur :loading="baTable.form.submitLoading" @click="onAssignSubmit()" type="primary">
                    {{ t('Save') }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { reactive, ref, inject, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import type baTableClass from '/@/utils/baTable'
import FormItem from '/@/components/formItem/index.vue'
import type { ElForm, FormItemRule } from 'element-plus'
import { buildValidatorData } from '/@/utils/validate'
const formRef = ref<InstanceType<typeof ElForm>>()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()
interface Props {
    adminList: anyObj,
    assignData: anyObj,
    modalConfig: anyObj,
    hideAssignModal: Function,
    onAssignSubmit?: Function
}

const props = withDefaults(defineProps<Props>(), {
    adminList: () => {
        return {}
    },
    assignData: () => {
        return {
            id: '',
            person_cost: '',
            producer_id: '',
            production_end_date: '',
            production_start_date: ''
        }
    },
    modalConfig: () => {
        return {
            visible: false,
            title: '',
        }
    },
})

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    person_cost: [buildValidatorData({ name: 'required', title: t('demand.demandRecord.person_cost') })],
    producer_id: [buildValidatorData({ name: 'required', title: t('demand.demandRecord.producer_id') })],
    production_end_date: [buildValidatorData({ name: 'required', title: t('demand.demandRecord.production_end_date') })],
    production_start_date: [buildValidatorData({ name: 'required', title: t('demand.demandRecord.production_start_date') })],
})
</script>

<style scoped lang="scss"></style>
