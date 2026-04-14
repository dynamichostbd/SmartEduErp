<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5 print:hidden">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Subject Wise Result</div>
                    <div class="mt-1 text-sm text-slate-600">Search Result</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goBack">Back</button>
                    <button type="button" class="rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="printing || !hasRows" @click="print">{{ printing ? '...' : 'PRINT' }}</button>
                    <button type="button" class="rounded-sm bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700" :disabled="exporting || !hasRows" @click="exportExcel">{{ exporting ? '...' : 'EXCEL' }}</button>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800 print:hidden">{{ error }}</div>

        <div class=" border border-slate-300 bg-white p-5 print:hidden">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Session</div>
                    <select v-model="filters.academic_session_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="s in sessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Academic Level</div>
                    <select v-model="filters.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Department/Group</div>
                    <select
                        v-model="filters.department_id"
                        :disabled="!filters.academic_qualification_id"
                        class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                        :class="!filters.academic_qualification_id ? 'bg-slate-100 text-slate-500' : ''"
                    >
                        <option value="">Select</option>
                        <option v-for="d in filteredDepartments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Class</div>
                    <select
                        v-model="filters.academic_class_id"
                        :disabled="!filters.academic_qualification_id"
                        class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                        :class="!filters.academic_qualification_id ? 'bg-slate-100 text-slate-500' : ''"
                    >
                        <option value="">Select</option>
                        <option v-for="c in filteredClasses" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Exam</div>
                    <select v-model="filters.exam_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="e in exams" :key="'ex-' + e.id" :value="String(e.id)">{{ e.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Subject</div>
                    <select v-model="filters.subject_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" @change="selectSubject">
                        <option value="">Select</option>
                        <option v-for="s in subjects" :key="'sub-' + s.subject_id" :value="String(s.subject_id)">{{ s.subject_name || '' }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Search Field</div>
                    <select v-model="filters.field_name" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">--Select One--</option>
                        <option value="student_id">Software ID</option>
                        <option value="name">Name</option>
                        <option value="mobile">Mobile</option>
                        <option value="college_roll">College Roll</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Search</div>
                    <input v-model="filters.search_keyword" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" @keydown.enter.prevent="search" />
                </div>

                <div class="lg:col-span-1 flex items-end">
                    <button type="button" class="h-9 w-full rounded-sm bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="loading" @click="search">{{ loading ? '...' : 'Go' }}</button>
                </div>
            </div>
        </div>

        <div class=" border border-slate-300 bg-white p-5" id="printArea">
            <div class="mb-4 rounded-xl border border-slate-300 bg-slate-50 p-4 text-sm text-slate-700">
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-5">
                    <div><span class="font-semibold">Session:</span> {{ result?.academic_session?.name || '--' }}</div>
                    <div><span class="font-semibold">Level:</span> {{ result?.qualification?.name || '--' }}</div>
                    <div><span class="font-semibold">Dept:</span> {{ result?.department?.name || '--' }}</div>
                    <div><span class="font-semibold">Class:</span> {{ result?.academic_class?.name || '--' }}</div>
                    <div><span class="font-semibold">Exam:</span> {{ result?.exam?.name || '--' }}</div>
                </div>
                <div class="mt-2"><span class="font-semibold">Subject Name:</span> {{ selectedSubject?.subject_name || '--' }}</div>
            </div>

            <div class="overflow-x-auto">
                <table id="excel-table" class="min-w-full border-collapse border border-slate-300 text-sm">
                    <thead class="bg-emerald-100">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-700">
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">#</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Software ID</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Student Name</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">College Roll</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">CT <span class="text-xs text-slate-500">({{ selectedSubject?.ct_mark ?? '' }})</span></th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">CQ <span class="text-xs text-slate-500">({{ selectedSubject?.cq_mark ?? '' }})</span></th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">MCQ <span class="text-xs text-slate-500">({{ selectedSubject?.mcq_mark ?? '' }})</span></th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">PRAC <span class="text-xs text-slate-500">({{ selectedSubject?.practical_mark ?? '' }})</span></th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">Obtained Mark</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">Total Mark</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">GRADE</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">GPA</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <tr v-for="(std, idx) in rows" :key="'r-' + idx" :class="idx % 2 === 0 ? 'bg-white' : 'bg-slate-50'">
                            <td class="border border-slate-300 px-1 py-1 text-slate-800">{{ idx + 1 }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-slate-800">{{ std.student_id || '' }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-slate-800">{{ std.name || '' }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-slate-800">{{ std.college_roll || '' }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-center text-slate-800">
                                <span v-if="Number(std.is_absent || 0) === 1 && Number(std.ct_mark || 0) === 0 && std.ct_allocated" class="text-rose-600">ABS</span>
                                <span v-else>{{ std.ct_mark ?? '' }}</span>
                            </td>
                            <td class="border border-slate-300 px-1 py-1 text-center text-slate-800">
                                <span v-if="Number(std.is_absent || 0) === 1 && Number(std.cq_mark || 0) === 0 && std.cq_allocated" class="text-rose-600">ABS</span>
                                <span v-else>{{ std.cq_mark ?? '' }}</span>
                            </td>
                            <td class="border border-slate-300 px-1 py-1 text-center text-slate-800">
                                <span v-if="Number(std.is_absent || 0) === 1 && Number(std.mcq_mark || 0) === 0 && std.mcq_allocated" class="text-rose-600">ABS</span>
                                <span v-else>{{ std.mcq_mark ?? '' }}</span>
                            </td>
                            <td class="border border-slate-300 px-1 py-1 text-center text-slate-800">
                                <span v-if="Number(std.is_absent || 0) === 1 && Number(std.practical_mark || 0) === 0 && std.practical_allocated" class="text-rose-600">ABS</span>
                                <span v-else>{{ std.practical_mark ?? '' }}</span>
                            </td>
                            <td class="border border-slate-300 px-1 py-1 text-center text-slate-800">{{ std.obtained_mark ?? '' }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-center text-slate-800">{{ std.total_mark ?? '' }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-center text-slate-800">{{ std.letter_grade ?? '' }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-center text-slate-800">{{ std.gpa ?? '' }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-center">
                                <span v-if="String(std.letter_grade || '') !== 'F' && String(std.letter_grade || '') !== 'ABS'" class="inline-flex w-full justify-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">PASSED</span>
                                <span v-else-if="String(std.letter_grade || '') === 'ABS'" class="inline-flex w-full justify-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">ABSENT</span>
                                <span v-else class="inline-flex w-full justify-center rounded-full bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700">FAILED</span>
                            </td>
                        </tr>
                        <tr v-if="!rows.length">
                            <td colspan="13" class="px-3 py-10 text-center text-slate-500">No data Found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ResultSubjectwiseResult',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            loading: false,
            printing: false,
            exporting: false,
            error: '',
            subjects: [],
            selectedSubject: null,
            result: null,
            rows: [],
            examsList: [],
            searchTimer: null,
            filters: {
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
                exam_id: '',
                subject_id: '',
                field_name: 'college_roll',
                search_keyword: '',
            },
        }
    },
    computed: {
        hasRows() {
            return (this.rows || []).length > 0
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
            if (!qid) return []
            const allowed = new Set(
                this.departmentQualifications
                    .filter((r) => String(r.academic_qualification_id) === String(qid))
                    .map((r) => String(r.department_id)),
            )
            return this.departments.filter((d) => allowed.has(String(d.id)))
        },
        filteredClasses() {
            const qid = this.filters?.academic_qualification_id
            if (!qid) return []
            return this.classes.filter((c) => String(c.academic_qualification_id) === String(qid))
        },
        exams() {
            const list = Array.isArray(this.examsList) && this.examsList.length ? this.examsList : this.systems?.global?.exams
            const items = Array.isArray(list) ? list : []
            return items.filter((e) => {
                const t = String(e?.exam_type || '')
                if (!t) return true
                return t === 'term'
            })
        },
    },
    watch: {
        'filters.academic_session_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch()
        },
        'filters.academic_qualification_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.filters.department_id = ''
                this.filters.academic_class_id = ''
                this.filters.exam_id = ''
                this.filters.subject_id = ''
                this.subjects = []
                this.selectedSubject = null
                this.scheduleSearch()
            }
        },
        'filters.department_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.filters.exam_id = ''
                this.filters.subject_id = ''
                this.subjects = []
                this.selectedSubject = null
                this.scheduleSearch()
            }
        },
        'filters.academic_class_id': async function (next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.filters.subject_id = ''
                this.subjects = []
                this.selectedSubject = null
                await this.loadSubjects()
                this.scheduleSearch()
            }
        },
        'filters.exam_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch()
        },
        'filters.subject_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.selectSubject()
                this.scheduleSearch()
            }
        },
        'filters.field_name'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch()
        },
        'filters.search_keyword'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch()
        },
    },
    methods: {
        scheduleSearch() {
            if (this.searchTimer) clearTimeout(this.searchTimer)
            this.searchTimer = setTimeout(() => {
                const ok =
                    !!this.filters.academic_session_id &&
                    !!this.filters.academic_qualification_id &&
                    !!this.filters.department_id &&
                    !!this.filters.academic_class_id &&
                    !!this.filters.exam_id &&
                    !!this.filters.subject_id
                if (ok) this.search()
            }, 250)
        },
        async loadExamsTerm() {
            try {
                const res = await window.axios.get('/admin/get-exam', { params: { allData: true, exam_type: 'term' } })
                this.examsList = Array.isArray(res?.data) ? res.data : Array.isArray(res?.data?.data) ? res.data.data : []
            } catch {
                this.examsList = []
            }
        },
        goBack() {
            window.history.back()
        },
        async loadSubjects() {
            this.subjects = []
            this.selectedSubject = null
            this.filters.subject_id = ''

            if (!this.filters.academic_qualification_id || !this.filters.department_id || !this.filters.academic_class_id) return

            try {
                const params = {
                    academic_qualification_id: this.filters.academic_qualification_id,
                    department_id: this.filters.department_id,
                    academic_class_id: this.filters.academic_class_id,
                }
                const res = await window.axios.get('/admin/classwise-subjects', { params })
                this.subjects = Array.isArray(res?.data) ? res.data : []
            } catch (e) {
                this.subjects = []
            }
        },
        selectSubject() {
            const id = String(this.filters.subject_id || '')
            this.selectedSubject = (this.subjects || []).find((s) => String(s?.subject_id) === id) || null
        },
        async search() {
            this.loading = true
            this.error = ''
            this.result = null
            this.rows = []
            try {
                const res = await window.axios.get('/admin/subjectwise-result-data', { params: this.filters })
                this.result = res?.data?.result || null
                this.rows = Array.isArray(this.result?.details) ? this.result.details : []
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load subject wise result.'
            } finally {
                this.loading = false
            }
        },
        async print() {
            this.printing = true
            try {
                setTimeout(() => window.print(), 50)
            } finally {
                setTimeout(() => {
                    this.printing = false
                }, 200)
            }
        },
        exportExcel() {
            this.exporting = true
            try {
                const safe = (v) => String(v ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')

                const headerLines = [
                    `Session: ${this.result?.academic_session?.name || ''}`,
                    `Academic Level: ${this.result?.qualification?.name || ''}`,
                    `Department/Group: ${this.result?.department?.name || ''}`,
                    `Academic Class: ${this.result?.academic_class?.name || ''}`,
                    `Exam Name: ${this.result?.exam?.name || ''}`,
                    `Subject Name: ${this.selectedSubject?.subject_name || ''}`,
                ].filter((x) => x.trim() !== '')

                const table = document.getElementById('excel-table')
                const tableHtml = table ? table.outerHTML : ''

                const html =
                    '<html><head><meta charset="UTF-8"></head><body>' +
                    (headerLines.length ? `<div>${headerLines.map((l) => `<div>${safe(l)}</div>`).join('')}</div><br/>` : '') +
                    tableHtml +
                    '</body></html>'

                const blob = new Blob([html], { type: 'application/vnd.ms-excel' })
                const url = URL.createObjectURL(blob)
                const a = document.createElement('a')
                a.href = url
                a.download = `subject_wise_result_${new Date().toISOString().slice(0, 19).replace(/[:T]/g, '_')}.xls`
                document.body.appendChild(a)
                a.click()
                document.body.removeChild(a)
                URL.revokeObjectURL(url)
            } finally {
                setTimeout(() => {
                    this.exporting = false
                }, 150)
            }
        },
    },
    async mounted() {
        await this.loadExamsTerm()
        await this.loadSubjects()
    },
    beforeUnmount() {
        if (this.searchTimer) {
            clearTimeout(this.searchTimer)
            this.searchTimer = null
        }
    },
}
</script>
