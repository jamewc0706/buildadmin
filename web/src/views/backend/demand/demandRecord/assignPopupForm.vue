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
                <el-form ref="formRef" @submit.prevent="" :model="assignData"
                    label-position="right" :label-width="baTable.form.labelWidth + 'px'" :rules="rules">
                    <FormItem :label="t('demand.demandRecord.producer_id')" type="select" v-model="assignData.producer_id"
                        prop="producer_id" :data="{ content: adminList }"
                        :placeholder="t('Please select field', { field: t('demand.demandRecord.producer_id') })" />
                    <FormItem :label="t('demand.demandRecord.date_list')" type="selects" prop="date_list"
                        v-model="assignData.date_list" :data="{
                            content: dateSelectList
                        }" />
                    <FormItem :label="t('demand.demandRecord.extra_content')" prop="extra_content" type="textarea"
                        :input-attr="{ rows: 6 }" v-model="assignData.extra_content" />
                </el-form>
            </div>
        </el-scrollbar>
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                <el-button @click="hideAssignModal">{{ t('Cancel') }}</el-button>
                <el-button v-blur :loading="baTable.form.submitLoading" @click="onAssignSubmit(formRef)" type="primary">
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
    dateSelectList: anyObj,
    hideAssignModal: Function,
    onAssignSubmit?: Function
}

const props = withDefaults(defineProps<Props>(), {
    adminList: () => {
        return {}
    },
    dateSelectList: () => {
        return {}
    },
    assignData: () => {
        return {
            id: '',
            producer_id: '',
            date_list: [],
            extra_content: ""
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
    producer_id: [buildValidatorData({ name: 'required', title: t('demand.demandRecord.producer_id') })],
    date_list: [buildValidatorData({ name: 'required', title: t('demand.demandRecord.date_list') })],
})
</script>

<style scoped lang="scss"></style>
