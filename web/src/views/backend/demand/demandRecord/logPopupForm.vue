<template>
    <!-- 对话框表单 -->
    <el-dialog class="ba-operate-dialog" :close-on-click-modal="false" :model-value="logModalConfig.visible"
        @close="hideLogModal" width="50%">
        <el-scrollbar class="ba-table-form-scrollbar">
            <el-col v-loading="loading" :xs="24" :sm="24" :md="24" :lg="12">
                <el-card :header="logModalConfig.title" shadow="never">
                    <el-timeline>
                        <el-timeline-item v-for="(item, idx) in log" :key="idx" size="large"
                            :timestamp="timeFormat(item.createtime)">
                            {{ item.title }}
                            <el-card>
                                <h4>Update Github template</h4>
                                <p>Tom committed 2018/4/12 20:46</p>
                            </el-card>
                        </el-timeline-item>
                    </el-timeline>
                </el-card>
            </el-col>
        </el-scrollbar>
    </el-dialog>
</template>

<script setup lang="ts">
import { timeFormat } from '/@/components/table'

interface Props {
    logModalConfig: anyObj,
    hideLogModal: Function,
    log: anyObj[],
    loading: boolean
}

const props = withDefaults(defineProps<Props>(), {
    log: () => {
        return []
    },
    logModalConfig: () => {
        return {
            visible: false,
            title: '',
        }
    },
    loading: () => {
        return false
    }
})

</script>

<style scoped lang="scss"></style>
