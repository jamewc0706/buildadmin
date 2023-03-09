<template>
    <!-- 对话框表单 -->
    <el-dialog class="abow_dialog" :close-on-click-modal="false"
        :model-value="['add', 'edit'].includes(baTable.form.operate!)" @close="baTable.toggleForm" width="50%">
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
                    <FormItem :label="t('tag.tag.admin_name')" type="select" v-model="baTable.form.items!.admin_id"
                        prop="admin_id" :data="{ content: adminList }"
                        :placeholder="t('Please select field', { field: t('tag.tag.admin_name') })" />
                    <FormItem :label="t('tag.tag.tag')" type="select" v-model="baTable.form.items!.tag" prop="tag" :data="{
                        content: {
                            1: '外延固定',
                            2: '外延灵活',
                            3: '非外延灵活',
                        }
                    }" :placeholder="t('Please input field', { field: t('tag.tag.tag') })" />
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
    adminList: anyObj,
}

const props = withDefaults(defineProps<Props>(), {
    adminList: () => {
        return {}
    },
})

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    admin_id: [buildValidatorData({ name: 'required', title: t('tag.tag.admin_name') })],
    tag: [buildValidatorData({ name: 'required', title: t('tag.tag.tag') })],
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