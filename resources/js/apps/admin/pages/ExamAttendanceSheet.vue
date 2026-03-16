<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Exam Attendance Sheet</div>
                    <div class="mt-1 text-sm text-slate-600">Printable exam attendance sheet</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800"
                        :disabled="loading || !rows.length"
                        @click="printPage"
                    >
                        Print
                    </button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <form class="grid grid-cols-1 gap-4 lg:grid-cols-12" @submit.prevent="search()">
                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Total Exam Paper</div>
                    <input
                        v-model.number="empty_col"
                        type="number"
                        min="5"
                        max="20"
                        class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                        placeholder="10"
                    />
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Students/Page</div>
                    <input
                        v-model.number="per_page"
                        type="number"
                        min="15"
                        max="15"
                        class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                        placeholder="15"
                    />
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Pagination</div>
                    <input
                        v-model.number="filters.pagination"
                        type="number"
                        class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                        placeholder="1000"
                        @change="search"
                    />
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Exam Name</div>
                    <input
                        v-model="exam_name"
                        type="text"
                        class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                        placeholder="Type Exam Name Here!"
                    />
                </div>

                <div class="lg:col-span-12"></div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Session</div>
                    <select v-model="filters.academic_session_id" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="s in sessions" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
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
                    <div class="text-xs font-semibold text-slate-600">Academic Class</div>
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

                <div class="lg:col-span-1">
                    <div class="text-xs font-semibold text-slate-600">Gender</div>
                    <select v-model="filters.gender" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>

                <div class="lg:col-span-1">
                    <div class="text-xs font-semibold text-slate-600">Status</div>
                    <select v-model="filters.status" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option value="active">Active</option>
                        <option value="deactive">Deactive</option>
                    </select>
                </div>

                <div class="lg:col-span-2 flex items-end">
                    <button
                        type="submit"
                        class="h-9 w-full rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700"
                        :disabled="loading"
                    >
                        {{ loading ? '...' : 'Search' }}
                    </button>
                </div>
            </form>
        </div>

        <div id="print-area" class="rounded-2xl border border-slate-200 bg-white p-5">
            <div v-if="!chunkedRows.length" class="text-center p-3 text-sm text-slate-600">No data to display.</div>

            <div
                v-else
                v-for="(chunk, pageIndex) in chunkedRows"
                :key="'page-' + pageIndex"
                class="print-page"
                :style="pageIndex > 0 ? 'page-break-before: always; margin-top: 2rem;' : ''"
            >
                <div class="text-center mb-2">
                    <div style="font-size: 24px;" class="font-semibold">
                        {{ systems?.site?.college_name || '' }}
                    </div>
                    <div style="font-size: 20px; margin-top: -6px;" class="font-semibold">
                        {{ exam_name || ' ' }}
                    </div>
                    <div class="bg-slate-900 text-white py-1 mt-1 text-sm font-semibold">
                        Student Exam Attendance Sheet
                    </div>
                </div>

                <div class="flex justify-between mb-2 text-xs font-semibold px-1">
                    <span>Session: {{ findName('academic_sessions', filters.academic_session_id) }}</span>
                    <span>Academic Level: {{ findName('academic_qualifications', filters.academic_qualification_id) }}</span>
                    <span>Dept/Group: {{ findName('departments', filters.department_id) }}</span>
                    <span>Academic Class: {{ findName('academic_classes', filters.academic_class_id) }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm">
                        <thead>
                            <tr>
                                <th rowspan="2" class="border border-black px-2 py-1 text-center align-middle" style="width: 4%">#</th>
                                <th rowspan="2" class="border border-black px-2 py-1 text-center align-middle" style="width: 9%">College Roll</th>
                                <th rowspan="2" class="border border-black p-0 align-middle" style="width: 25%">
                                    <div class="flex" style="height: 60px;">
                                        <div class="flex items-center justify-center flex-grow px-2">Student Name</div>
                                        <div class="flex flex-col" style="width: 110px; border-left: 1px solid #000;">
                                            <div class="flex items-center justify-start pl-2 flex-1" style="border-bottom: 1px solid #000;">Exam Title</div>
                                            <div class="flex items-center justify-start pl-2 flex-1">Exam Date</div>
                                        </div>
                                    </div>
                                </th>
                                <th v-for="n in parseInt(empty_col)" :key="'h1-' + n" class="border border-black px-2 py-1"></th>
                            </tr>
                            <tr>
                                <th v-for="n in parseInt(empty_col)" :key="'h2-' + n" class="border border-black px-2 py-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(student, idx) in chunk" :key="'s-' + student.id">
                                <td class="border border-black px-2 py-1 text-center">{{ pageIndex * per_page + idx + 1 }}</td>
                                <td class="border border-black px-2 py-1 text-center">{{ student.college_roll }}</td>
                                <td class="border border-black px-2 py-1">{{ student.name }}</td>
                                <td v-for="n in parseInt(empty_col)" :key="'b-' + student.id + '-' + n" class="border border-black px-2 py-1"></td>
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
    name: 'ExamAttendanceSheet',
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
            empty_col: 7,
            per_page: 15,
            exam_name: '',
            rows: [],
            filters: {
                pagination: 1000,
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
                status: 'active',
                gender: '',
            },
        }
    },
    computed: {
        sessions() {
            const list = this.systems?.global?.academic_sessions
            return Array.isArray(list) ? list : []
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
        chunkedRows() {
            const list = Array.isArray(this.rows) ? this.rows : []
            const size = parseInt(this.per_page) || 15
            if (!list.length) return []
            const out = []
            for (let i = 0; i < list.length; i += size) out.push(list.slice(i, i + size))
            return out
        },
    },
    mounted() {
        this.search()
    },
    methods: {
        findName(key, id) {
            if (!id) return ''
            const list = this.systems?.global?.[key]
            if (!Array.isArray(list)) return ''
            const item = list.find((r) => String(r.id) === String(id))
            return item?.name || ''
        },
        async search() {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get('/admin/exam-attendance-sheet/list', { params: this.filters })
                const payload = res?.data || {}
                this.rows = Array.isArray(payload?.data) ? payload.data : Array.isArray(payload) ? payload : []
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load data.'
                this.rows = []
            } finally {
                this.loading = false
            }
        },
        printPage() {
            const printContent = document.getElementById('print-area')?.innerHTML || ''
            const printWindow = window.open('', '_blank')
            if (!printWindow) return

            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Print - Student Exam Attendance Sheet</title>
                    <style>
                        body { margin: 0; padding: 0; font-family: Arial, sans-serif; background: #fff; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { border: 1px solid #000 !important; padding: 4px 5px; font-size: 16px; color: #000; height: 30px; }
                        .bg-slate-900 { background-color: #111827 !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                        .text-center { text-align: center; }
                        .flex { display: flex; }
                        .items-center { align-items: center; }
                        .justify-center { justify-content: center; }
                        .justify-between { justify-content: space-between; }
                        .flex-col { flex-direction: column; }
                        .flex-grow { flex-grow: 1; }
                        .mb-2 { margin-bottom: .5rem; }
                        .mt-1 { margin-top: .25rem; }
                        .py-1 { padding-top: .25rem; padding-bottom: .25rem; }
                        .px-1 { padding-left: .25rem; padding-right: .25rem; }
                        .pl-2 { padding-left: .5rem; }
                        .font-semibold { font-weight: 600; }
                        .text-xs { font-size: 12px; }
                        @page { size: 15in 9.1in; margin: 5mm; padding-left: 50px; }
                    </style>
                </head>
                <body>
                    ${printContent}
                    <script>
                        window.onload = function() {
                            window.print();
                            window.onafterprint = function() { window.close(); };
                        };
                    <\/script>
                </body>
                </html>
            `)

            printWindow.document.close()
        },
    },
}
</script>
