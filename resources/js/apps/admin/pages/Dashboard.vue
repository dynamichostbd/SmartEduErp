<template>
    <div class="flex flex-col gap-4">
        <div v-if="popup && (popup.title || popup.description || popup.image)" class="print:hidden">
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="truncate text-lg font-semibold text-slate-900">{{ popup.title || 'Notice' }}</div>
                        <div class="mt-2 text-sm text-slate-700" v-html="popup.description"></div>
                    </div>
                    <button type="button" class="shrink-0 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50" @click="popup = null">Close</button>
                </div>
                <div v-if="popup.image" class="mt-4 flex justify-center">
                    <img :src="popup.image" class="max-h-[320px] w-auto rounded-xl border border-slate-200" />
                </div>
            </div>
        </div>

        <div v-if="!initialReady && !hasHydratedCache" class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-600">
            Loading dashboard...
        </div>

        <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border-l-4 border-sky-500 bg-white p-5">
                <div class="text-sm text-slate-500">Today’s Payment</div>
                <div class="mt-2 text-2xl font-semibold text-sky-700">{{ money(info.todays_payment) }}</div>
            </div>
            <div class="rounded-2xl border-l-4 border-sky-500 bg-white p-5">
                <div class="text-sm text-slate-500">Previous Day Payment</div>
                <div class="mt-2 text-2xl font-semibold text-sky-700">{{ money(info.prev_day_payment) }}</div>
            </div>
            <div class="rounded-2xl border-l-4 border-sky-500 bg-white p-5">
                <div class="text-sm text-slate-500">Last 7 Day's Payment</div>
                <div class="mt-2 text-2xl font-semibold text-sky-700">{{ money(info.prev7_day_payment) }}</div>
            </div>
            <div class="rounded-2xl border-l-4 border-sky-500 bg-white p-5">
                <div class="text-sm text-slate-500">Last Month Payment</div>
                <div class="mt-2 text-2xl font-semibold text-sky-700">{{ money(info.previous_month_payment) }}</div>
            </div>

            <div class="rounded-2xl border-l-4 border-sky-500 bg-white p-5">
                <div class="text-sm text-slate-500">This Month Payment</div>
                <div class="mt-2 text-2xl font-semibold text-sky-700">{{ money(info.current_month_payment) }}</div>
            </div>
            <div class="rounded-2xl border-l-4 border-sky-500 bg-white p-5">
                <div class="text-sm text-slate-500">Current Year Payment</div>
                <div class="mt-2 text-2xl font-semibold text-sky-700">{{ money(info.current_year_payment) }}</div>
            </div>
            <div class="rounded-2xl border-l-4 border-rose-500 bg-white p-5">
                <div class="text-sm text-slate-500">Today’s No. of Transaction</div>
                <div class="mt-2 text-2xl font-semibold text-rose-700">{{ number(info.todays_trans) }}</div>
            </div>
            <div class="rounded-2xl border-l-4 border-rose-500 bg-white p-5">
                <div class="text-sm text-slate-500">Prev. Day No. of Transaction</div>
                <div class="mt-2 text-2xl font-semibold text-rose-700">{{ number(info.prev_day_trans) }}</div>
            </div>

            <div class="rounded-2xl border-l-4 border-amber-500 bg-white p-5">
                <div class="text-sm text-slate-500">Total Department</div>
                <div class="mt-2 text-2xl font-semibold text-amber-700">{{ number(info.total_dept) }}</div>
            </div>
            <div class="rounded-2xl border-l-4 border-amber-500 bg-white p-5">
                <div class="text-sm text-slate-500">Total Students</div>
                <div class="mt-2 text-2xl font-semibold text-amber-700">{{ number(info.total_student) }}</div>
            </div>

            <div class="rounded-2xl border-l-4 border-emerald-500 bg-white p-5">
                <div class="text-sm text-slate-500">Today's Wallet Recharge</div>
                <div class="mt-2 text-2xl font-semibold text-emerald-700">{{ money(info.todays_wallet_recharge) }}</div>
            </div>
            <div class="rounded-2xl border-l-4 border-emerald-500 bg-white p-5">
                <div class="text-sm text-slate-500">Today's Wallet Transaction</div>
                <div class="mt-2 text-2xl font-semibold text-emerald-700">{{ number(info.todays_wallet_trans) }}</div>
            </div>
        </div>

        <div v-if="initialReady || hasHydratedCache" class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="mb-3 flex items-center justify-between gap-3">
                <div>
                    <div class="text-lg font-semibold text-slate-900">Today's Payments</div>
                    <div class="text-sm text-slate-600">{{ tableSummary }}</div>
                </div>

                <div class="flex items-center gap-2 print:hidden">
                    <button type="button" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="loadingTable" @click="loadPayments(1)">Refresh</button>
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goInvoices">Invoices</button>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Software ID</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Admission ID</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Student</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Dept. / Group</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Academic Level / Class</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Purpose</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Invoice</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Payment Date</th>
                            <th class="px-3 py-2 text-right font-semibold text-slate-700">Amount</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="row in rows" :key="'inv-' + row.id">
                            <td class="px-3 py-2">{{ row.student ? row.student.student_id : '' }}</td>
                            <td class="px-3 py-2">
                                <span v-if="row.student">{{ row.student.admission_id }}</span>
                                <span v-else-if="row.online_admission">{{ row.online_admission.admission_roll }}</span>
                            </td>
                            <td class="px-3 py-2">
                                <div v-if="row.student">
                                    <div class="font-semibold text-slate-900">{{ row.student.name }}</div>
                                    <div class="text-xs text-slate-600">{{ row.student.mobile }}</div>
                                </div>
                                <div v-else-if="row.online_admission">
                                    <div class="font-semibold text-slate-900">{{ row.online_admission.name }}</div>
                                    <div class="text-xs text-slate-600">{{ row.online_admission.mobile }}</div>
                                </div>
                            </td>
                            <td class="px-3 py-2">{{ row.department ? row.department.name : '' }}</td>
                            <td class="px-3 py-2">
                                <div>{{ row.qualification ? row.qualification.name : '' }}</div>
                                <div class="text-xs text-slate-600">({{ row.academic_class ? row.academic_class.name : '' }})</div>
                            </td>
                            <td class="px-3 py-2">{{ row.head ? row.head.name : '' }}</td>
                            <td class="px-3 py-2">
                                <a href="#" class="font-semibold text-emerald-700 hover:underline" @click.prevent="openInvoice(row.id)">
                                    {{ row.invoice_number }}
                                </a>
                                <div class="text-xs text-slate-600">{{ row.invoice_date }}</div>
                            </td>
                            <td class="px-3 py-2">{{ row.payment_date }}</td>
                            <td class="px-3 py-2 text-right font-semibold">{{ money(row.amount) }}</td>
                            <td class="px-3 py-2 text-center">
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                                    :class="row.status === 'success' ? 'bg-emerald-50 text-emerald-700' : row.status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700'"
                                >
                                    {{ (row.status || '').toUpperCase() }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="!rows.length">
                            <td colspan="10" class="px-3 py-10 text-center text-slate-500">No Data Found</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="meta.total > 0" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between print:hidden">
                <div class="text-sm text-slate-600">Showing {{ meta.from }} to {{ meta.to }} of {{ meta.total }} entries</div>

                <div class="flex items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="meta.current_page <= 1" @click="loadPayments(meta.current_page - 1)">
                        Prev
                    </button>
                    <div class="text-sm font-semibold text-slate-700">{{ meta.current_page }} / {{ meta.last_page }}</div>
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="meta.current_page >= meta.last_page" @click="loadPayments(meta.current_page + 1)">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Dashboard',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            loadingInfo: false,
            loadingTable: false,
            initialLoadedInfo: false,
            initialLoadedTable: false,
            hasHydratedCache: false,
            info: {},
            popup: null,
            rows: [],
            meta: {
                from: 0,
                to: 0,
                total: 0,
                current_page: 1,
                last_page: 1,
            },
        }
    },
    computed: {
        tableSummary() {
            if (!this.meta || !this.meta.total) return ''
            return `Total: ${this.meta.total}`
        },
        deptCacheKey() {
            const deptId = this.systems?.global?.auth_user?.department_id
            const roleId = this.systems?.global?.auth_user?.role_id
            const d = Number(deptId || 0)
            const r = Number(roleId || 0)
            return `admin_dashboard_cache_v1_dept_${Number.isFinite(d) ? d : 0}_role_${Number.isFinite(r) ? r : 0}`
        },
        initialReady() {
            return this.initialLoadedInfo && this.initialLoadedTable
        },
    },
    created() {
        this.hydrateFromCache()
        Promise.all([this.loadInfo(), this.loadPayments(1)]).catch(() => {})
    },
    methods: {
        hydrateFromCache() {
            try {
                const raw = window.localStorage.getItem(this.deptCacheKey)
                if (!raw) return

                const parsed = JSON.parse(raw)
                const cachedInfo = parsed?.info && typeof parsed.info === 'object' ? parsed.info : null
                const cachedRows = Array.isArray(parsed?.rows) ? parsed.rows : null
                const cachedMeta = parsed?.meta && typeof parsed.meta === 'object' ? parsed.meta : null

                if (cachedInfo) {
                    this.info = cachedInfo
                    this.popup = cachedInfo?.popup || null
                    this.initialLoadedInfo = true
                }

                if (cachedRows && cachedMeta) {
                    this.rows = cachedRows
                    this.meta = {
                        from: cachedMeta.from || 0,
                        to: cachedMeta.to || 0,
                        total: cachedMeta.total || 0,
                        current_page: cachedMeta.current_page || 1,
                        last_page: cachedMeta.last_page || 1,
                    }
                    this.initialLoadedTable = true
                }

                if (cachedInfo || (cachedRows && cachedMeta)) {
                    this.hasHydratedCache = true
                }
            } catch (e) {
            }
        },
        persistToCache(partial = {}) {
            try {
                const raw = window.localStorage.getItem(this.deptCacheKey)
                const prev = raw ? JSON.parse(raw) : {}
                const next = {
                    ...prev,
                    ...partial,
                    ts: Date.now(),
                }
                window.localStorage.setItem(this.deptCacheKey, JSON.stringify(next))
            } catch (e) {
            }
        },
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
        goInvoices() {
            window.history.pushState({}, '', '/admin/invoice')
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        openInvoice(id) {
            window.history.pushState({}, '', `/admin/invoice/${id}`)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        async loadInfo() {
            this.loadingInfo = true
            try {
                const res = await window.axios.get('/admin/get-dashboard-info')
                this.info = res?.data || {}
                this.popup = this.info?.popup || null
                this.persistToCache({ info: this.info })
            } catch (e) {
                this.info = {}
                this.popup = null
            } finally {
                this.loadingInfo = false
                this.initialLoadedInfo = true
            }
        },
        async loadPayments(page = 1) {
            this.loadingTable = true
            try {
                const res = await window.axios.get('/admin/today-payments', {
                    params: {
                        pagination: 15,
                        status: 'success',
                        date: true,
                        page,
                    },
                })

                const data = res?.data || {}
                this.rows = Array.isArray(data.data) ? data.data : []
                this.meta = {
                    from: data.from || 0,
                    to: data.to || 0,
                    total: data.total || 0,
                    current_page: data.current_page || 1,
                    last_page: data.last_page || 1,
                }

                if (page === 1) {
                    this.persistToCache({ rows: this.rows, meta: this.meta })
                }
            } catch (e) {
                this.rows = []
                this.meta = { from: 0, to: 0, total: 0, current_page: 1, last_page: 1 }
            } finally {
                this.loadingTable = false
                this.initialLoadedTable = true
            }
        },
    },
}
</script>
