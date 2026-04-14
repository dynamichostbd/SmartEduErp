<template>
    <div class="min-h-screen bg-slate-50">
        <div class="mx-auto">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-slate-900">Hostel Payment</h1>
                <p class="text-slate-600">Manage hostel payments and fees</p>
            </div>

            <!-- Filters -->
            <div class="border border-slate-300 bg-white p-5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Status</label>
                        <select v-model="filters.status" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                            <option value="">All</option>
                            <option value="pending">Dues</option>
                            <option value="success">Paid</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Session</label>
                        <select v-model="filters.academic_session_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                            <option value="">All Sessions</option>
                            <option v-for="session in academicSessions" :key="session.id" :value="session.id">{{ session.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Academic Level</label>
                        <select v-model="filters.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                            <option value="">All Levels</option>
                            <option v-for="qual in qualifications" :key="qual.id" :value="qual.id">{{ qual.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Department</label>
                        <select v-model="filters.department_id" :disabled="!filters.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                            <option value="">All Departments</option>
                            <option v-for="dept in filteredDepartments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Academic Class</label>
                        <select v-model="filters.academic_class_id" :disabled="!filters.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                            <option value="">All Classes</option>
                            <option v-for="cls in filteredClasses" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Student Type</label>
                        <select v-model="filters.student_type" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                            <option value="">All Types</option>
                            <option v-for="type in studentTypes" :key="type" :value="type">{{ type }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Hostel</label>
                        <select v-model="filters.hostel_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                            <option value="">All Hostels</option>
                            <option v-for="h in hostels" :key="h.id" :value="h.id">{{ h.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">From Date</label>
                        <input v-model="filters.from_date" type="date" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" placeholder="From Date">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">To Date</label>
                        <input v-model="filters.to_date" type="date" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" placeholder="To Date">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Account Head</label>
                        <select v-model="filters.account_head_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                            <option value="">All Heads</option>
                            <option v-for="head in accountHeads" :key="head.id" :value="head.id">{{ head.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Search Field</label>
                        <select v-model="filters.field_name" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" :disabled="loading">
                            <option value="">Select Field</option>
                            <option value="name">Name</option>
                            <option value="mobile">Mobile</option>
                            <option value="college_roll">College Roll</option>
                            <option value="admission_id">Admission ID</option>
                            <option value="reg_no">Reg No</option>
                            <option value="invoice_number">Invoice Number</option>
                            <option value="student_id">Student ID</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Search</label>
                        <input v-model="filters.value" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" @keydown.enter.prevent="loadPayments" />
                    </div>
                    <div class="flex items-end">
                        <button @click="loadPayments" :disabled="loading" class="h-9 w-full rounded-sm bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50">
                            {{ loading ? 'Loading...' : 'Search' }}
                        </button>
                    </div>
                    <div class="flex items-end">
                        <button @click="resetFilters" class="h-9 w-full rounded-sm border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-6 border border-slate-300 bg-white p-5">
                <div class="flex flex-wrap gap-3">
                    <button @click="generateHostelFees" class="rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        Generate Hostel Fees
                    </button>
                    <button @click="exportExcel" class="rounded-sm bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">
                        Export Excel
                    </button>
                    <button @click="printInvoice" class="rounded-sm bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">
                        Print Invoice
                    </button>
                </div>
            </div>

            <!-- Payment Table -->
            <div class="mt-6 border border-slate-300 bg-white p-5">
                <div class="mb-4 flex justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">Payment Records</h2>
                    <div class="flex gap-2">
                        <span class="text-sm text-slate-600">Total: {{ meta.total }}</span>
                        <span class="text-sm text-slate-600">|</span>
                        <span class="text-sm text-slate-600">Total Amount: {{ formatMoney(totalAmount) }}</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse border border-slate-300">
                        <thead class="bg-emerald-100">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-700">
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">
                                    <input type="checkbox" @change="toggleSelectAll" :checked="allSelected" class="rounded border-slate-300">
                                </th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Student</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Admission ID</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Academic</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">Months</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Invoice</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Payment Date</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">Discount</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">Amount</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">Status</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <tr v-for="(payment, index) in payments" :key="payment.id" :class="index % 2 === 0 ? 'bg-white' : 'bg-slate-50'">
                                <td class="border border-slate-300 px-1 py-1 text-center">
                                    <input type="checkbox" v-model="selectedPayments" :value="payment.id" class="rounded border-slate-300">
                                </td>
                                <td class="border border-slate-300 px-1 py-1 text-sm text-slate-800">
                                    <div class="font-medium">{{ payment.student?.student_id }}</div>
                                    <div>{{ payment.student?.name }}</div>
                                    <div class="text-xs text-slate-500">{{ payment.student?.mobile }}</div>
                                </td>
                                <td class="border border-slate-300 px-1 py-1 text-sm text-slate-800">{{ payment.student?.admission_id }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-sm text-slate-800">
                                    <div>{{ payment.department?.name }}</div>
                                    <div>{{ payment.qualification?.name }}</div>
                                    <div class="text-xs text-slate-500">({{ payment.academic_class?.name }})</div>
                                </td>
                                <td class="border border-slate-300 px-1 py-1 text-center text-sm text-slate-800">{{ payment.details_count }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-sm text-slate-800">
                                    <a href="#" @click.prevent="openInvoicePage(payment)" class="text-emerald-600 hover:text-emerald-800">
                                        <div>{{ payment.invoice_number }}</div>
                                        <div class="text-xs">{{ formatDate(payment.invoice_date) }}</div>
                                    </a>
                                </td>
                                <td class="border border-slate-300 px-1 py-1 text-sm text-slate-800">{{ formatDate(payment.payment_date) }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-center text-sm text-slate-800">{{ formatMoney(payment.discount_amount) }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-center text-sm text-slate-800 font-medium">{{ formatMoney(payment.amount) }}</td>
                                <td class="border border-slate-300 px-1 py-1">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold" :class="getStatusClass(payment.status)">
                                        {{ payment.status }}
                                    </span>
                                </td>
                                <td class="border border-slate-300 px-1 py-1 text-center">
                                    <div class="flex justify-center gap-2">
                                        <button v-if="payment.status !== 'success'" @click="editInvoiceModal(payment)" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-slate-300 bg-white text-slate-700 hover:bg-slate-50" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button v-if="payment.status !== 'success'" @click="deletePayment(payment.id)" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-red-200 bg-white text-red-700 hover:bg-red-50" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <button v-if="payment.status !== 'success'" @click="paidInvoice(payment)" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100" title="Paid">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Bulk Actions -->
                <div v-if="selectedPayments.length > 0" class="mt-4 flex items-center justify-between rounded-sm border border-slate-300 bg-slate-50 p-3">
                    <span class="text-sm text-slate-600">{{ selectedPayments.length }} items selected</span>
                    <div class="flex gap-2">
                        <button @click="bulkReceivePayment" class="rounded-sm bg-emerald-600 px-3 py-1 text-sm font-semibold text-white hover:bg-emerald-700">
                            Bulk Receive
                        </button>
                        <button @click="bulkDelete" class="rounded-sm bg-red-600 px-3 py-1 text-sm font-semibold text-white hover:bg-red-700">
                            Bulk Delete
                        </button>
                    </div>
                </div>

                <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-xs text-slate-600">Showing {{ meta.from || 0 }} to {{ meta.to || 0 }} of {{ meta.total || 0 }} entries</div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="rounded-sm border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading || meta.current_page <= 1" @click="loadPayments(meta.current_page - 1)">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div class="text-sm font-semibold text-slate-700">{{ meta.current_page }} / {{ meta.last_page }}</div>
                        <button type="button" class="rounded-sm border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading || meta.current_page >= meta.last_page" @click="loadPayments(meta.current_page + 1)">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showInvoiceModal" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click="closeInvoiceModal">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="w-full max-w-3xl rounded-sm bg-white p-6" @click.stop>
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                            <div class="text-lg font-semibold text-slate-900">Delete invoice</div>
                            <div class="text-sm text-slate-600">Invoice No.: <span class="font-semibold">{{ invoiceData.invoice_number }}</span></div>
                            <div class="text-sm text-slate-600">Name: <span class="font-semibold">{{ invoiceData.student?.name || '' }}</span></div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button v-if="invoiceData.status !== 'success'" type="button" class="rounded-sm bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="invoiceLoading" @click="paidInvoice(invoiceData)">
                                Paid
                            </button>
                            <button type="button" class="rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="closeInvoiceModal">
                                Close
                            </button>
                        </div>
                    </div>

                    <div v-if="invoiceLoading" class="py-6 text-center text-sm text-slate-600">Loading...</div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full border-collapse border border-slate-300">
                            <thead class="bg-emerald-100">
                                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-700">
                                    <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">SL</th>
                                    <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Month</th>
                                    <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Purpose</th>
                                    <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">Amount</th>
                                    <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                <tr v-if="invoiceData.details && invoiceData.details.length > 0" v-for="(fees, key) in invoiceData.details" :key="fees.id">
                                    <td class="border border-slate-300 px-1 py-1 text-center text-sm">{{ key + 1 }}</td>
                                    <td class="border border-slate-300 px-1 py-1 text-sm">{{ formatMonth(fees.invoice_date) }}</td>
                                    <td class="border border-slate-300 px-1 py-1 text-sm">{{ fees.head?.name || '' }}</td>
                                    <td class="border border-slate-300 px-1 py-1 text-center text-sm">{{ fees.amount }}</td>
                                    <td class="border border-slate-300 px-1 py-1 text-center">
                                        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-red-200 bg-white text-red-700 hover:bg-red-50" @click="deleteInvoiceDetail(fees)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-else>
                                    <td colspan="5" class="border border-slate-300 px-1 py-2 text-center text-sm text-slate-500">No hostel fees have been generated yet</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'HostelPaymentIndex',
    props: ['systems'],
    data() {
        return {
            loading: false,
            searchTimer: null,
            payments: [],
            meta: { current_page: 1, last_page: 1, from: 0, to: 0, total: 0 },
            selectedPayments: [],
            showInvoiceModal: false,
            invoiceLoading: false,
            invoiceData: {},
            filters: {
                pagination: 10,
                status: '',
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
                student_type: '',
                hostel_id: '',
                from_date: '',
                to_date: '',
                account_head_id: '',
                field_name: '',
                value: ''
            }
        }
    },
    computed: {
        hostels() {
            return this.systems?.global?.hostels || []
        },
        academicSessions() {
            return this.systems?.global?.academic_sessions || []
        },
        qualifications() {
            return this.systems?.global?.academic_qualifications || []
        },
        departments() {
            return this.systems?.global?.departments || []
        },
        departmentQualidactions() {
            const list = this.systems?.global?.department_qualidactions || this.systems?.global?.department_qualifications
            return Array.isArray(list) ? list : []
        },
        filteredDepartments() {
            const qid = this.filters?.academic_qualification_id
            if (!qid) return []
            const allowed = new Set(
                this.departmentQualidactions
                    .filter((r) => String(r.academic_qualification_id) === String(qid))
                    .map((r) => String(r.department_id)),
            )
            return (this.departments || []).filter((d) => allowed.has(String(d.id)))
        },
        academicClasses() {
            return this.systems?.global?.academic_classes || []
        },
        filteredClasses() {
            const qid = this.filters?.academic_qualification_id
            if (!qid) return []
            return (this.academicClasses || []).filter((c) => String(c.academic_qualification_id) === String(qid))
        },
        studentTypes() {
            return this.systems?.global?.student_types || []
        },
        accountHeads() {
            const raw = this.systems?.global?.hostel_heads
            if (!raw) return []
            if (Array.isArray(raw)) return raw
            if (typeof raw === 'object') {
                return Object.keys(raw)
                    .map((id) => ({ id: Number(id), name: raw[id] }))
                    .filter((x) => Number.isFinite(x.id))
            }
            return []
        },
        totalAmount() {
            return this.payments.reduce((sum, payment) => sum + parseFloat(payment.amount || 0), 0)
        },
        allSelected() {
            return this.payments.length > 0 && this.selectedPayments.length === this.payments.length
        }
    },
    watch: {
        'filters.academic_qualification_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.filters.department_id = ''
                this.filters.academic_class_id = ''
                this.scheduleLoad(true)
            }
        },
        'filters.status'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad(true)
        },
        'filters.academic_session_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad(true)
        },
        'filters.department_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad(true)
        },
        'filters.academic_class_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad(true)
        },
        'filters.student_type'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad(true)
        },
        'filters.hostel_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad(true)
        },
        'filters.from_date'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad(true)
        },
        'filters.to_date'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad(true)
        },
        'filters.account_head_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad(true)
        },
        'filters.field_name'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad(true)
        },
        'filters.value'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad(true)
        },
        'filters.pagination'(next, prev) {
            if (Number(next || 0) !== Number(prev || 0)) this.scheduleLoad(true)
        },
    },
    mounted() {
        this.loadPayments(1)
    },
    methods: {
        scheduleLoad(resetPage) {
            if (this.searchTimer) clearTimeout(this.searchTimer)
            this.searchTimer = setTimeout(() => {
                const page = resetPage ? 1 : Number(this.meta?.current_page || 1)
                this.loadPayments(page)
            }, 250)
        },
        async loadPayments(page = 1) {
            this.loading = true
            try {
                const response = await window.axios.get('/admin/hostelPayment', { params: { ...this.filters, page } })
                const payload = response?.data || {}
                this.payments = payload.data || []
                this.meta = {
                    current_page: payload.current_page || 1,
                    last_page: payload.last_page || 1,
                    from: payload.from || 0,
                    to: payload.to || 0,
                    total: payload.total || 0,
                }
                this.selectedPayments = []
            } catch (error) {
                this.$toast.error('Failed to load payments')
            } finally {
                this.loading = false
            }
        },
        resetFilters() {
            this.filters = {
                pagination: 10,
                status: '',
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
                student_type: '',
                hostel_id: '',
                from_date: '',
                to_date: '',
                account_head_id: '',
                field_name: '',
                value: ''
            }
            this.loadPayments(1)
        },
        toggleSelectAll() {
            if (this.allSelected) {
                this.selectedPayments = []
            } else {
                this.selectedPayments = this.payments.map(p => p.id)
            }
        },
        generateHostelFees() {
            const path = '/admin/hostelFeeGenerate/create'
            window.history.pushState({}, '', path)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        exportExcel() {
            this.$toast.success('Excel exported successfully')
        },
        printInvoice() {
            if (this.selectedPayments.length === 1) {
                this.goTo(`/admin/hostelPayment/${this.selectedPayments[0]}`)
                return
            }
            if (this.selectedPayments.length > 1) {
                this.$toast.error('Please select only one invoice to print')
                return
            }
            this.$toast.error('Please select an invoice first')
        },
        openInvoicePage(payment) {
            if (!payment?.id) return
            this.goTo(`/admin/hostelPayment/${payment.id}`)
        },
        editInvoiceModal(payment) {
            this.openInvoiceModal(payment.id)
        },
        goTo(path) {
            window.history.pushState({}, '', path)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        async openInvoiceModal(id) {
            this.showInvoiceModal = true
            this.invoiceLoading = true
            this.invoiceData = {}
            try {
                const res = await window.axios.get(`/admin/hostelPayment/${id}`)
                this.invoiceData = res?.data || {}
            } catch (e) {
                this.invoiceData = {}
                this.$toast.error('Failed to load invoice')
            } finally {
                this.invoiceLoading = false
            }
        },
        closeInvoiceModal() {
            this.showInvoiceModal = false
            this.invoiceLoading = false
            this.invoiceData = {}
        },
        async deleteInvoiceDetail(fees) {
            if (!fees?.id) return
            if (confirm('Are you sure want to delete?')) {
                this.invoiceLoading = true
                try {
                    const res = await window.axios.get(`/admin/hostel-fees-delete/${fees.id}`)
                    this.$toast.success(res?.data?.message || 'Delete Successfully!')
                    await this.openInvoiceModal(fees.hostel_payment_id)
                    await this.loadPayments(this.meta.current_page || 1)
                } catch (e) {
                    this.$toast.error('Something went wrong')
                } finally {
                    this.invoiceLoading = false
                }
            }
        },
        paidInvoice(payment) {
            if (confirm('Are you sure want to paid?')) {
                this.loading = true
                const data = {
                    tran_id: payment.invoice_number,
                    card_type: 'SSL',
                    bank_tran_id: 'manually paid',
                }
                window.axios.put(`/admin/hostelPayment/${payment.id}`, data)
                    .then((res) => {
                        this.$toast.success(res.data.message)
                        this.closeInvoiceModal()
                        this.loadPayments(this.meta.current_page || 1)
                    })
                    .catch(() => {
                        this.$toast.error('Something went wrong')
                    })
                    .finally(() => {
                        this.loading = false
                    })
            }
        },
        deletePayment(id) {
            if (confirm('Are you sure you want to delete this payment?')) {
                this.loading = true
                window.axios.delete(`/admin/hostelPayment/${id}`)
                    .then((res) => {
                        this.$toast.success(res?.data?.message || 'Payment deleted successfully')
                        this.loadPayments(this.meta.current_page || 1)
                    })
                    .catch(() => {
                        this.$toast.error('Failed to delete payment')
                    })
                    .finally(() => {
                        this.loading = false
                    })
            }
        },
        async bulkReceivePayment() {
            if (confirm(`Are you sure want to receive ${this.selectedPayments.length} invoices?`)) {
                this.loading = true
                try {
                    await Promise.all(
                        this.selectedPayments.map((id) =>
                            window.axios.put(`/admin/hostelPayment/${id}`, {
                                tran_id: '',
                                card_type: 'SSL',
                                bank_tran_id: 'manually paid',
                            })
                        )
                    )
                    this.$toast.success('Paid Successfully!')
                    this.selectedPayments = []
                    await this.loadPayments(this.meta.current_page || 1)
                } catch (e) {
                    this.$toast.error('Something went wrong')
                } finally {
                    this.loading = false
                }
            }
        },
        bulkDelete() {
            if (confirm(`Are you sure you want to delete ${this.selectedPayments.length} payments?`)) {
                this.loading = true
                Promise.all(this.selectedPayments.map((id) => window.axios.delete(`/admin/hostelPayment/${id}`)))
                    .then(() => {
                        this.$toast.success(`Bulk deleted ${this.selectedPayments.length} payments`)
                        this.selectedPayments = []
                        this.loadPayments(this.meta.current_page || 1)
                    })
                    .catch(() => {
                        this.$toast.error('Failed to delete payments')
                    })
                    .finally(() => {
                        this.loading = false
                    })
            }
        },
        formatMoney(amount) {
            return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount || 0)
        },
        formatDate(date) {
            if (!date) return '--'
            return new Date(date).toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: '2-digit' 
            })
        },
        formatMonth(date) {
            if (!date) return '--'
            return new Date(date).toLocaleDateString('en-US', { month: 'long' })
        },
        getStatusClass(status) {
            const classes = {
                success: 'bg-emerald-50 text-emerald-700',
                pending: 'bg-amber-50 text-amber-700',
                failed: 'bg-red-50 text-red-700'
            }
            return classes[status] || 'bg-slate-50 text-slate-700'
        }
    }
}
</script>
