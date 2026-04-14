<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">SMS History Details</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading" @click="goIndex">Back</button>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class=" border border-slate-300 bg-white p-5">
            <div v-if="loading" class="text-sm text-slate-600">Loading...</div>
            <div v-else class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-300 p-4">
                    <div class="text-xs font-semibold text-slate-500">Date</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.date || '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-300 p-4">
                    <div class="text-xs font-semibold text-slate-500">Template</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ template.name || '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-300 p-4">
                    <div class="text-xs font-semibold text-slate-500">SMS Cost</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.sms_cost ?? '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-300 p-4">
                    <div class="text-xs font-semibold text-slate-500">Total Sending SMS</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.total_sending_sms ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'SmsHistoryView',
    props: {
        smsHistoryId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            loading: false,
            error: '',
            row: {},
            template: {},
        }
    },
    async created() {
        await this.load()
    },
    methods: {
        go(path) {
            window.history.pushState({}, '', path)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        goIndex() {
            this.go('/admin/smsHistory')
        },
        async load() {
            if (!this.smsHistoryId) return
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/smsHistory/${this.smsHistoryId}`)
                this.row = res?.data?.sms_history || {}
                this.template = res?.data?.sms_template || {}
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load SMS history.'
            } finally {
                this.loading = false
            }
        },
    },
}
</script>
