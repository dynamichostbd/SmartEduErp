<template>
    <div class="min-h-screen bg-slate-50">
        <div class="mx-auto">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-slate-900">Monthly Hostel Payment Report</h1>
                <p class="text-slate-600">Search and view monthly hostel payment reports</p>
            </div>

            <!-- Search Filters -->
            <div class="border border-slate-300 bg-white p-5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Financial Year</label>
                        <select v-model="filters.financial_year_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                            <option value="">All Years</option>
                            <option v-for="session in academicSessions" :key="session.id" :value="session.id">{{ session.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Payable Month</label>
                        <select v-model="filters.payable_month" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                            <option value="">All Months</option>
                            <option v-for="month in months" :key="month.key" :value="month.key">{{ month.name }}</option>
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
                        <label class="text-xs font-semibold text-slate-600">Account Head</label>
                        <select v-model="filters.account_head_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                            <option value="">All Heads</option>
                            <option v-for="head in accountHeads" :key="head.id" :value="head.id">{{ head.name }}</option>
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
                        <label class="text-xs font-semibold text-slate-600">Search Field</label>
                        <select v-model="filters.field_name" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" :disabled="loading">
                            <option value="">Select Field</option>
                            <option value="student_id">Software ID</option>
                            <option value="name">Name</option>
                            <option value="mobile">Mobile</option>
                            <option value="admission_id">Admission ID</option>
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
                </div>
            </div>

            <!-- Students Table -->
            <div class="mt-6 border border-slate-300 bg-white p-5">
                <div class="mb-4 flex justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">Students</h2>
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
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Student Info</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Academic Info</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Financial Year</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Hostel Name</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Invoice Number</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Purpose</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <tr v-for="(payment, index) in payments" :key="payment.id" :class="index % 2 === 0 ? 'bg-white' : 'bg-slate-50'">
                                <td class="border border-slate-300 px-1 py-1 text-sm text-slate-800">
                                    <div class="font-medium">{{ payment.student_id }}</div>
                                    <div>{{ payment.student_name }}</div>
                                    <div class="text-xs text-slate-500">{{ payment.mobile }}</div>
                                </td>
                                <td class="border border-slate-300 px-1 py-1 text-sm text-slate-800">
                                    <div>{{ payment.session_name }}</div>
                                    <div>{{ payment.department_name }}</div>
                                    <div class="text-xs text-slate-500">{{ payment.academic_level }} ({{ payment.academic_class }})</div>
                                </td>
                                <td class="border border-slate-300 px-1 py-1 text-sm text-slate-800">{{ payment.financial_year }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-sm text-slate-800">{{ payment.hostel_name }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-sm text-slate-800">{{ payment.invoice_number }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-sm text-slate-800">
                                    <div>{{ payment.head_name }}</div>
                                    <div class="text-xs text-slate-500">{{ formatMonth(payment.invoice_date) }}</div>
                                </td>
                                <td class="border border-slate-300 px-1 py-1 text-sm text-center text-slate-800 font-medium">{{ formatMoney(payment.amount) }}</td>
                            </tr>
                        </tbody>
                    </table>
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
    </div>
</template>

<script>
export default {
    name: 'HostelPaymentMonthly',
    props: ['systems'],
    data() {
        return {
            loading: false,
            searchTimer: null,
            payments: [],
            meta: { current_page: 1, last_page: 1, from: 0, to: 0, total: 0 },
            filters: {
                pagination: 10,
                field_name: 'mobile',
                academic_class_id: '',
                financial_year_id: '',
                academic_session_id: '',
                department_id: '',
                academic_qualification_id: '',
                account_head_id: '',
                hostel_id: '',
                payable_month: '',
                status: 'success'
            }
        }
    },
    computed: {
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
        hostels() {
            return this.systems?.global?.hostels || []
        },
        months() {
            return [
                { key: '01', name: 'January' },
                { key: '02', name: 'February' },
                { key: '03', name: 'March' },
                { key: '04', name: 'April' },
                { key: '05', name: 'May' },
                { key: '06', name: 'June' },
                { key: '07', name: 'July' },
                { key: '08', name: 'August' },
                { key: '09', name: 'September' },
                { key: '10', name: 'October' },
                { key: '11', name: 'November' },
                { key: '12', name: 'December' }
            ]
        },
        totalAmount() {
            return this.payments.reduce((sum, payment) => sum + parseFloat(payment.amount || 0), 0)
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
        'filters.financial_year_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad(true)
        },
        'filters.payable_month'(next, prev) {
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
        'filters.account_head_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad(true)
        },
        'filters.hostel_id'(next, prev) {
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
                const response = await window.axios.get('/admin/hostelPayment-monthly', { params: { ...this.filters, page } })
                const payload = response?.data || {}
                this.payments = payload.data || []
                this.meta = {
                    current_page: payload.current_page || 1,
                    last_page: payload.last_page || 1,
                    from: payload.from || 0,
                    to: payload.to || 0,
                    total: payload.total || 0,
                }
            } catch (error) {
                this.$toast.error('Failed to load monthly payments')
            } finally {
                this.loading = false
            }
        },
        formatMoney(amount) {
            return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount || 0)
        },
        formatMonth(date) {
            if (!date) return '--'
            return new Date(date).toLocaleDateString('en-US', { month: 'long' })
        }
    }
}
</script>
