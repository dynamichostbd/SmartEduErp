<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="text-xl font-semibold text-slate-900">SMS History</div>
                </div>

                <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                    <select v-model="filters.sms_template_id" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200" :disabled="loading">
                        <option value="">All Templates</option>
                        <option v-for="t in templates" :key="'t-' + t.id" :value="String(t.id)">{{ t.name }}</option>
                    </select>

                    <input v-model="filters.from_date" type="date" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200" :disabled="loading" />
                    <input v-model="filters.to_date" type="date" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200" :disabled="loading" />

                    <div class="flex items-center gap-2">
                        <button class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-600 text-white hover:bg-emerald-700" :disabled="loading" @click="load(1)">
                            <span class="sr-only">Search</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.35-5.15a7 7 0 11-14 0 7 0 0114 0z" />
                            </svg>
                        </button>

                        <button class="h-10 rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800" :disabled="loading" @click="goCreate">Send SMS</button>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex flex-col gap-1 text-sm sm:flex-row sm:items-center sm:justify-end">
                <div class="text-emerald-700"><span class="font-semibold">Total Send:</span> {{ totalSend }}</div>
                <div class="hidden sm:block px-2 text-slate-400">|</div>
                <div class="text-rose-700"><span class="font-semibold">Total Cost:</span> {{ totalCost }}</div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-slate-200">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3">#</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3">Date</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3">SMS Template</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3 text-center">SMS Cost</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3 text-center">Total Sending SMS</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3 text-center">Total Cost</th>
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
                            <td class="border border-slate-200 px-4 py-3">{{ row.date || '—' }}</td>
                            <td class="border border-slate-200 px-4 py-3">{{ row.sms_template_name || '—' }}</td>
                            <td class="border border-slate-200 px-4 py-3 text-center">{{ money(row.sms_cost) }}</td>
                            <td class="border border-slate-200 px-4 py-3 text-center">{{ Number(row.total_sending_sms || 0) }}</td>
                            <td class="border border-slate-200 px-4 py-3 text-center">{{ money(Number(row.sms_cost || 0) * Number(row.total_sending_sms || 0)) }}</td>
                            <td class="border border-slate-200 px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-slate-50" title="View" @click="goView(row.id)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-7 9.5-7 9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
                                        </svg>
                                    </button>
                                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-red-200 bg-white text-red-700 hover:bg-red-50" title="Delete" :disabled="deletingId === row.id" @click="destroy(row.id)">
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
    name: 'SmsHistoryIndex',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        const today = new Date().toISOString().slice(0, 10)
        return {
            loading: false,
            deletingId: null,
            error: '',
            rows: [],
            templates: [],
            filters: {
                pagination: 10,
                sms_template_id: '',
                from_date: today,
                to_date: today,
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
        totalSend() {
            return (this.rows || []).reduce((acc, r) => acc + Number(r.total_sending_sms || 0), 0)
        },
        totalCost() {
            return this.money(
                (this.rows || []).reduce((acc, r) => acc + Number(r.total_sending_sms || 0) * Number(r.sms_cost || 0), 0)
            )
        },
    },
    async created() {
        await this.loadTemplates()
        await this.load(1)
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
            this.go('/admin/smsHistory/create')
        },
        goView(id) {
            this.go(`/admin/smsHistory/${id}`)
        },
        async loadTemplates() {
            try {
                const res = await window.axios.get('/admin/smsTemplate', { params: { allData: true } })
                this.templates = Array.isArray(res?.data) ? res.data : []
            } catch {
                this.templates = []
            }
        },
        async load(page = 1) {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get('/admin/smsHistory', {
                    params: {
                        page,
                        pagination: this.filters.pagination,
                        sms_template_id: this.filters.sms_template_id,
                        from_date: this.filters.from_date,
                        to_date: this.filters.to_date,
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
                this.error = e?.response?.data?.message || 'Failed to load SMS history.'
            } finally {
                this.loading = false
            }
        },
        async destroy(id) {
            if (!confirm('Are you sure?')) return
            this.deletingId = id
            try {
                const res = await window.axios.delete(`/admin/smsHistory/${id}`)
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
