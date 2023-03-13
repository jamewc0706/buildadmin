<template>
    <!-- 对话框表单 -->
    <el-dialog class="abow_dialog" :close-on-click-modal="false"
        :model-value="['add', 'edit'].includes(baTable.form.operate!)" @close="baTable.toggleForm" width="30%">
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ baTable.form.operate ? t(baTable.form.operate) : '' }}
            </div>
        </template>
        <el-scrollbar v-loading="baTable.form.loading" class="ba-table-form-scrollbar">
            <div class="ba-operate-form" :class="'ba-' + baTable.form.operate + '-form'"
                :style="'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'">
                <el-form v-if="!baTable.form.loading" ref="formRef" @submit.prevent=""
                    @keyup.enter="baTable.onSubmit(formRef)" :model="baTable.form.items" label-position="right"
                    :label-width="baTable.form.labelWidth + 'px'" :rules="rules">
                    <FormItem :label="t('demand.demandPersonRecord.status')" type="select"
                        v-model="baTable.form.items!.status" prop="status" :data="{
                            content: {
                                1: '待开始',
                                2: '进行中',
                                3: '已完成',
                                4: '请假',
                                5: '暂停中',
                                6: '已回收',
                                7: '延期',
                            }
                        }" :placeholder="t('Please input field', { field: t('demand.demandRecord.link') })" />
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
    status: [buildValidatorData({ name: 'required', title: t('demand.demandPersonRecord.status') })],
})
</script>

<style scoped lang="scss">
.abow_dialog {
    display: flex;
    justify-content: center;
    align-items: Center;
    overflow: hidden;

    .el-dialog {
        margin: 0 auto !important;
        height: 90%;
        overflow: hidden;

        .el-dialog__body {
            position: absolute;
            left: 0;
            top: 54px;
            bottom: 0;
            right: 0;
            padding: 0;
            z-index: 1;
            overflow: hidden;
            overflow-y: auto;
        }
    }
}
</style>
