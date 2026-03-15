<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Attendance Sheet</div>
                    <div class="mt-1 text-sm text-slate-600">Students attendance sheet</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goBack">
                        Back
                    </button>
                    <button type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" :disabled="loading" @click="search">
                        {{ loading ? 'Loading...' : 'Search' }}
                    </button>
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="!hasData" @click="printReport">
                        Print
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
                    <input v-model="filters.from_date" type="date" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">To Date <span class="text-rose-600">*</span></div>
                    <input v-model="filters.to_date" type="date" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Session <span class="text-rose-600">*</span></div>
                    <select v-model="filters.academic_session_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="s in sessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Academic Level <span class="text-rose-600">*</span></div>
                    <select v-model="filters.academic_qualification_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Department <span class="text-rose-600">*</span></div>
                    <select v-model="filters.department_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="d in filteredDepartments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Class <span class="text-rose-600">*</span></div>
                    <select v-model="filters.academic_class_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="c in filteredClasses" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="overflow-x-auto rounded-xl border border-slate-200" id="printArea">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">College Roll</th>
                            <th
                                v-for="d in dateKeys"
                                :key="'d-' + d"
                                class="px-2 py-2 text-center font-semibold text-slate-700"
                                style="font-size: 11px"
                            >
                                {{ dayOfMonth(d) }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="(s, idx) in students" :key="'s-' + String(s.student_id) + '-' + idx">
                            <td class="px-3 py-2 text-slate-800">{{ s.college_roll || '--' }}</td>
                            <td
                                v-for="d in dateKeys"
                                :key="'c-' + String(s.student_id) + '-' + d"
                                class="px-2 py-2 text-center"
                                style="font-size: 11px"
                            >
                                <span v-if="(s.attendance_data || []).includes(d)" class="font-semibold text-emerald-700">P</span>
                                <span v-else class="font-semibold text-rose-700">A</span>
                            </td>
                        </tr>
                        <tr v-if="!students.length">
                            <td :colspan="1 + dateKeys.length" class="px-3 py-10 text-center text-slate-500">No Students Found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AttendanceSheet',
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
            filters: {
                from_date: start,
                to_date: end,
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
            },
            students: [],
            dates: {},
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
        dateKeys() {
            const obj = this.dates && typeof this.dates === 'object' ? this.dates : {}
            return Object.keys(obj)
        },
        hasData() {
            return this.students.length > 0 && this.dateKeys.length > 0
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
        dayOfMonth(isoDate) {
            const d = String(isoDate || '')
            return d.length >= 10 ? d.slice(8, 10) : d
        },
        async search() {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get('/admin/attendance-sheet/list', { params: this.filters })
                this.students = Array.isArray(res?.data?.students) ? res.data.students : []
                this.dates = res?.data?.dates && typeof res.data.dates === 'object' ? res.data.dates : {}
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load attendance sheet.'
                this.students = []
                this.dates = {}
            } finally {
                this.loading = false
            }
        },
        printReport() {
            const el = document.getElementById('printArea')
            if (!el) return
            const html = el.outerHTML
            const w = window.open('', 'print', 'height=700,width=1100')
            if (!w) return
            w.document.write('<html><head><title>Attendance Sheet</title>')
            w.document.write('<style>body{font-family: Arial, sans-serif; padding: 20px;} table{width:100%; border-collapse: collapse;} th,td{border:1px solid #333; padding:4px; font-size:11px;} th{text-align:center;} td{text-align:center;} td:first-child, th:first-child{text-align:left;}</style>')
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
