<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="text-xl font-semibold text-slate-900">SMS Transaction</div>
                    <div v-if="smsBalance" class="mt-1 text-sm text-slate-600">
                        <span class="font-semibold" style="text-decoration: underline">Available Balance</span> :
                        <span class="font-semibold text-emerald-700">{{ smsBalanceText }} TK.</span>
                    </div>
                </div>

                <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                    <select v-model="filters.field_name" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none" :disabled="loading">
                        <option value="invoice_number">Invoice Number</option>
                    </select>

                    <input v-model="filters.value" type="text" class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none sm:w-72" placeholder="Search..." :disabled="loading" @keyup.enter="load(1)" />

                    <button class="h-10 rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800" :disabled="loading" @click="goCreate">Recharge Balance</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-slate-200">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3">#</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3">Invoice Date</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3">Invoice Number</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3 text-center">Amount</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3">Payment Date</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3 text-center">Status</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading" class="text-sm text-slate-600">
                            <td class="border border-slate-200 px-4 py-6" colspan="7">Loading...</td>
                        </tr>
                        <tr v-else-if="rows.length === 0" class="text-sm text-slate-600">
                            <td class="border border-slate-200 px-4 py-6" colspan="7">No data found.</td>
                        </tr>
                        <tr v-for="(row, idx) in rows" v-else :key="row.id" class="text-sm text-slate-800" :class="idx % 2 === 0 ? 'bg-white' : 'bg-slate-50'">
                            <td class="border border-slate-200 px-4 py-3 text-slate-600">{{ rowSerial(idx) }}</td>
                            <td class="border border-slate-200 px-4 py-3">{{ row.invoice_date || '—' }}</td>
                            <td class="border border-slate-200 px-4 py-3">
                                <a href="#" class="text-emerald-700 hover:underline" @click.prevent="goView(row.id)">{{ row.invoice_number || '—' }}</a>
                            </td>
                            <td class="border border-slate-200 px-4 py-3 text-center">{{ money(row.amount) }}</td>
                            <td class="border border-slate-200 px-4 py-3">{{ row.payment_date || '—' }}</td>
                            <td class="border border-slate-200 px-4 py-3 text-center">{{ row.status || '—' }}</td>
                            <td class="border border-slate-200 px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-slate-50" title="View" @click="goView(row.id)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-7 9.5-7 9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
                                        </svg>
                                    </button>
                                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-red-200 bg-white text-red-700 hover:bg-red-50" title="Delete" :disabled="deletingId === row.id || row.status === 'success'" @click="destroy(row.id)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 11v6" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 11v6" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7l1 14h8l1-14" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-xs text-slate-600">Showing {{ meta.from || 0 }} to {{ meta.to || 0 }} of {{ meta.total || 0 }} entries</div>

                <div class="flex items-center justify-end gap-1 overflow-x-auto">
                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading || meta.current_page <= 1" @click="load(meta.current_page - 1)">«</button>
                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading || meta.current_page >= meta.last_page" @click="load(meta.current_page + 1)">»</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'SmsTransactionIndex',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            loading: false,
            deletingId: null,
            error: '',
            rows: [],
            filters: {
                pagination: 10,
                field_name: 'invoice_number',
                value: '',
            },
            meta: {
                current_page: 1,
                last_page: 1,
                from: 0,
                to: 0,
                total: 0,
                per_page: 10,
            },
        }
    },
    computed: {
        smsBalance() {
            return Number(this.systems?.site?.sms_balance || 0)
        },
        smsBalanceText() {
            return this.smsBalance.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        },
    },
    created() {
        this.load(1)
    },
    methods: {
        toast(message, type = 'success') {
            window.dispatchEvent(new CustomEvent('app-toast', { detail: { message, type } }))
        },
        money(v) {
            const n = Number(v || 0)
            return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        },
        rowSerial(idx) {
            const from = Number(this.meta.from || 0)
            return from + idx
        },
        go(path) {
            window.history.pushState({}, '', path)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        goCreate() {
            this.go('/admin/smsTransaction/create')
        },
        goView(id) {
            this.go(`/admin/smsTransaction/${id}`)
        },
        async load(page = 1) {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get('/admin/smsTransaction', {
                    params: {
                        page,
                        pagination: this.filters.pagination,
                        field_name: this.filters.field_name,
                        value: this.filters.value,
                    },
                })
                const payload = res?.data || {}
                this.rows = Array.isArray(payload?.data) ? payload.data : []
                this.meta = {
                    current_page: payload?.current_page || 1,
                    last_page: payload?.last_page || 1,
                    from: payload?.from ?? 0,
                    to: payload?.to ?? 0,
                    total: payload?.total ?? 0,
                    per_page: payload?.per_page || this.filters.pagination,
                }
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load SMS transactions.'
            } finally {
                this.loading = false
            }
        },
        async destroy(id) {
            if (!confirm('Are you sure?')) return
            this.deletingId = id
            try {
                const res = await window.axios.delete(`/admin/smsTransaction/${id}`)
                this.toast(res?.data?.message || 'Delete Successfully!', 'success')
                await this.load(this.meta.current_page || 1)
            } catch (e) {
                this.toast(e?.response?.data?.message || 'Delete failed.', 'error')
            } finally {
                this.deletingId = null
            }
        },
    },
}
</script>
