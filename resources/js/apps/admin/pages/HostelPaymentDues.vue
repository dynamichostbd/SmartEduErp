<template>
    <div class="min-h-screen bg-slate-50">
        <div class="mx-auto">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-slate-900">Hostel Dues Report</h1>
                <p class="text-slate-600">Search and view hostel dues reports</p>
            </div>

            <!-- Search Filters -->
            <div class="border border-slate-300 bg-white p-5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Financial Year *</label>
                        <select v-model="filters.financial_year_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                            <option value="">All Years</option>
                            <option v-for="session in academicSessions" :key="session.id" :value="session.id">{{ session.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Account Head</label>
                        <select v-model="filters.account_head_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                            <option value="">All Heads</option>
                            <option v-for="(name, id) in accountHeads" :key="id" :value="id">{{ name }}</option>
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
                        <label class="text-xs font-semibold text-slate-600">Hostel *</label>
                        <select v-model="filters.hostel_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                            <option value="">All Hostels</option>
                            <option v-for="h in hostels" :key="h.id" :value="h.id">{{ h.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Month</label>
                        <select v-model="filters.dues_month" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                            <option value="">All Months</option>
                            <option v-for="month in months" :key="month.key" :value="month.key">{{ month.name }}</option>
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
                        <input v-model="filters.value" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" @keydown.enter.prevent="loadDues" />
                    </div>
                    <div class="flex items-end">
                        <button @click="loadDues" :disabled="loading" class="h-9 w-full rounded-sm bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50">
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
                        <span class="text-sm text-slate-600">Total: {{ dues.length }}</span>
                        <span class="text-sm text-slate-600">|</span>
                        <span class="text-sm text-slate-600">Total Dues: {{ formatMoney(totalDues) }}</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse border border-slate-300">
                        <thead class="bg-emerald-100">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-700">
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Student Info</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Admission/Software</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Session</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Academic Info</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Hostel Name</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">Dues Amount</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <tr v-for="(due, index) in dues" :key="due.id" :class="index % 2 === 0 ? 'bg-white' : 'bg-slate-50'">
                                <td class="border border-slate-300 px-1 py-1 text-sm text-slate-800">
                                    <a href="#" @click="viewDuesModal(due)" class="text-emerald-600 hover:text-emerald-800">
                                        <div>{{ due.student_name }}</div>
                                        <div class="text-xs text-slate-500">{{ due.mobile }}</div>
                                    </a>
                                </td>
                                <td class="border border-slate-300 px-1 py-1 text-sm text-slate-800">
                                    <a href="#" @click="viewDuesModal(due)" class="text-emerald-600 hover:text-emerald-800">
                                        <div class="font-medium">{{ due.student_id }}</div>
                                        <div class="text-xs text-slate-500">{{ due.admission_id }}</div>
                                    </a>
                                </td>
                                <td class="border border-slate-300 px-1 py-1 text-sm text-slate-800">{{ due.academic_session?.name }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-sm text-slate-800">
                                    <div>{{ due.department?.name }}</div>
                                    <div>{{ due.qualification?.name }}</div>
                                    <div class="text-xs text-slate-500">({{ due.academic_class?.name }})</div>
                                </td>
                                <td class="border border-slate-300 px-1 py-1 text-sm text-slate-800">{{ due.hostel?.name }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-sm text-center text-slate-800 font-medium text-rose-700">{{ formatMoney(due.total_dues) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- View Dues Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click="closeModal">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="w-full max-w-2xl rounded-sm bg-white p-6" @click.stop>
                <div class="mb-4 flex justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">Student Dues List</h3>
                    <button @click="closeModal" class="text-slate-400 hover:text-slate-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="mb-4">
                    <span class="font-semibold">Name:</span> {{ selectedStudent.student_name }}
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse border border-slate-300">
                        <thead class="bg-emerald-100">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-700">
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">SL</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Year</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Month</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Purpose</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <tr v-if="selectedStudent.dues_fees && selectedStudent.dues_fees.length > 0" v-for="(fee, key) in selectedStudent.dues_fees" :key="key">
                                <td class="border border-slate-300 px-1 py-1 text-center text-sm">{{ key + 1 }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-sm">{{ formatYear(fee.date) }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-sm">{{ formatMonth(fee.date) }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-sm">{{ fee.head?.name || '' }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-center text-sm font-medium">{{ formatMoney(fee.amount) }}</td>
                            </tr>
                            <tr v-else>
                                <td colspan="5" class="border border-slate-300 px-1 py-1 text-center text-sm text-slate-500">
                                    No hostel fees have been generated yet
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'HostelPaymentDues',
    props: ['systems'],
    data() {
        return {
            loading: false,
            searchTimer: null,
            dues: [],
            showModal: false,
            selectedStudent: {},
            filters: {
                field_name: 'mobile',
                value: '',
                academic_class_id: '',
                academic_session_id: '',
                department_id: '',
                academic_qualification_id: '',
                account_head_id: '',
                hostel_id: '',
                dues_month: '',
                financial_year_id: ''
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
            if (!raw) return {}
            if (Array.isArray(raw)) {
                return raw.reduce((acc, r) => {
                    if (r && r.id !== undefined) acc[r.id] = r.name
                    return acc
                }, {})
            }
            if (typeof raw === 'object') return raw
            return {}
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
        totalDues() {
            return this.dues.reduce((sum, due) => sum + parseFloat(due.total_dues || 0), 0)
        }
    },
    watch: {
        'filters.academic_qualification_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.filters.department_id = ''
                this.filters.academic_class_id = ''
                this.scheduleLoad()
            }
        },
        'filters.financial_year_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad()
        },
        'filters.account_head_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad()
        },
        'filters.academic_session_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad()
        },
        'filters.department_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad()
        },
        'filters.academic_class_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad()
        },
        'filters.hostel_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad()
        },
        'filters.dues_month'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad()
        },
        'filters.field_name'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad()
        },
        'filters.value'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad()
        },
    },
    methods: {
        scheduleLoad() {
            if (this.searchTimer) clearTimeout(this.searchTimer)
            this.searchTimer = setTimeout(() => {
                this.loadDues(true)
            }, 250)
        },
        async loadDues(silent = false) {
            if (!this.filters.hostel_id || !this.filters.financial_year_id) {
                if (!silent) {
                    if (!this.filters.hostel_id) this.$toast.error('Hostel is required')
                    else this.$toast.error('Financial Year is required')
                }
                this.dues = []
                return
            }

            this.loading = true
            try {
                const response = await window.axios.get('/admin/hostelPayment-dues', { params: this.filters })
                this.dues = response.data.data || []
            } catch (error) {
                if (!silent) this.$toast.error('Failed to load dues')
            } finally {
                this.loading = false
            }
        },
        viewDuesModal(student) {
            this.selectedStudent = student
            this.showModal = true
        },
        closeModal() {
            this.showModal = false
            this.selectedStudent = {}
        },
        formatMoney(amount) {
            return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount || 0)
        },
        formatMonth(date) {
            if (!date) return '--'
            return new Date(date).toLocaleDateString('en-US', { month: 'long' })
        },
        formatYear(date) {
            if (!date) return '--'
            return new Date(date).getFullYear()
        }
    }
}
</script>
