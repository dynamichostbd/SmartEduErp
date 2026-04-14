<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5 print:hidden">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Tabulation Sheet</div>
                    <div class="mt-1 text-sm text-slate-600">Search Result</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goBack">Back</button>
                    <button type="button" class="rounded-sm bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" :disabled="printing || !hasRows" @click="print">{{ printing ? '...' : 'PRINT' }}</button>
                    <button type="button" class="rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="downloading || !hasRows" @click="downloadExcel">{{ downloading ? '...' : 'EXCEL' }}</button>
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
                    <input v-model="filters.search_keyword" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="Type your text" @keydown.enter.prevent="search" />
                </div>

                <div class="lg:col-span-1 flex items-end">
                    <button type="button" class="h-9 w-full rounded-sm bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="loading" @click="search">{{ loading ? '...' : 'Go' }}</button>
                </div>
            </div>
        </div>

        <div class=" border border-slate-300 bg-white p-5" id="printArea">
            <div v-if="hasRows" class="mb-4 overflow-hidden rounded-xl border border-slate-300">
                <table class="w-full text-sm">
                    <tbody>
                        <tr class="border-b border-slate-300">
                            <th class="w-[20%] bg-slate-50 px-3 py-2 text-left font-semibold text-slate-700">Session</th>
                            <td class="w-[30%] px-3 py-2">{{ result.academic_session }}</td>
                            <th class="w-[20%] bg-slate-50 px-3 py-2 text-left font-semibold text-slate-700">Academic Level</th>
                            <td class="px-3 py-2">{{ result.academic_level }}</td>
                        </tr>
                        <tr class="border-b border-slate-300">
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

            <div v-if="result.subject_chunks && result.subject_chunks.length" class="space-y-6">
                <div v-for="(chunk, chunkIndex) in result.subject_chunks" :key="'chunk-' + chunkIndex" class="tabulation-page" :class="{ 'page-break': chunkIndex < result.subject_chunks.length - 1 }">
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse text-xs">
                            <thead class="bg-emerald-100">
                                <tr>
                                    <th rowspan="2" class="border border-slate-300 px-2 py-2 text-center font-semibold">SL.</th>
                                    <th rowspan="2" class="border border-slate-300 px-2 py-2 text-left font-semibold">College Roll</th>
                                    <th rowspan="2" class="border border-slate-300 px-2 py-2 text-left font-semibold">Student Name</th>
                                    <template v-if="chunk.length">
                                        <th v-for="(sub, sbKey) in chunk" :key="`sub_${chunkIndex}_${sbKey}`" colspan="5" class="border border-slate-300 px-2 py-2 text-center font-semibold">
                                            {{ sub.subject ? sub.subject.name_en : '' }}
                                        </th>
                                    </template>
                                    <th rowspan="2" class="border border-slate-300 px-2 py-2 text-center font-semibold">Total</th>
                                    <th rowspan="2" class="border border-slate-300 px-2 py-2 text-center font-semibold">GPA</th>
                                    <th rowspan="2" class="border border-slate-300 px-2 py-2 text-center font-semibold">Grade</th>
                                </tr>
                                <tr v-if="chunk.length" class="text-center">
                                    <template v-for="(sub, sbKey) in chunk" :key="`subh_${chunkIndex}_${sbKey}`">
                                        <th class="border border-slate-300 px-2 py-2 font-semibold">CQ</th>
                                        <th class="border border-slate-300 px-2 py-2 font-semibold">MCQ</th>
                                        <th class="border border-slate-300 px-2 py-2 font-semibold">PRAC.</th>
                                        <th class="border border-slate-300 px-2 py-2 font-semibold">OBTAINED</th>
                                        <th class="border border-slate-300 px-2 py-2 font-semibold">GRADE</th>
                                    </template>
                                </tr>
                            </thead>

                            <tbody v-if="(result.result_sheet || []).length" class="bg-white">
                                <tr v-for="(std, idx) in result.result_sheet" :key="'std-' + chunkIndex + '-' + idx">
                                    <td class="border border-slate-300 px-2 py-1 text-center">{{ idx + 1 }}</td>
                                    <td class="border border-slate-300 px-2 py-1">{{ std.college_roll }}</td>
                                    <td class="border border-slate-300 px-2 py-1">{{ std.name }}</td>

                                    <template v-if="std.subjects && std.subjects.length">
                                        <template v-for="(mark, rmKey) in std.subjects.slice(chunkIndex * 3, (chunkIndex + 1) * 3)" :key="'mk-' + chunkIndex + '-' + rmKey">
                                            <td class="border border-slate-300 px-2 py-1 text-center">{{ mark.cq_mark }}</td>
                                            <td class="border border-slate-300 px-2 py-1 text-center">{{ mark.mcq_mark }}</td>
                                            <td class="border border-slate-300 px-2 py-1 text-center">{{ mark.practical_mark }}</td>
                                            <td class="border border-slate-300 px-2 py-1 text-center">{{ mark.total_mark }}</td>
                                            <td class="border border-slate-300 px-2 py-1 text-center">{{ mark.letter_grade }}</td>
                                        </template>
                                    </template>

                                    <td class="border border-slate-300 px-2 py-1 text-center">{{ std.total_mark }}</td>
                                    <td class="border border-slate-300 px-2 py-1 text-center">{{ std.gpa }}</td>
                                    <td class="border border-slate-300 px-2 py-1 text-center">{{ std.letter_grade }}</td>
                                </tr>
                            </tbody>

                            <tbody v-else>
                                <tr>
                                    <td :colspan="tableColspan(chunk.length)" class="border border-slate-300 px-3 py-10 text-center text-slate-500">No data Found</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div v-else class="rounded-xl border border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-600">
                No data Found
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ResultTabulationSheet',
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
            downloading: false,
            error: '',
            searchTimer: null,
            result: {
                academic_session: '',
                academic_level: '',
                department: '',
                academic_class: '',
                exam: '',
                subjects: [],
                subject_chunks: [],
                result_sheet: [],
            },
            exams: [],
            filters: {
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
                exam_id: '',
                field_name: 'college_roll',
                search_keyword: '',
            },
        }
    },
    computed: {
        hasRows() {
            return Array.isArray(this.result?.result_sheet) && this.result.result_sheet.length > 0
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
                this.scheduleSearch()
            }
        },
        'filters.department_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.filters.exam_id = ''
                this.scheduleSearch()
            }
        },
        'filters.academic_class_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch()
        },
        'filters.exam_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch()
        },
        'filters.field_name'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch()
        },
        'filters.search_keyword'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch()
        },
    },
    created() {
        this.loadExams()
    },
    methods: {
        scheduleSearch() {
            if (this.searchTimer) clearTimeout(this.searchTimer)
            this.searchTimer = setTimeout(() => {
                const msg = this.validateFilters()
                if (!msg) this.search()
            }, 250)
        },
        toast(message, type = 'success') {
            window.dispatchEvent(new CustomEvent('app-toast', { detail: { message, type } }))
        },
        goBack() {
            window.history.back()
        },
        tableColspan(subjectCount) {
            return 3 + subjectCount * 5 + 3
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
                const res = await window.axios.get('/admin/tabulation-sheet-data', { params: this.filters })
                this.result = res?.data || {
                    academic_session: '',
                    academic_level: '',
                    department: '',
                    academic_class: '',
                    exam: '',
                    subjects: [],
                    subject_chunks: [],
                    result_sheet: [],
                }
            } catch (e) {
                this.result = {
                    academic_session: '',
                    academic_level: '',
                    department: '',
                    academic_class: '',
                    exam: '',
                    subjects: [],
                    subject_chunks: [],
                    result_sheet: [],
                }
                this.error = e?.response?.data?.message || 'Failed to load data.'
            } finally {
                this.loading = false
            }
        },
        async downloadExcel() {
            this.error = ''
            const msg = this.validateFilters()
            if (msg) {
                this.error = msg
                this.toast(msg, 'error')
                return
            }

            this.downloading = true
            try {
                const response = await window.axios.get('/admin/download-tabulation-sheet', {
                    params: this.filters,
                    responseType: 'blob',
                })

                const blob = new Blob([response.data], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                })
                const url = window.URL.createObjectURL(blob)
                const link = document.createElement('a')
                link.href = url

                let filename = 'Tabulation_Sheet.xlsx'
                const contentDisposition = response.headers?.['content-disposition']
                if (contentDisposition) {
                    const fileNameMatch = contentDisposition.match(/filename="?(.+)"?/)
                    if (fileNameMatch && fileNameMatch.length === 2) filename = fileNameMatch[1]
                }

                link.setAttribute('download', filename)
                document.body.appendChild(link)
                link.click()
                link.parentNode.removeChild(link)
                window.URL.revokeObjectURL(url)
            } catch (e) {
                this.toast('Download failed', 'error')
            } finally {
                this.downloading = false
            }
        },
        print() {
            this.printing = true
            const styleEl = document.createElement('style')
            styleEl.setAttribute('data-print-page-style', 'tabulation-sheet')
            styleEl.textContent = '@media print{@page{size: legal landscape;margin:2mm;}}'
            document.head.appendChild(styleEl)
            this.$nextTick(() => {
                window.print()
                setTimeout(() => {
                    styleEl.remove()
                    this.printing = false
                }, 500)
            })
        },
    },
    beforeUnmount() {
        if (this.searchTimer) {
            clearTimeout(this.searchTimer)
            this.searchTimer = null
        }
    },
}
</script>

<style scoped>
@media print {
    .page-break {
        page-break-after: always;
        break-after: page;
    }

    @page {
        size: legal landscape;
        margin: 2mm;
    }

    table,
    th,
    td {
        font-size: 10px !important;
        padding: 2px 4px !important;
    }

    th,
    td {
        border: 1px solid #000 !important;
    }
}
</style>
