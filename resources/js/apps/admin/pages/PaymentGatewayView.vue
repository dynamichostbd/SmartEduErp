<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Payment Gateway Details</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading" @click="goIndex">Back</button>
                    <button type="button" class="rounded-sm bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" :disabled="loading || !paymentGatewayId" @click="goEdit">Edit</button>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class=" border border-slate-300 bg-white p-5">
            <div v-if="loading" class="text-sm text-slate-600">Loading...</div>
            <div v-else class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-300 p-4 sm:col-span-2">
                    <div class="text-xs font-semibold text-slate-500">Title</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.title || '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-300 p-4">
                    <div class="text-xs font-semibold text-slate-500">Department</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ deptName(row.department_id) }}</div>
                </div>
                <div class="rounded-xl border border-slate-300 p-4">
                    <div class="text-xs font-semibold text-slate-500">Provider</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.provider || '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-300 p-4">
                    <div class="text-xs font-semibold text-slate-500">Store ID</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.store_id || '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-300 p-4">
                    <div class="text-xs font-semibold text-slate-500">Store Password</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.store_password || '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-300 p-4">
                    <div class="text-xs font-semibold text-slate-500">Account No</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.account_no || '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-300 p-4">
                    <div class="text-xs font-semibold text-slate-500">Status</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.status || '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-300 p-4 sm:col-span-2">
                    <div class="text-xs font-semibold text-slate-500">Description</div>
                    <div class="mt-1 whitespace-pre-wrap text-sm font-semibold text-slate-900">{{ row.description || '—' }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'PaymentGatewayView',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        paymentGatewayId: {
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
        departments() {
            const list = this.systems?.global?.departments
            return Array.isArray(list) ? list : []
        },
    },
    async created() {
        await this.load()
    },
    methods: {
        deptName(id) {
            const sid = String(id || '')
            const found = this.departments.find((d) => String(d.id) === sid)
            return found?.name || '—'
        },
        go(path) {
            window.history.pushState({}, '', path)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        goIndex() {
            this.go('/admin/paymentGateway')
        },
        goEdit() {
            this.go(`/admin/paymentGateway/${this.paymentGatewayId}/edit`)
        },
        async load() {
            if (!this.paymentGatewayId) return
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/paymentGateway/${this.paymentGatewayId}`)
                this.row = res?.data?.payment_gateway || res?.data || {}
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load payment gateway.'
            } finally {
                this.loading = false
            }
        },
    },
}
</script>
