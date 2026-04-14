<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">{{ pageHeading }}</div>
                    <div class="mt-1 text-sm text-slate-600">Admissions</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goDashboard">Dashboard</button>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class=" border border-slate-300 bg-white p-5">
            <div class="grid grid-cols-1 gap-3 lg:grid-cols-12">
                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Status</div>
                    <select v-model="filters.status" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option value="pending">Dues</option>
                        <option value="success">Paid</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Session</div>
                    <select v-model="filters.academic_session_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="s in sessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Academic Level</div>
                    <select v-model="filters.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Department</div>
                    <select v-model="filters.department_id" :disabled="!filters.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="d in filteredDepartments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Class</div>
                    <select v-model="filters.academic_class_id" :disabled="!filters.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="c in filteredClasses" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">All Purpose</div>
                    <select v-model="filters.account_head_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="h in admissionHeads" :key="'ah-' + h.id" :value="String(h.id)">{{ h.name }}</option>
                    </select>
                </div>

                <div v-if="mode === 'accountWise'" class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">All Account</div>
                    <select v-model="filters.payment_gateway_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="a in accountsAdmissions" :key="'acc-' + a.id" :value="String(a.id)">{{ a.account_no }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">From Date</div>
                    <input v-model="filters.from_date" type="date" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">To Date</div>
                    <input v-model="filters.to_date" type="date" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Search Field</div>
                    <select v-model="filters.field_name" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="invoice_number">Invoice No.</option>
                        <option value="name">Name</option>
                        <option value="mobile">Mobile</option>
                        <option value="admission_roll">Admission Roll</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Search</div>
                    <input v-model="filters.value" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" @keydown.enter.prevent="load(1)" />
                </div>

                <div class="lg:col-span-1 flex items-end gap-2">
                    <button type="button" class="h-9 w-full rounded-sm bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="loading" @click="load(1)">{{ loading ? '...' : 'Go' }}</button>
                </div>
            </div>
        </div>

        <div class=" border border-slate-300 bg-white">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-slate-300 text-sm">
                    <thead class="bg-emerald-100">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-700">
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Student</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Academic Info</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Admission Info</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Invoice</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Payment Date</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-right font-semibold text-slate-700">Amount</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">Pay. Status</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">App. Status</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <tr v-for="(row, idx) in rows" :key="'a-' + row.id" :class="idx % 2 === 0 ? 'bg-white' : 'bg-slate-50'">
                            <td class="border border-slate-300 px-1 py-1">
                                <div class="font-semibold text-slate-900">{{ row.name }}</div>
                                <div class="text-xs text-slate-600">{{ row.mobile }}</div>
                                <div class="text-xs text-slate-500">Roll: {{ row.admission_roll || '--' }} | Reg: {{ row.registration_no || '--' }}</div>
                            </td>
                            <td class="border border-slate-300 px-1 py-1 text-slate-800">
                                <div>{{ row.academic_session ? row.academic_session.name : '' }}</div>
                                <div class="text-xs text-slate-500">({{ row.department ? row.department.name : '' }})</div>
                            </td>
                            <td class="border border-slate-300 px-1 py-1 text-slate-800">
                                <div>{{ row.qualification ? row.qualification.name : '' }}</div>
                                <div class="text-xs text-slate-500">({{ row.academic_class ? row.academic_class.name : '' }})</div>
                            </td>
                            <td class="border border-slate-300 px-1 py-1 text-slate-800">
                                <a href="#" class="font-semibold text-emerald-700 hover:underline" @click.prevent="openAdmission(row.id)">{{ row.invoice_number }}</a>
                                <div class="text-xs text-slate-600">{{ row.invoice_date }}</div>
                            </td>
                            <td class="border border-slate-300 px-1 py-1 text-slate-800">{{ row.payment_date }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-right font-semibold">{{ money(row.amount) }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-center">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" :class="row.status === 'success' ? 'bg-emerald-50 text-emerald-700' : row.status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700'">
                                    {{ (row.status || '').toUpperCase() }}
                                </span>
                            </td>
                            <td class="border border-slate-300 px-1 py-1 text-center">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" :class="row.application_status === 'approved' ? 'bg-emerald-50 text-emerald-700' : row.application_status === 'rejected' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700'">
                                    {{ row.application_status || '' }}
                                </span>
                            </td>
                            <td class="border border-slate-300 px-1 py-1 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100" title="Edit" @click="editInvoiceModal(row)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!rows.length">
                            <td colspan="9" class="px-1 py-10 text-center text-slate-500">No Data Found</td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-emerald-100">
                        <tr>
                            <td colspan="7" class="px-1 py-2 text-right font-semibold text-slate-700">Total Amount</td>
                            <td class="px-1 py-2 text-right font-semibold text-slate-900">{{ money(totalAmount) }}</td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div v-if="meta.total > 0" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-slate-600">Showing {{ meta.from }} to {{ meta.to }} of {{ meta.total }} entries</div>

                <div class="flex items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="meta.current_page <= 1" @click="load(meta.current_page - 1)">Prev</button>
                    <div class="text-sm font-semibold text-slate-700">{{ meta.current_page }} / {{ meta.last_page }}</div>
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="meta.current_page >= meta.last_page" @click="load(meta.current_page + 1)">Next</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50">
        <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-sm bg-white p-6">
            <div class="mb-4 flex items-center justify-between border-b pb-4">
                <h3 class="text-lg font-semibold text-slate-900">Edit Admission</h3>
                <button type="button" class="text-slate-400 hover:text-slate-600" @click="closeEditModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-2 border-b pb-2">
                    <label class="block text-sm font-medium text-slate-700">Invoice No: <strong>{{ editInvoice.invoice_number }}</strong></label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Student Name</label>
                    <input v-model="editInvoice.name" type="text" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="Name" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Admission Roll</label>
                    <input v-model="editInvoice.admission_roll" type="text" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="Admission Roll" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Mobile</label>
                    <input v-model="editInvoice.mobile" type="text" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="Mobile" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Amount</label>
                    <input v-model="editInvoice.amount" type="number" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="Amount" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Account Head</label>
                    <select v-model="editInvoice.account_head_id" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option v-for="head in admissionHeads" :key="head.id" :value="head.id">{{ head.name }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Session</label>
                    <select v-model="editInvoice.academic_session_id" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option v-for="session in sessionsSorted" :key="session.id" :value="session.id">{{ session.name }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Academic Level</label>
                    <select v-model="editInvoice.academic_qualification_id" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option v-for="qual in qualifications" :key="qual.id" :value="qual.id">{{ qual.name }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Department</label>
                    <select v-model="editInvoice.department_id" :disabled="!editInvoice.academic_qualification_id" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option v-for="dept in filteredEditDepartments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Class</label>
                    <select v-model="editInvoice.academic_class_id" :disabled="!editInvoice.academic_qualification_id" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option v-for="cls in filteredEditClasses" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Payment Date</label>
                    <input v-model="editInvoice.payment_date" type="date" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Refund Date</label>
                    <input v-model="editInvoice.refund_date" type="date" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Refund Note</label>
                    <textarea v-model="editInvoice.refund_note" rows="3" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="Refund note"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="closeEditModal">Cancel</button>
                <button type="button" class="rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="updating" @click="updateInvoice">
                    <span v-if="updating" class="flex items-center gap-2">
                        <i class="fas fa-spinner fa-spin"></i>
                        Updating...
                    </span>
                    <span v-else>Update Admission</span>
                </button>
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
            updating: false,
            showEditModal: false,
            rows: [],
            meta: { from: 0, to: 0, total: 0, current_page: 1, last_page: 1 },
            searchTimer: null,
            editInvoice: {
                id: null,
                name: '',
                mobile: '',
                admission_roll: '',
                amount: '',
                status: 'success',
                invoice_number: '',
                account_head_id: '',
                academic_session_id: '',
                department_id: '',
                academic_qualification_id: '',
                academic_class_id: '',
                payment_date: '',
                refund_date: '',
                refund_note: '',
                refund_amount: '',
            },
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
            if (!qId) return []

            const dqs = this.systems?.global?.department_qualidactions
            const allowed = Array.isArray(dqs) ? dqs.filter((x) => String(x?.academic_qualification_id) === String(qId)).map((x) => String(x?.department_id)) : []
            if (!allowed.length) return []

            return this.departments.filter((d) => allowed.includes(String(d?.id)))
        },
        filteredClasses() {
            const qId = this.filters.academic_qualification_id
            if (!qId) return []
            return this.classes.filter((c) => String(c?.academic_qualification_id) === String(qId))
        },
        filteredEditDepartments() {
            const qId = this.editInvoice.academic_qualification_id
            if (!qId) return []

            const dqs = this.systems?.global?.department_qualidactions
            const allowed = Array.isArray(dqs) ? dqs.filter((x) => String(x?.academic_qualification_id) === String(qId)).map((x) => String(x?.department_id)) : []
            if (!allowed.length) return []

            return this.departments.filter((d) => allowed.includes(String(d?.id)))
        },
        filteredEditClasses() {
            const qId = this.editInvoice.academic_qualification_id
            if (!qId) return []
            return this.classes.filter((c) => String(c?.academic_qualification_id) === String(qId))
        },
        totalAmount() {
            return this.rows.reduce((sum, r) => sum + (Number(r?.amount || 0) || 0), 0)
        },
    },
    created() {
        this.load(1)
    },
    watch: {
        'filters.academic_qualification_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.filters.department_id = ''
                this.filters.academic_class_id = ''
                this.scheduleLoad(true)
            }
        },
        'editInvoice.academic_qualification_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.editInvoice.department_id = ''
                this.editInvoice.academic_class_id = ''
            }
        },
        filters: {
            handler() {
                this.scheduleLoad(true)
            },
            deep: true,
        },
    },
    methods: {
        scheduleLoad(resetPage) {
            if (this.searchTimer) clearTimeout(this.searchTimer)
            this.searchTimer = setTimeout(() => {
                const page = resetPage ? 1 : Number(this.meta?.current_page || 1)
                this.load(page)
            }, 250)
        },
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
        // Edit Modal Methods
        editInvoiceModal(item) {
            const date = new Date().toISOString().slice(0, 10)
            
            this.editInvoice = {
                id: item.id,
                name: item.name,
                mobile: item.mobile,
                admission_roll: item.admission_roll,
                amount: item.amount,
                status: item.status || 'success',
                invoice_number: item.invoice_number,
                account_head_id: item.account_head_id,
                academic_session_id: item.academic_session_id,
                department_id: item.department_id,
                academic_qualification_id: item.academic_qualification_id,
                academic_class_id: item.academic_class_id,
                payment_date: item.payment_date ? item.payment_date : date,
                refund_date: item.refund_date ? item.refund_date : date,
                refund_note: item.refund_note,
                refund_amount: item.refund_amount,
            }

            this.showEditModal = true
        },
        closeEditModal() {
            this.showEditModal = false
        },
        async updateInvoice() {
            if (!confirm('Are you sure want to update?')) return

            this.updating = true
            this.error = ''
            try {
                await window.axios.put(`/admin/admission/${this.editInvoice.id}`, this.editInvoice)
                this.showEditModal = false
                this.load(this.meta.current_page)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to update admission.'
            } finally {
                this.updating = false
            }
        },
    },
}
</script>
