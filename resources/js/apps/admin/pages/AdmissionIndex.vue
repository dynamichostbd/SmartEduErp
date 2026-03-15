<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">{{ pageHeading }}</div>
                    <div class="mt-1 text-sm text-slate-600">Admissions</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goDashboard">Dashboard</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="grid grid-cols-1 gap-3 lg:grid-cols-12">
                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Status</div>
                    <select v-model="filters.status" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" @change="load(1)">
                        <option value="">All</option>
                        <option value="pending">Dues</option>
                        <option value="success">Paid</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Session</div>
                    <select v-model="filters.academic_session_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="s in sessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Academic Level</div>
                    <select v-model="filters.academic_qualification_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Department</div>
                    <select v-model="filters.department_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="d in filteredDepartments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Class</div>
                    <select v-model="filters.academic_class_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="c in filteredClasses" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">All Purpose</div>
                    <select v-model="filters.account_head_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="h in admissionHeads" :key="'ah-' + h.id" :value="String(h.id)">{{ h.name }}</option>
                    </select>
                </div>

                <div v-if="mode === 'accountWise'" class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">All Account</div>
                    <select v-model="filters.payment_gateway_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="a in accountsAdmissions" :key="'acc-' + a.id" :value="String(a.id)">{{ a.account_no }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">From Date</div>
                    <input v-model="filters.from_date" type="date" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">To Date</div>
                    <input v-model="filters.to_date" type="date" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Search Field</div>
                    <select v-model="filters.field_name" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="invoice_number">Invoice No.</option>
                        <option value="name">Name</option>
                        <option value="mobile">Mobile</option>
                        <option value="admission_roll">Admission Roll</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Search</div>
                    <input v-model="filters.value" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" @keydown.enter.prevent="load(1)" />
                </div>

                <div class="lg:col-span-1 flex items-end gap-2">
                    <button type="button" class="h-10 w-full rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="loading" @click="load(1)">{{ loading ? '...' : 'Go' }}</button>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Student</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Admission Roll</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Session</th>
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
                        <tr v-for="row in rows" :key="'r-' + row.id">
                            <td class="px-3 py-2">
                                <div class="font-semibold text-slate-900">{{ row.name }}</div>
                                <div class="text-xs text-slate-600">{{ row.mobile }}</div>
                            </td>
                            <td class="px-3 py-2">{{ row.admission_roll }}</td>
                            <td class="px-3 py-2">{{ row.academic_session ? row.academic_session.name : '' }}</td>
                            <td class="px-3 py-2">{{ row.department ? row.department.name : '' }}</td>
                            <td class="px-3 py-2">
                                <div>{{ row.qualification ? row.qualification.name : '' }}</div>
                                <div class="text-xs text-slate-600">({{ row.academic_class ? row.academic_class.name : '' }})</div>
                            </td>
                            <td class="px-3 py-2">{{ row.head ? row.head.name : '' }}</td>
                            <td class="px-3 py-2">
                                <a href="#" class="font-semibold text-emerald-700 hover:underline" @click.prevent="openAdmission(row.id)">{{ row.invoice_number }}</a>
                                <div class="text-xs text-slate-600">{{ row.invoice_date }}</div>
                            </td>
                            <td class="px-3 py-2">{{ row.payment_date }}</td>
                            <td class="px-3 py-2 text-right font-semibold">{{ money(row.amount) }}</td>
                            <td class="px-3 py-2 text-center">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" :class="row.status === 'success' ? 'bg-emerald-50 text-emerald-700' : row.status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700'">
                                    {{ (row.status || '').toUpperCase() }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="!rows.length">
                            <td colspan="10" class="px-3 py-10 text-center text-slate-500">No Data Found</td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-slate-50">
                        <tr>
                            <td colspan="8" class="px-3 py-2 text-right font-semibold text-slate-700">Total Amount</td>
                            <td class="px-3 py-2 text-right font-semibold text-slate-900">{{ money(totalAmount) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div v-if="meta.total > 0" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-slate-600">Showing {{ meta.from }} to {{ meta.to }} of {{ meta.total }} entries</div>

                <div class="flex items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="meta.current_page <= 1" @click="load(meta.current_page - 1)">Prev</button>
                    <div class="text-sm font-semibold text-slate-700">{{ meta.current_page }} / {{ meta.last_page }}</div>
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="meta.current_page >= meta.last_page" @click="load(meta.current_page + 1)">Next</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AdmissionIndex',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        mode: {
            type: String,
            default: '',
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
                status: 'success',
                field_name: 'mobile',
                value: '',
                academic_class_id: '',
                academic_session_id: '',
                department_id: '',
                academic_qualification_id: '',
                account_head_id: '',
                payment_gateway_id: '',
                from_date: '',
                to_date: '',
            },
        }
    },
    computed: {
        pageHeading() {
            if (this.mode === 'accountWise') return 'Admission Account Wise Payment Report'
            return 'Admission Payment Report'
        },
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
        departments() {
            const list = this.systems?.global?.departments
            return Array.isArray(list) ? list : []
        },
        classes() {
            const list = this.systems?.global?.academic_classes
            return Array.isArray(list) ? list : []
        },
        admissionHeads() {
            const raw = this.systems?.global?.admission_heads || {}
            const keys = Object.keys(raw || {})
            return keys
                .map((k) => ({ id: Number(k), name: raw[k] }))
                .filter((x) => Number.isFinite(x.id) && x.name != null)
        },
        accountsAdmissions() {
            const list = this.systems?.global?.accounts_admissions
            return Array.isArray(list) ? list : []
        },
        filteredDepartments() {
            const qId = this.filters.academic_qualification_id
            if (!qId) return this.departments

            const dqs = this.systems?.global?.department_qualidactions
            const allowed = Array.isArray(dqs) ? dqs.filter((x) => String(x?.academic_qualification_id) === String(qId)).map((x) => String(x?.department_id)) : []
            if (!allowed.length) return this.departments

            return this.departments.filter((d) => allowed.includes(String(d?.id)))
        },
        filteredClasses() {
            const qId = this.filters.academic_qualification_id
            if (!qId) return this.classes
            return this.classes.filter((c) => String(c?.academic_qualification_id) === String(qId))
        },
        totalAmount() {
            return this.rows.reduce((sum, r) => sum + (Number(r?.amount || 0) || 0), 0)
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
        goDashboard() {
            window.history.pushState({}, '', '/admin/dashboard')
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        openAdmission(id) {
            if (!id) return
            window.history.pushState({}, '', `/admin/admission/${id}`)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        apiPath() {
            if (this.mode === 'accountWise') return '/admin/admission'
            return '/admin/admission'
        },
        async load(page) {
            this.loading = true
            this.error = ''
            try {
                const params = { ...this.filters, page: page || 1 }
                if (this.mode !== 'accountWise') {
                    delete params.payment_gateway_id
                }

                const res = await window.axios.get(this.apiPath(), { params })
                const data = res?.data || null
                this.rows = Array.isArray(data?.data) ? data.data : []
                this.meta = {
                    from: data?.from || 0,
                    to: data?.to || 0,
                    total: data?.total || 0,
                    current_page: data?.current_page || 1,
                    last_page: data?.last_page || 1,
                }
            } catch (e) {
                this.rows = []
                this.meta = { from: 0, to: 0, total: 0, current_page: 1, last_page: 1 }
                this.error = e?.response?.data?.message || 'Failed to load admissions.'
            } finally {
                this.loading = false
            }
        },
    },
}
</script>
