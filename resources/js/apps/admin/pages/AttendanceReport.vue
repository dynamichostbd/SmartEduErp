<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Attendance Report</div>
                    <div class="mt-1 text-sm text-slate-600">Students attendance summary</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goBack">
                        Back
                    </button>
                    <button type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" :disabled="loading" @click="search">
                        {{ loading ? 'Loading...' : 'Search' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">From Date <span class="text-rose-600">*</span></div>
                    <input v-model="filters.from_date" type="date" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">To Date <span class="text-rose-600">*</span></div>
                    <input v-model="filters.to_date" type="date" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Session <span class="text-rose-600">*</span></div>
                    <select v-model="filters.academic_session_id" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="s in sessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Academic Level <span class="text-rose-600">*</span></div>
                    <select v-model="filters.academic_qualification_id" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Department <span class="text-rose-600">*</span></div>
                    <select v-model="filters.department_id" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="d in filteredDepartments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Class <span class="text-rose-600">*</span></div>
                    <select v-model="filters.academic_class_id" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="c in filteredClasses" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Search By</div>
                    <select v-model="filters.field_name" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select One</option>
                        <option value="student_id">Software ID</option>
                        <option value="name">Name</option>
                        <option value="mobile">Mobile</option>
                        <option value="admission_id">Admission ID</option>
                        <option value="college_roll">College Roll</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Value</div>
                    <input v-model="filters.value" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm font-semibold text-slate-900">Students Attendance Summary</div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="text-sm text-slate-700">
                        Total Class:
                    </div>
                    <input v-model.number="totalClass" type="number" min="0" class="h-9 w-24 rounded-lg border border-slate-200 bg-white px-3 text-center text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />

                    <button type="button" class="h-9 rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="!studentsComputed.length" @click="printReport">
                        Print
                    </button>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200" id="printArea">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">#</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Software ID</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Student Name</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Mobile</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">
                                <button type="button" class="inline-flex items-center gap-1" @click="toggleSort('roll')">
                                    College Roll
                                    <span class="text-xs text-slate-400">{{ sortLabel('roll') }}</span>
                                </button>
                            </th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Total Class</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Total Present</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Total Absent</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">
                                <button type="button" class="inline-flex items-center gap-1" @click="toggleSort('percentage')">
                                    Present Percentage (%)
                                    <span class="text-xs text-slate-400">{{ sortLabel('percentage') }}</span>
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="(s, idx) in studentsComputed" :key="'r-' + String(s.student_id) + '-' + idx">
                            <td class="px-3 py-2 text-center text-slate-600">{{ idx + 1 }}</td>
                            <td class="px-3 py-2 text-slate-800">{{ s.student_id }}</td>
                            <td class="px-3 py-2 text-slate-800">{{ s.name }}</td>
                            <td class="px-3 py-2 text-slate-800">{{ s.mobile }}</td>
                            <td class="px-3 py-2 text-center text-slate-800">{{ s.college_roll }}</td>
                            <td class="px-3 py-2 text-center text-slate-800"><b>{{ totalClass }}</b></td>
                            <td class="px-3 py-2 text-center text-emerald-700"><b>{{ s.total_present }}</b></td>
                            <td class="px-3 py-2 text-center text-rose-700">
                                <b>{{ totalClass > 0 ? (Number(totalClass) - Number(s.total_present || 0)) : 0 }}</b>
                            </td>
                            <td class="px-3 py-2 text-center text-amber-700">
                                <b>{{ totalClass > 0 ? percentage(s.total_present) : '0.00' }}%</b>
                            </td>
                        </tr>
                        <tr v-if="!studentsComputed.length">
                            <td colspan="9" class="px-3 py-10 text-center text-slate-500">No Students Found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AttendanceReport',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        const today = new Date()
        const y = today.getFullYear()
        const m = today.getMonth()
        const start = new Date(y, m, 1).toISOString().slice(0, 10)
        const end = new Date(y, m + 1, 0).toISOString().slice(0, 10)

        return {
            loading: false,
            error: '',
            totalClass: 0,
            sortConfig: {
                field: null,
                direction: 'desc',
            },
            filters: {
                from_date: start,
                to_date: end,
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
                field_name: 'mobile',
                value: '',
            },
            rows: [],
        }
    },
    computed: {
        sessions() {
            const list = this.systems?.global?.academic_sessions
            return Array.isArray(list) ? list : []
        },
        sessionsSorted() {
            const list = Array.isArray(this.sessions) ? [...this.sessions] : []
            return list.sort((a, b) => this.sessionSortKey(b) - this.sessionSortKey(a))
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
        departmentQualidactions() {
            const list = this.systems?.global?.department_qualidactions || this.systems?.global?.department_qualifications
            return Array.isArray(list) ? list : []
        },
        filteredDepartments() {
            const qid = this.filters?.academic_qualification_id
            if (!qid) return this.departments
            const allowed = new Set(this.departmentQualidactions.filter((r) => String(r.academic_qualification_id) === String(qid)).map((r) => String(r.department_id)))
            return this.departments.filter((d) => allowed.has(String(d.id)))
        },
        filteredClasses() {
            const qid = this.filters?.academic_qualification_id
            if (!qid) return this.classes
            return this.classes.filter((c) => String(c.academic_qualification_id) === String(qid))
        },
        studentsComputed() {
            let arrayList = Array.isArray(this.rows) ? [...this.rows] : []

            if (this.sortConfig.field === 'percentage') {
                arrayList.sort((a, b) => {
                    const pA = this.getPercent(a)
                    const pB = this.getPercent(b)
                    return this.sortConfig.direction === 'asc' ? pA - pB : pB - pA
                })
            } else if (this.sortConfig.field === 'roll') {
                arrayList.sort((a, b) => {
                    const rA = parseInt(a.college_roll || 0) || 0
                    const rB = parseInt(b.college_roll || 0) || 0
                    return this.sortConfig.direction === 'asc' ? rA - rB : rB - rA
                })
            }

            return arrayList
        },
    },
    watch: {
        'filters.academic_qualification_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.filters.department_id = ''
                this.filters.academic_class_id = ''
            }
        },
    },
    methods: {
        sessionSortKey(s) {
            const name = String(s?.name || '')
            const m = name.match(/(\d{4})\s*[-/]\s*(\d{4})/)
            if (m) {
                const start = Number(m[1] || 0)
                const end = Number(m[2] || 0)
                return start * 10000 + end
            }
            const n = Number(name)
            if (!Number.isNaN(n) && Number.isFinite(n)) return n
            const id = Number(s?.id || 0)
            return Number.isFinite(id) ? id : 0
        },
        goBack() {
            window.location.href = '/admin/dashboard'
        },
        normalizeRows(list) {
            const arr = Array.isArray(list) ? list : []
            return arr.map((r) => ({
                student_id: r.student_id,
                name: r.name,
                mobile: r.mobile,
                college_roll: r.college_roll,
                admission_id: r.admission_id,
                total_present: Number(r.total_present || 0),
            }))
        },
        toggleSort(field) {
            if (this.sortConfig.field !== field) {
                this.sortConfig.field = field
                this.sortConfig.direction = 'desc'
                return
            }
            this.sortConfig.direction = this.sortConfig.direction === 'asc' ? 'desc' : 'asc'
        },
        sortLabel(field) {
            if (this.sortConfig.field !== field) return ''
            return this.sortConfig.direction === 'asc' ? '↑' : '↓'
        },
        getPercent(row) {
            const total = parseInt(this.totalClass || 0) || 0
            if (total === 0) return 0
            return (Number(row?.total_present || 0) * 100) / total
        },
        percentage(totalPresent) {
            const total = parseInt(this.totalClass || 0) || 0
            if (total === 0) return '0.00'
            return ((Number(totalPresent || 0) * 100) / total).toFixed(2)
        },
        async search() {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get('/admin/attendance-report/list', { params: this.filters })
                this.rows = this.normalizeRows(res?.data)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load report.'
                this.rows = []
            } finally {
                this.loading = false
            }
        },
        printReport() {
            const el = document.getElementById('printArea')
            if (!el) return
            const html = el.outerHTML
            const w = window.open('', 'print', 'height=700,width=1000')
            if (!w) return
            w.document.write('<html><head><title>Attendance Report</title>')
            w.document.write('<style>body{font-family: Arial, sans-serif; padding: 20px;} table{width:100%; border-collapse: collapse;} th,td{border:1px solid #333; padding:6px; font-size:12px;} th{text-align:left;} .text-center{text-align:center;}</style>')
            w.document.write('</head><body>')
            w.document.write(html)
            w.document.write('</body></html>')
            w.document.close()
            w.focus()
            w.print()
            w.close()
        },
    },
}
</script>
