<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 print:hidden">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Settlement Summary</div>
                    <div class="mt-1 text-sm text-slate-600">Account Summary</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading" @click="search">
                        {{ loading ? '...' : 'Search' }}
                    </button>
                    <button type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" :disabled="printing || !hasData" @click="print">
                        {{ printing ? '...' : 'PRINT' }}
                    </button>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-3 lg:grid-cols-12">
                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Report Type</div>
                    <select v-model="search_data.report_type" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option value="all">All Payments</option>
                        <option value="invoice">Student Payments</option>
                        <option value="admission">External Payments</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">From Date</div>
                    <input v-model="search_data.from_date" type="date" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">To Date</div>
                    <input v-model="search_data.to_date" type="date" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">All Account Head</div>
                    <select v-model="search_data.account_head_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="h in accountHeads" :key="'h-' + h.id" :value="String(h.id)">{{ h.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">All Account</div>
                    <select v-model="search_data.store_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="a in accountStores" :key="'s-' + a.store_id" :value="String(a.store_id)">{{ a.account_no }}</option>
                    </select>
                </div>
            </div>

            <div v-if="error" class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">{{ error }}</div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div id="printArea" class="flex flex-col gap-4">
                <div v-if="hasData" class="text-center">
                    <div class="text-xl font-bold text-slate-900">{{ systems?.site?.title_en || systems?.site?.title || '' }}</div>
                    <div class="text-sm text-slate-700">Smart Education ERP</div>
                    <div class="mt-1 text-sm text-slate-700">
                        <span class="font-semibold">{{ search_data.from_date }}</span>
                        to
                        <span class="font-semibold">{{ search_data.to_date }}</span>
                    </div>
                </div>

                <template v-for="(rows, levelKey) in summaries" :key="'lvl-' + levelKey">
                    <div class="px-1 text-base font-bold text-slate-900">
                        {{ academicLevel(levelKey) }}
                        <span class="font-semibold">( {{ money(totals(levelKey).total_amount) }} )</span>
                        BDT
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-700">Department Name</th>
                                    <th class="px-3 py-2 text-center font-semibold text-slate-700">Number of Student</th>
                                    <th class="px-3 py-2 text-center font-semibold text-slate-700">Payment Purpose</th>
                                    <th class="px-3 py-2 text-center font-semibold text-slate-700">Head Amount</th>
                                    <th class="px-3 py-2 text-center font-semibold text-slate-700">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                <tr v-for="(row, idx) in rows" :key="'r-' + levelKey + '-' + idx">
                                    <td class="px-3 py-2">{{ row.dept_name }}</td>
                                    <td class="px-3 py-2 text-center">{{ number(row.total_student) }}</td>
                                    <td class="px-3 py-2 text-center">{{ row.head_name_en }}</td>
                                    <td class="px-3 py-2 text-center">{{ money(row.head_amount) }}</td>
                                    <td class="px-3 py-2 text-center">{{ money(row.total_amount) }}</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2"></td>
                                    <td class="px-3 py-2 text-center font-semibold">{{ number(totals(levelKey).total_student) }}</td>
                                    <td class="px-3 py-2"></td>
                                    <td class="px-3 py-2 text-right font-semibold">Total =</td>
                                    <td class="px-3 py-2 text-center font-semibold">{{ money(totals(levelKey).total_amount) }} BDT</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </template>

                <div v-if="!hasData" class="py-10 text-center text-sm text-slate-600">No data found</div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'InvoiceAccountSummary',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            loading: false,
            printing: false,
            error: '',
            accountHeads: [],
            paymentGateways: [],
            summaries: {},
            search_data: {
                account_head_id: '',
                store_id: '',
                report_type: '',
                from_date: '',
                to_date: '',
            },
        }
    },
    computed: {
        hasData() {
            return this.summaries && Object.keys(this.summaries).length > 0
        },
        qualifications() {
            const list = this.systems?.global?.academic_qualifications
            return Array.isArray(list) ? list : []
        },
        accountStores() {
            const stores = new Map()
            ;(this.paymentGateways || []).forEach((g) => {
                if (g?.store_id == null) return
                const k = String(g.store_id)
                if (!stores.has(k)) {
                    stores.set(k, {
                        store_id: String(g.store_id),
                        account_no: g.account_no || k,
                    })
                }
            })
            return Array.from(stores.values())
        },
    },
    created() {
        this.loadLookups()
    },
    methods: {
        number(v) {
            const n = Number(v || 0)
            if (!Number.isFinite(n)) return 0
            return n
        },
        money(v) {
            const n = Number(v || 0)
            const val = Number.isFinite(n) ? n : 0
            return val.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        },
        academicLevel(id) {
            const k = String(id)
            const found = this.qualifications.find((q) => String(q.id) === k)
            return found ? found.name : ''
        },
        totals(levelKey) {
            const rows = this.summaries?.[levelKey] || []
            let total_amount = 0
            let total_student = 0
            rows.forEach((el) => {
                total_amount += parseFloat(el.total_amount || 0)
                total_student += parseFloat(el.total_student || 0)
            })
            return { total_amount, total_student }
        },
        async loadLookups() {
            try {
                const [headsRes, gatewayRes] = await Promise.all([
                    window.axios.get('/admin/accountHead', { params: { allData: true } }),
                    window.axios.get('/admin/get-paymentGateway', { params: { allData: true } }),
                ])
                this.accountHeads = Array.isArray(headsRes?.data) ? headsRes.data : []
                this.paymentGateways = Array.isArray(gatewayRes?.data) ? gatewayRes.data : []
            } catch (e) {
                this.accountHeads = []
                this.paymentGateways = []
            }
        },
        validate() {
            if (!this.search_data.report_type) return 'Report Type is required'
            if (!this.search_data.from_date) return 'From date is required'
            if (!this.search_data.to_date) return 'To date is required'
            return ''
        },
        async search() {
            this.error = ''
            const msg = this.validate()
            if (msg) {
                this.error = msg
                return
            }

            this.loading = true
            try {
                const res = await window.axios.get('/admin/account-summary', { params: this.search_data })
                this.summaries = res?.data || {}
            } catch (e) {
                this.summaries = {}
                this.error = e?.response?.data?.message || 'Failed to load summary.'
            } finally {
                this.loading = false
            }
        },
        print() {
            this.printing = true
            const styleEl = document.createElement('style')
            styleEl.setAttribute('data-print-page-style', 'account-summary')
            styleEl.textContent = '@media print{@page{size:A4;margin:10mm;}}'
            document.head.appendChild(styleEl)
            this.$nextTick(() => {
                window.print()
                setTimeout(() => {
                    styleEl.remove()
                    this.printing = false
                }, 500)
            })
        },
    },
}
</script>
