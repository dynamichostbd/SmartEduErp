<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Admin Admit Card (Four in One)</div>
                    <div class="mt-1 text-sm text-slate-600">Download admit cards (4 per page)</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="loading || !rows.length" @click="downloadBulk">
                        Download Admit Cards
                    </button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <form class="grid grid-cols-1 gap-4 lg:grid-cols-12" @submit.prevent="search(true)">
                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Status</div>
                    <select v-model="filters.status" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option value="active">Active</option>
                        <option value="deactive">Deactive</option>
                    </select>
                </div>

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
                    <div class="text-xs font-semibold text-slate-600">Student Type</div>
                    <select v-model="filters.student_type" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="t in studentTypes" :key="'t-' + t" :value="t">{{ t }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Gender</div>
                    <select v-model="filters.gender" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Exam Name</div>
                    <input v-model="filters.exam_name" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="Exam Name" />
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Issue Date</div>
                    <input v-model="filters.issue_date" type="date" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Field</div>
                    <select v-model="filters.field_name" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="mobile">Mobile</option>
                        <option value="student_id">Software ID</option>
                        <option value="name">Name</option>
                        <option value="reg_no">Reg No</option>
                        <option value="college_roll">College Roll</option>
                        <option value="admission_id">Admission ID</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Value</div>
                    <input v-model="filters.value" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="Search" />
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Pagination</div>
                    <select v-model.number="filters.pagination" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option :value="10">10</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                        <option :value="100">100</option>
                    </select>
                </div>

                <div class="lg:col-span-2 flex items-end">
                    <button type="submit" class="h-9 w-full rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800" :disabled="loading">
                        {{ loading ? '...' : 'Search' }}
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="text-sm text-slate-600">{{ meta.total || 0 }} student(s) found</div>
            <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Software ID</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Student</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Admission ID</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Roll</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Reg No</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Session</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Dept</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Level / Class</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="r in rows" :key="'r-' + r.id">
                            <td class="px-3 py-2">{{ r.student_id }}</td>
                            <td class="px-3 py-2">{{ r.name }}</td>
                            <td class="px-3 py-2">{{ r.admission_id }}</td>
                            <td class="px-3 py-2">{{ r.college_roll }}</td>
                            <td class="px-3 py-2">{{ r.reg_no }}</td>
                            <td class="px-3 py-2">{{ r.academic_session_name || '--' }}</td>
                            <td class="px-3 py-2">{{ r.department_name || '--' }}</td>
                            <td class="px-3 py-2">{{ (r.academic_qualification_name || '') + (r.academic_class_name ? ' (' + r.academic_class_name + ')' : '') }}</td>
                            <td class="px-3 py-2 text-center">{{ r.status }}</td>
                        </tr>
                        <tr v-if="!rows.length">
                            <td colspan="9" class="px-3 py-10 text-center text-slate-500">No data found</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="meta && meta.last_page > 1" class="mt-4 flex flex-wrap items-center justify-between gap-2">
                <div class="text-sm text-slate-600">Showing {{ meta.from || 0 }}-{{ meta.to || 0 }} of {{ meta.total || 0 }}</div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm hover:bg-slate-50" :disabled="Number(meta.current_page || 1) <= 1" @click="goPage(Number(meta.current_page || 1) - 1)">
                        Prev
                    </button>
                    <button v-for="p in pageNumbers" :key="'p-' + p" type="button" class="h-9 rounded-lg border px-3 text-sm" :class="p === Number(meta.current_page || 1) ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'" @click="goPage(p)">
                        {{ p }}
                    </button>
                    <button type="button" class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm hover:bg-slate-50" :disabled="Number(meta.current_page || 1) >= Number(meta.last_page || 1)" @click="goPage(Number(meta.current_page || 1) + 1)">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AdminAdmitCardFourInOne',
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
            meta: { current_page: 1, last_page: 1, total: 0, from: 0, to: 0 },
            filters: {
                pagination: 10,
                page: 1,
                field_name: 'mobile',
                value: '',
                academic_session_id: '',
                academic_class_id: '',
                department_id: '',
                academic_qualification_id: '',
                student_type: '',
                hostel_id: '',
                subject_ids: [],
                fourth_subject_id: '',
                gender: '',
                status: 'active',
                exam_name: '',
                issue_date: '',
            },
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
        studentTypes() {
            const list = this.systems?.global?.student_types
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
        pageNumbers() {
            const cur = Number(this.meta.current_page || 1)
            const last = Number(this.meta.last_page || 1)
            const start = Math.max(1, cur - 2)
            const end = Math.min(last, cur + 2)
            const out = []
            for (let i = start; i <= end; i++) out.push(i)
            return out
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
    mounted() {
        this.search(true)
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
        goPage(p) {
            const page = Math.max(1, Number(p || 1))
            this.filters.page = page
            this.search(false)
        },
        async search(resetPage) {
            if (resetPage) this.filters.page = 1
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get('/admin/admin-admit-card-four-in-one', { params: this.filters })
                const payload = res?.data || {}
                this.rows = Array.isArray(payload?.data) ? payload.data : []
                this.meta = {
                    current_page: payload?.current_page || 1,
                    last_page: payload?.last_page || 1,
                    from: payload?.from ?? 0,
                    to: payload?.to ?? 0,
                    total: payload?.total ?? 0,
                    per_page: payload?.per_page || this.filters.pagination,
                }
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load students.'
                this.rows = []
            } finally {
                this.loading = false
            }
        },
        downloadBulk() {
            if (!confirm('Are you sure you want to download admit cards for the current page?')) return

            const payload = { ...this.filters, page: this.meta?.current_page || this.filters.page || 1 }
            const form = document.createElement('form')
            form.method = 'GET'
            form.action = '/admin/admit-card-four-in-one-bulk'

            const input = document.createElement('input')
            input.type = 'hidden'
            input.name = 'search_params'
            input.value = JSON.stringify(payload)
            form.appendChild(input)

            document.body.appendChild(form)
            form.submit()
            document.body.removeChild(form)
        },
    },
}
</script>
