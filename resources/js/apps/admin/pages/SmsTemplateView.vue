<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">SMS Template Details</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading" @click="goIndex">Back</button>
                    <button type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" :disabled="loading || !smsTemplateId" @click="goEdit">Edit</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div v-if="loading" class="text-sm text-slate-600">Loading...</div>
            <div v-else class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200 p-4 sm:col-span-2">
                    <div class="text-xs font-semibold text-slate-500">Template Name</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.name || '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs font-semibold text-slate-500">SMS Type</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ smsTypeLabel(row.sms_type) }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs font-semibold text-slate-500">Common Message</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ Number(row.common_message || 0) === 1 ? 'YES' : 'NO' }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs font-semibold text-slate-500">Sending Status</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ Number(row.sending_status || 0) === 1 ? 'ONLINE' : 'OFFLINE' }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4 sm:col-span-2">
                    <div class="text-xs font-semibold text-slate-500">SMS Body</div>
                    <div class="mt-1 whitespace-pre-wrap text-sm font-semibold text-slate-900">{{ row.sms_body || '—' }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'SmsTemplateView',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        smsTemplateId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            loading: false,
            error: '',
            row: {},
        }
    },
    computed: {
        smsTypes() {
            return this.systems?.global?.sms_types || {}
        },
    },
    async created() {
        await this.load()
    },
    methods: {
        smsTypeLabel(key) {
            const k = String(key || '')
            return this.smsTypes?.[k] || k || '—'
        },
        go(path) {
            window.history.pushState({}, '', path)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        goIndex() {
            this.go('/admin/smsTemplate')
        },
        goEdit() {
            this.go(`/admin/smsTemplate/${this.smsTemplateId}/edit`)
        },
        async load() {
            if (!this.smsTemplateId) return
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/smsTemplate/${this.smsTemplateId}`)
                this.row = res?.data?.sms_template || res?.data || {}
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load template.'
            } finally {
                this.loading = false
            }
        },
    },
}
</script>
