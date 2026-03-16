<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Refund Amount</div>
                    <div class="mt-1 text-sm text-slate-600">Invoices</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goInvoices">Invoices</button>
                    <button type="button" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="loading" @click="load(1)">
                        {{ loading ? '...' : 'Search' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="grid grid-cols-1 gap-3 lg:grid-cols-12">
                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Session</div>
                    <select v-model="filters.academic_session_id" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="s in sessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Academic Level</div>
                    <select v-model="filters.academic_qualification_id" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Department</div>
                    <select v-model="filters.department_id" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="d in filteredDepartments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Class</div>
                    <select v-model="filters.academic_class_id" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="c in filteredClasses" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">From Date</div>
                    <input v-model="filters.from_date" type="date" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">To Date</div>
                    <input v-model="filters.to_date" type="date" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Search Field</div>
                    <select v-model="filters.field_name" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="invoice_number">Invoice Number</option>
                        <option value="student_id">Software ID</option>
                        <option value="name">Name</option>
                        <option value="mobile">Mobile</option>
                        <option value="reg_no">Reg No</option>
                        <option value="admission_id">Admission ID</option>
                    </select>
                </div>

                <div class="lg:col-span-4">
                    <div class="text-xs font-semibold text-slate-600">Search</div>
                    <input v-model="filters.value" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" @keydown.enter.prevent="load(1)" />
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Per Page</div>
                    <select v-model="filters.pagination" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" @change="load(1)">
                        <option :value="10">10</option>
                        <option :value="15">15</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Student</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Admission ID</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Dept. / Group</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Academic Level / Class</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Purpose</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Invoice</th>
                            <th class="px-3 py-2 text-right font-semibold text-slate-700">Refund Amount</th>
                            <th class="px-3 py-2 text-right font-semibold text-slate-700">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="row in rows" :key="'r-' + row.id">
                            <td class="px-3 py-2">
                                <div v-if="row.student">
                                    <div class="font-semibold text-slate-900">{{ row.student.student_id }}</div>
                                    <div class="text-xs text-slate-600">{{ row.student.name }}</div>
                                    <div class="text-xs text-slate-600">{{ row.student.mobile }}</div>
                                </div>
                                <div v-else class="text-slate-500">-</div>
                            </td>
                            <td class="px-3 py-2">{{ row.student ? row.student.admission_id : '' }}</td>
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
                                <div class="text-xs text-slate-600">{{ row.payment_date }}</div>
                            </td>
                            <td class="px-3 py-2 text-right font-semibold text-rose-700">{{ money(row.refund_amount) }}</td>
                            <td class="px-3 py-2 text-right font-semibold">{{ money(row.amount) }}</td>
                        </tr>
                        <tr v-if="!rows.length">
                            <td colspan="8" class="px-3 py-10 text-center text-slate-500">No Data Found</td>
                        </tr>
                    </tbody>
                    <tfoot v-if="rows.length" class="bg-slate-50">
                        <tr>
                            <td colspan="6" class="px-3 py-2 text-right font-semibold text-slate-700">Total</td>
                            <td class="px-3 py-2 text-right font-semibold text-rose-700">{{ money(totalRefundAmount) }}</td>
                            <td class="px-3 py-2 text-right font-semibold">{{ money(totalAmount) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div v-if="meta.total > 0" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-slate-600">Showing {{ meta.from }} to {{ meta.to }} of {{ meta.total }} entries</div>

                <div class="flex items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="meta.current_page <= 1" @click="load(meta.current_page - 1)">
                        Prev
                    </button>
                    <div class="text-sm font-semibold text-slate-700">{{ meta.current_page }} / {{ meta.last_page }}</div>
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="meta.current_page >= meta.last_page" @click="load(meta.current_page + 1)">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'InvoiceRefundAmount',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            loading: false,
            error: '',
            rows: [],
            meta: { from: 0, to: 0, total: 0, current_page: 1, last_page: 1 },
            filters: {
                pagination: 10,
                field_name: 'mobile',
                value: '',
                academic_class_id: '',
                academic_session_id: '',
                department_id: '',
                academic_qualification_id: '',
                from_date: '',
                to_date: '',
                status: 'success',
            },
        }
    },
    computed: {
        sessions() {
            const list = this.systems?.global?.academic_sessions
            return Array.isArray(list) ? list : []
        },
        sessionsSorted() {
            const list = [...this.sessions]
            const sortKey = (s) => {
                const name = String(s?.name || '').trim()
                const m = name.match(/(19|20)\d{2}/)
                return m ? Number(m[0]) : 0
            }
            return list.sort((a, b) => sortKey(b) - sortKey(a))
        },
        qualifications() {
            const list = this.systems?.global?.academic_qualifications
            return Array.isArray(list) ? list : []
        },
        classes() {
            const list = this.systems?.global?.academic_classes
            return Array.isArray(list) ? list : []
        },
        departments() {
            const list = this.systems?.global?.departments
            return Array.isArray(list) ? list : []
        },
        departmentQualifications() {
            const list = this.systems?.global?.department_qualidactions || this.systems?.global?.department_qualifications
            return Array.isArray(list) ? list : []
        },
        filteredDepartments() {
            const qid = this.filters?.academic_qualification_id
            if (!qid) return this.departments
            const allowed = new Set(
                this.departmentQualifications
                    .filter((r) => String(r.academic_qualification_id) === String(qid))
                    .map((r) => String(r.department_id)),
            )
            return this.departments.filter((d) => allowed.has(String(d.id)))
        },
        filteredClasses() {
            const qid = this.filters?.academic_qualification_id
            if (!qid) return this.classes
            return this.classes.filter((c) => String(c.academic_qualification_id) === String(qid))
        },
        totalAmount() {
            return (this.rows || []).reduce((sum, item) => sum + parseFloat(item?.amount || 0), 0)
        },
        totalRefundAmount() {
            return (this.rows || []).reduce((sum, item) => sum + parseFloat(item?.refund_amount || 0), 0)
        },
    },
    watch: {
        'filters.academic_qualification_id': function () {
            this.filters.department_id = ''
            this.filters.academic_class_id = ''
        },
    },
    created() {
        this.load(1)
    },
    methods: {
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
        async load(page = 1) {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get('/admin/refund-amount', {
                    params: { ...this.filters, page },
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
            } catch (e) {
                this.rows = []
                this.meta = { from: 0, to: 0, total: 0, current_page: 1, last_page: 1 }
                this.error = e?.response?.data?.message || 'Failed to load refund amounts.'
            } finally {
                this.loading = false
            }
        },
    },
}
</script>
