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
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="!hasHiddenAnything" @click="toggleActionButtons">
                        {{ showActionButtons ? 'Hide Buttons' : 'Unhide Buttons' }}
                    </button>
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
                    <select v-model="filters.department_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="d in filteredDepartments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Class</div>
                    <select v-model="filters.academic_class_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
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
            <div v-if="showActionButtons && hasHiddenAnything" class="mb-3 flex flex-wrap gap-2 print:hidden">
                <template v-for="col in hiddenColumnKeys" :key="'hc-' + col">
                    <button type="button" class="rounded-sm bg-emerald-600 px-3 py-1 text-xs font-semibold text-white hover:bg-emerald-700" @click="hideColumn(col)">
                        {{ columnLabel(col) }}
                    </button>
                </template>

                <template v-for="sub in hiddenSubjectsDetailed" :key="'hs-' + sub.id">
                    <button type="button" class="rounded-sm bg-emerald-600 px-3 py-1 text-xs font-semibold text-white hover:bg-emerald-700" @click="toggleSubjectHidden(sub.id)">
                        {{ sub.name }}
                    </button>
                </template>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-xs">
                    <thead class="bg-emerald-100">
                        <tr>
                            <th :colspan="tableColspan" class="border border-slate-300 p-0">
                                <table class="w-full border-collapse text-xs">
                                    <tbody>
                                        <tr>
                                            <td colspan="2" class="border border-slate-300 px-2 py-1 font-semibold">{{ collegeName }}</td>
                                            <th class="border border-slate-300 px-2 py-1 text-left font-semibold">Academic Level</th>
                                            <td class="border border-slate-300 px-2 py-1">{{ result.academic_level }}</td>
                                        </tr>
                                        <tr>
                                            <th class="border border-slate-300 px-2 py-1 text-left font-semibold">Department/Group</th>
                                            <td class="border border-slate-300 px-2 py-1">{{ result.department }}</td>
                                            <th class="border border-slate-300 px-2 py-1 text-left font-semibold">Class</th>
                                            <td class="border border-slate-300 px-2 py-1">{{ result.academic_class }}</td>
                                        </tr>
                                        <tr>
                                            <th class="border border-slate-300 px-2 py-1 text-left font-semibold">Session</th>
                                            <td class="border border-slate-300 px-2 py-1">{{ result.academic_session }}</td>
                                            <th class="border border-slate-300 px-2 py-1 text-left font-semibold">Exam</th>
                                            <td class="border border-slate-300 px-2 py-1">{{ result.exam }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </th>
                        </tr>

                        <tr>
                            <th rowspan="2" class="border border-slate-300 px-2 py-2 text-center font-semibold">SL.</th>
                            <th v-if="!isColumnHidden('software_id')" rowspan="2" class="border border-slate-300 px-2 py-2 text-center font-semibold">Software ID</th>
                            <th v-if="!isColumnHidden('college_roll')" rowspan="2" class="border border-slate-300 px-2 py-2 text-center font-semibold">
                                College Roll
                                <div v-if="showActionButtons" class="mt-1">
                                    <button type="button" class="rounded border border-slate-300 bg-white px-1 py-0.5 text-[10px] font-semibold text-emerald-700 hover:bg-slate-50" @click="sortByCollegeRoll">Sort</button>
                                </div>
                            </th>
                            <th v-if="!isColumnHidden('student_name')" rowspan="2" class="border border-slate-300 px-2 py-2 text-left font-semibold">Student Name</th>
                            <th v-if="!isColumnHidden('mobile')" rowspan="2" class="border border-slate-300 px-2 py-2 text-center font-semibold">
                                Mobile
                                <button v-if="showActionButtons" type="button" class="ml-1 rounded border border-slate-300 bg-white px-1 py-0.5 text-[10px] font-semibold text-rose-700 hover:bg-slate-50" @click="hideColumn('mobile')">Hide</button>
                            </th>

                            <template v-for="(sub, sbKey) in visibleSubjects" :key="'sub-' + sbKey">
                                <th class="border border-slate-300 px-2 py-2 text-center font-semibold">
                                    <div class="flex flex-col items-center gap-1">
                                        <div class="max-w-[72px] break-words">{{ sub.subject ? sub.subject.name_en : '' }}</div>
                                        <div v-if="showActionButtons" class="flex items-center gap-1">
                                            <button type="button" class="rounded border border-slate-300 bg-white px-1 py-0.5 text-[10px] font-semibold text-emerald-700 hover:bg-slate-50" @click="sortBySubjectCtMark(getSubjectId(sub))">Sort</button>
                                            <button type="button" class="rounded border border-slate-300 bg-white px-1 py-0.5 text-[10px] font-semibold text-rose-700 hover:bg-slate-50" @click="toggleSubjectHidden(getSubjectId(sub))">Hide</button>
                                        </div>
                                    </div>
                                </th>
                            </template>

                            <th v-if="!isColumnHidden('total')" rowspan="2" class="border border-slate-300 px-2 py-2 text-center font-semibold">
                                Total
                                <button v-if="showActionButtons" type="button" class="ml-1 rounded border border-slate-300 bg-white px-1 py-0.5 text-[10px] font-semibold text-emerald-700 hover:bg-slate-50" @click="sortByTotalmark">Sort</button>
                            </th>
                        </tr>

                        <tr v-if="visibleSubjects.length" class="text-center">
                            <template v-for="(sub, sbKey) in visibleSubjects" :key="'subh-' + sbKey">
                                <th class="border border-slate-300 px-2 py-2 font-semibold">CT</th>
                            </template>
                        </tr>
                    </thead>

                    <tbody v-if="displayedResultSheet.length" class="bg-white">
                        <tr v-for="(std, idx) in displayedResultSheet" :key="'r-' + idx">
                            <td class="border border-slate-300 px-2 py-1 text-center">{{ idx + 1 }}</td>
                            <td v-if="!isColumnHidden('software_id')" class="border border-slate-300 px-2 py-1 text-center">{{ std.software_id }}</td>
                            <td v-if="!isColumnHidden('college_roll')" class="border border-slate-300 px-2 py-1 text-center">{{ std.college_roll }}</td>
                            <td v-if="!isColumnHidden('student_name')" class="border border-slate-300 px-2 py-1">{{ std.name }}</td>
                            <td v-if="!isColumnHidden('mobile')" class="border border-slate-300 px-2 py-1 text-center">{{ std.mobile }}</td>

                            <template v-for="(sub, rmKey) in visibleSubjects" :key="'m-' + rmKey">
                                <td class="border border-slate-300 px-2 py-1 text-center">{{ getSubjectCtMark(std, getSubjectId(sub)) }}</td>
                            </template>

                            <td v-if="!isColumnHidden('total')" class="border border-slate-300 px-2 py-1 text-center">{{ std.total_mark }}</td>
                        </tr>
                    </tbody>

                    <tbody v-else>
                        <tr>
                            <td :colspan="tableColspan" class="border border-slate-300 px-3 py-10 text-center text-slate-500">No data Found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ResultTabulationSheetCT',
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
            result: {
                academic_session: '',
                academic_level: '',
                department: '',
                academic_class: '',
                exam: '',
                subjects: [],
                result_sheet: [],
            },
            filters: {
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
                exam_id: '',
                field_name: 'college_roll',
                search_keyword: '',
            },
            hiddenColumns: {
                software_id: false,
                college_roll: false,
                student_name: false,
                mobile: false,
                total: false,
            },
            hiddenSubjectIds: [],
            showActionButtons: true,
            sortState: {
                key: null,
                direction: 1,
            },
        }
    },
    computed: {
        collegeName() {
            return this.systems?.site?.college_name || ''
        },
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
        visibleSubjects() {
            const subjects = this.result?.subjects
            const list = Array.isArray(subjects) ? subjects : []
            return list.filter((sub) => {
                const id = this.getSubjectId(sub)
                if (id == null) return true
                return !this.hiddenSubjectIds.includes(String(id))
            })
        },
        displayedResultSheet() {
            const raw = this.result?.result_sheet
            const sheet = Array.isArray(raw) ? [...raw] : []

            if (!this.sortState.key) return sheet

            const dir = this.sortState.direction

            if (this.sortState.key === 'total') {
                return sheet.sort((a, b) => {
                    const av = Number(a?.total_mark ?? -Infinity)
                    const bv = Number(b?.total_mark ?? -Infinity)
                    return (av - bv) * dir
                })
            }

            if (this.sortState.key === 'college_roll') {
                return sheet.sort((a, b) => {
                    const aRaw = a?.college_roll ?? ''
                    const bRaw = b?.college_roll ?? ''
                    const av = parseInt(aRaw, 10)
                    const bv = parseInt(bRaw, 10)

                    if (!Number.isNaN(av) && !Number.isNaN(bv)) {
                        return (av - bv) * dir
                    }

                    return String(aRaw).localeCompare(String(bRaw), undefined, { numeric: true }) * dir
                })
            }

            if (String(this.sortState.key).startsWith('subject:')) {
                const subjectId = String(this.sortState.key).split(':')[1]
                return sheet.sort((a, b) => {
                    const av = Number(this.getSubjectCtMark(a, subjectId) ?? -Infinity)
                    const bv = Number(this.getSubjectCtMark(b, subjectId) ?? -Infinity)
                    return (av - bv) * dir
                })
            }

            return sheet
        },
        hiddenColumnKeys() {
            const cols = this.hiddenColumns || {}
            return Object.keys(cols).filter((k) => !!cols[k])
        },
        hiddenSubjectsDetailed() {
            const subjects = this.result?.subjects
            const list = Array.isArray(subjects) ? subjects : []
            return list
                .map((sub) => {
                    const id = this.getSubjectId(sub)
                    const name = sub?.subject?.name_en || ''
                    return { id, name }
                })
                .filter((x) => x.id != null && this.hiddenSubjectIds.includes(String(x.id)))
        },
        hasHiddenAnything() {
            return this.hiddenColumnKeys.length > 0 || this.hiddenSubjectsDetailed.length > 0
        },
        tableColspan() {
            const fixedKeys = ['software_id', 'college_roll', 'student_name', 'mobile', 'total']
            const visibleFixed = 1 + fixedKeys.filter((k) => !this.isColumnHidden(k)).length
            return visibleFixed + this.visibleSubjects.length
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
        toggleActionButtons() {
            this.showActionButtons = !this.showActionButtons
        },
        isColumnHidden(key) {
            return !!this.hiddenColumns?.[key]
        },
        hideColumn(key) {
            if (!this.hiddenColumns) this.hiddenColumns = {}
            this.hiddenColumns[key] = !this.hiddenColumns[key]
        },
        columnLabel(key) {
            const map = {
                software_id: 'Software ID',
                college_roll: 'College Roll',
                student_name: 'Student Name',
                mobile: 'Mobile',
                total: 'Total',
            }
            return map[key] ? map[key] : key
        },
        getSubjectId(sub) {
            if (!sub) return null
            if (sub.subject_id != null) return sub.subject_id
            if (sub.subject?.id != null) return sub.subject.id
            if (sub.id != null) return sub.id
            return null
        },
        toggleSubjectHidden(subjectId) {
            if (subjectId == null) return
            const id = String(subjectId)
            const idx = this.hiddenSubjectIds.indexOf(id)
            if (idx >= 0) this.hiddenSubjectIds.splice(idx, 1)
            else this.hiddenSubjectIds.push(id)
        },
        getSubjectCtMark(std, subjectId) {
            if (!std || subjectId == null) return null
            const id = String(subjectId)
            const subjects = std.subjects
            if (!Array.isArray(subjects)) return null
            const found = subjects.find((m) => String(m.subject_id) === id)
            return found ? found.ct_mark : null
        },
        sortByTotalmark() {
            if (this.sortState.key === 'total') {
                this.sortState.direction = this.sortState.direction * -1
            } else {
                this.sortState.key = 'total'
                this.sortState.direction = -1
            }
        },
        sortBySubjectCtMark(subjectId) {
            if (subjectId == null) return
            const key = `subject:${String(subjectId)}`
            if (this.sortState.key === key) {
                this.sortState.direction = this.sortState.direction * -1
            } else {
                this.sortState.key = key
                this.sortState.direction = -1
            }
        },
        sortByCollegeRoll() {
            if (this.sortState.key === 'college_roll') {
                this.sortState.direction = this.sortState.direction * -1
            } else {
                this.sortState.key = 'college_roll'
                this.sortState.direction = 1
            }
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
                const res = await window.axios.get('/admin/get-exam', { params: { allData: true, exam_type: 'ct' } })
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
                const res = await window.axios.get('/admin/tabulation-sheet-ct-data', { params: this.filters })
                this.result = res?.data || {
                    academic_session: '',
                    academic_level: '',
                    department: '',
                    academic_class: '',
                    exam: '',
                    subjects: [],
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
                    result_sheet: [],
                }
                this.error = e?.response?.data?.message || 'Failed to load data.'
            } finally {
                this.loading = false
            }
        },
        print() {
            this.printing = true
            const styleEl = document.createElement('style')
            styleEl.setAttribute('data-print-page-style', 'tabulation-sheet-ct')
            styleEl.textContent = '@media print{@page{size:14in 8.5in;margin:6mm;}}'
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
}
</script>

<style scoped>
@media print {
    @page {
        size: 14in 8.5in;
        margin: 6mm;
    }

    table,
    th,
    td {
        font-size: 12px !important;
        padding: 2px 3px !important;
        line-height: 1.15;
    }

    th,
    td {
        border: 1px solid #000 !important;
    }
}
</style>
