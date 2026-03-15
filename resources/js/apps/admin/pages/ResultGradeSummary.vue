<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 print:hidden">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Result Grade Summary</div>
                    <div class="mt-1 text-sm text-slate-600">Search Result</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goBack">Back</button>
                    <button type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" :disabled="printing || !hasRows" @click="print">{{ printing ? '...' : 'PRINT' }}</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 print:hidden">{{ error }}</div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 print:hidden">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Session</div>
                    <select v-model="filters.academic_session_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="s in sessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Academic Level</div>
                    <select v-model="filters.academic_qualification_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Department/Group</div>
                    <select v-model="filters.department_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="d in filteredDepartments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Class</div>
                    <select v-model="filters.academic_class_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="c in filteredClasses" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Exam</div>
                    <select v-model="filters.exam_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="e in exams" :key="'ex-' + e.id" :value="String(e.id)">{{ e.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-1 flex items-end">
                    <button type="button" class="h-10 w-full rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="loading" @click="search">{{ loading ? '...' : 'Go' }}</button>
                </div>
            </div>
        </div>

        <div id="printArea" class="flex flex-col gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="overflow-hidden rounded-xl border border-slate-200">
                    <table class="w-full text-sm">
                        <tbody>
                            <tr class="border-b border-slate-200">
                                <th class="w-[20%] bg-slate-50 px-3 py-2 text-left font-semibold text-slate-700">Session</th>
                                <td class="w-[30%] px-3 py-2">{{ result.academic_session }}</td>
                                <th class="w-[20%] bg-slate-50 px-3 py-2 text-left font-semibold text-slate-700">Academic Level</th>
                                <td class="px-3 py-2">{{ result.academic_level }}</td>
                            </tr>
                            <tr class="border-b border-slate-200">
                                <th class="bg-slate-50 px-3 py-2 text-left font-semibold text-slate-700">Department/Group</th>
                                <td class="px-3 py-2">{{ result.department }}</td>
                                <th class="bg-slate-50 px-3 py-2 text-left font-semibold text-slate-700">Class</th>
                                <td class="px-3 py-2">{{ result.academic_class }}</td>
                            </tr>
                            <tr>
                                <th class="bg-slate-50 px-3 py-2 text-left font-semibold text-slate-700">Exam</th>
                                <td colspan="3" class="px-3 py-2">{{ result.exam }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
                <div class="xl:col-span-4">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="text-sm font-semibold text-slate-900">Total Grade Summary</div>
                        <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200">
                            <table v-if="(result.grade_summary || []).length" class="min-w-full border-collapse text-sm">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="border border-slate-300 px-3 py-2 text-center font-semibold">GRADE</th>
                                        <th class="border border-slate-300 px-3 py-2 text-center font-semibold">Total Student</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    <tr v-for="(gSumm, gKey) in result.grade_summary" :key="'g-' + gKey">
                                        <td class="border border-slate-300 px-3 py-2 text-center font-semibold">{{ gSumm.letter_grade }}</td>
                                        <td class="border border-slate-300 px-3 py-2 text-center font-semibold">{{ gSumm.grade_count }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div v-else class="px-3 py-10 text-center text-sm text-slate-500">No data found</div>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-8">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="text-sm font-semibold text-slate-900">Subject Wise Grade Summary</div>
                        <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200">
                            <table v-if="(result.grade_summary || []).length" class="min-w-full border-collapse text-sm">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th rowspan="2" class="border border-slate-300 px-3 py-2 text-left font-semibold" style="min-width: 280px">Subject</th>
                                        <th colspan="9" class="border border-slate-300 px-3 py-2 text-center font-semibold">GRADE WISE STUDENT COUNT</th>
                                    </tr>
                                    <tr>
                                        <th v-for="(g, idx) in grades" :key="'gh-' + idx" class="border border-slate-300 px-3 py-2 text-center font-semibold" style="min-width: 90px">
                                            {{ g }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    <tr v-for="(group, sKey) in subjectSummaryGroups" :key="'sub-' + sKey">
                                        <td class="border border-slate-300 px-3 py-2 text-left">{{ group && group.length ? group[0].subject_name : '' }}</td>
                                        <td v-for="(g, idx) in grades" :key="'gd-' + sKey + '-' + idx" class="border border-slate-300 px-3 py-2 text-center">
                                            <span v-if="findCount(group, g) != null">{{ findCount(group, g) }}</span>
                                            <span v-else class="text-slate-400">--</span>
                                        </td>
                                    </tr>
                                    <tr v-if="!subjectSummaryGroups.length">
                                        <td :colspan="1 + grades.length" class="border border-slate-300 px-3 py-10 text-center text-sm text-slate-500">No data found</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div v-else class="px-3 py-10 text-center text-sm text-slate-500">No data found</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ResultGradeSummary',
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
            error: '',
            exams: [],
            grades: ['A+', 'A', 'A-', 'B', 'C', 'D', 'F', 'Absent', 'Undefined'],
            result: {
                academic_session: '',
                academic_level: '',
                department: '',
                academic_class: '',
                exam: '',
                grade_summary: [],
                subject_summary: [],
            },
            filters: {
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
                exam_id: '',
            },
        }
    },
    computed: {
        hasRows() {
            return Array.isArray(this.result?.grade_summary) && this.result.grade_summary.length > 0
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
            if (!qid) return this.departments
            const allowed = new Set(
                this.departmentQualifications
                    .filter((r) => String(r.academic_qualification_id) === String(qid))
                    .map((r) => String(r.department_id)),
            )
            return this.departments.filter((d) => allowed.has(String(d.id)))
        },
        filteredClasses() {
            const qid = this.filters?.academic_qualification_id
            if (!qid) return this.classes
            return this.classes.filter((c) => String(c.academic_qualification_id) === String(qid))
        },
        subjectSummaryGroups() {
            const raw = this.result?.subject_summary
            if (!raw) return []
            if (Array.isArray(raw)) return raw
            if (typeof raw === 'object') return Object.values(raw)
            return []
        },
    },
    watch: {
        'filters.academic_qualification_id': function () {
            this.filters.department_id = ''
            this.filters.academic_class_id = ''
            this.filters.exam_id = ''
        },
        'filters.department_id': function () {
            this.filters.exam_id = ''
        },
    },
    created() {
        this.loadExams()
    },
    methods: {
        toast(message, type = 'success') {
            window.dispatchEvent(new CustomEvent('app-toast', { detail: { message, type } }))
        },
        goBack() {
            window.history.back()
        },
        validateFilters() {
            if (!this.filters.academic_session_id) return 'Session is required'
            if (!this.filters.academic_qualification_id) return 'Academic Level is required'
            if (!this.filters.department_id) return 'Department is required'
            if (!this.filters.academic_class_id) return 'Academic Class is required'
            if (!this.filters.exam_id) return 'Exam is required'
            return ''
        },
        async loadExams() {
            try {
                const res = await window.axios.get('/admin/get-exam', { params: { allData: true, exam_type: 'term' } })
                this.exams = Array.isArray(res?.data) ? res.data : Array.isArray(res?.data?.data) ? res.data.data : []
            } catch (e) {
                this.exams = []
            }
        },
        findCount(group, grade) {
            if (!Array.isArray(group)) return null
            const found = group.find((e) => String(e?.letter_grade) === String(grade))
            if (!found) return null
            return found.grade_count
        },
        async search() {
            this.error = ''
            const msg = this.validateFilters()
            if (msg) {
                this.error = msg
                this.toast(msg, 'error')
                return
            }

            this.loading = true
            try {
                const res = await window.axios.get('/admin/result-grade-summary-data', { params: this.filters })
                this.result = res?.data || {
                    academic_session: '',
                    academic_level: '',
                    department: '',
                    academic_class: '',
                    exam: '',
                    grade_summary: [],
                    subject_summary: [],
                }
            } catch (e) {
                this.result = {
                    academic_session: '',
                    academic_level: '',
                    department: '',
                    academic_class: '',
                    exam: '',
                    grade_summary: [],
                    subject_summary: [],
                }
                this.error = e?.response?.data?.message || 'Failed to load data.'
            } finally {
                this.loading = false
            }
        },
        print() {
            this.printing = true
            this.$nextTick(() => {
                window.print()
                setTimeout(() => {
                    this.printing = false
                }, 250)
            })
        },
    },
}
</script>

<style scoped>
@media print {
    @page {
        size: A4;
        margin: 8mm;
    }

    table,
    th,
    td {
        font-size: 12px !important;
        padding: 2px 4px !important;
        line-height: 1.2;
    }

    th,
    td {
        border: 1px solid #000 !important;
    }
}
</style>
