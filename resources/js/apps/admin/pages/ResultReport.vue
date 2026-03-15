<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Result Report</div>
                    <div class="mt-1 text-sm text-slate-600">Search Result</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goBack">Back</button>
                    <button
                        v-if="(result?.details || []).length"
                        type="button"
                        class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700"
                        :disabled="downloadingSingle || bulkExportStatus === 'pending' || bulkExportStatus === 'processing'"
                        @click="downloadMarksheet"
                    >
                        <span v-if="downloadingSingle">...</span>
                        <span v-else-if="bulkExportStatus === 'done'">Ready — click to download</span>
                        <span v-else-if="bulkExportStatus === 'pending' || bulkExportStatus === 'processing'">{{ bulkExportMessage || 'Processing...' }}</span>
                        <span v-else>DOWNLOAD MARKSHEET</span>
                    </button>
                    <button
                        v-if="(result?.details || []).length"
                        type="button"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                        :disabled="downloadingAll"
                        @click="downloadAllMarksheet"
                    >
                        {{ downloadingAll ? '...' : 'ALL MARKSHEET' }}
                    </button>
                    <button
                        v-if="(result?.details || []).length"
                        type="button"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                        :disabled="exportingExcel"
                        @click="exportExcel"
                    >
                        {{ exportingExcel ? '...' : 'EXCEL' }}
                    </button>
                    <button type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" :disabled="printing" @click="print">{{ printing ? '...' : 'PRINT' }}</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
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

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Type</div>
                    <select v-model="filters.type" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="select">Select</option>
                        <option value="all">ALL</option>
                        <option value="merit">Merit</option>
                        <option value="unmerit">Unmerit</option>
                    </select>
                </div>

                <div class="lg:col-span-2" v-if="filters.type === 'unmerit'">
                    <div class="text-xs font-semibold text-slate-600">Failed Subject</div>
                    <select v-model="filters.failed_subject" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">--Select--</option>
                        <option v-for="i in 10" :key="'f-' + i" :value="String(i)">{{ i }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2" v-if="filters.type === 'unmerit' && filters.failed_subject">
                    <div class="text-xs font-semibold text-slate-600">4th Subject</div>
                    <select v-model="filters.exclude_fourth_subject" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">With 4th Subject</option>
                        <option value="1">Without 4th Subject</option>
                    </select>
                </div>

                <div class="lg:col-span-2" v-if="filters.type === 'all'">
                    <div class="text-xs font-semibold text-slate-600">Passed Subject</div>
                    <select v-model="filters.passed_subject" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">--Select--</option>
                        <option v-for="i in 10" :key="'p-' + i" :value="String(i)">{{ i }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Search Field</div>
                    <select v-model="filters.field_name" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">--Select One--</option>
                        <option value="student_id">Software ID</option>
                        <option value="name">Name</option>
                        <option value="mobile">Mobile</option>
                        <option value="college_roll">College Roll</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Search</div>
                    <input v-model="filters.search_keyword" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" @keydown.enter.prevent="search" />
                </div>

                <div class="lg:col-span-1 flex items-end">
                    <button type="button" class="h-10 w-full rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="loading" @click="search">{{ loading ? '...' : 'Go' }}</button>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5" v-if="result">
            <div class="mb-4 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-5">
                    <div><span class="font-semibold">Session:</span> {{ result?.academic_session?.name || '--' }}</div>
                    <div><span class="font-semibold">Level:</span> {{ result?.qualification?.name || '--' }}</div>
                    <div><span class="font-semibold">Dept:</span> {{ result?.department?.name || '--' }}</div>
                    <div><span class="font-semibold">Class:</span> {{ result?.academic_class?.name || '--' }}</div>
                    <div><span class="font-semibold">Exam:</span> {{ result?.exam?.name || '--' }}</div>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table id="pdf-table" class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">#</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Software ID</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Student Name</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">
                                <button type="button" class="inline-flex items-center gap-1 hover:underline" @click="sortBy('college_roll')">
                                    College Roll
                                    <span v-if="sortField === 'college_roll'">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
                                </button>
                            </th>
                            <template v-if="filters.type === 'unmerit'">
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Total Failed</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Failed Subjects</th>
                            </template>
                            <template v-if="filters.type === 'all'">
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Total Passed</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Passed Subjects</th>
                            </template>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">
                                <button type="button" class="inline-flex items-center gap-1 hover:underline" @click="sortBy('total_mark')">
                                    Total Mark
                                    <span v-if="sortField === 'total_mark'">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
                                </button>
                            </th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">GRADE</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">
                                <button type="button" class="inline-flex items-center gap-1 hover:underline" @click="sortBy('gpa')">
                                    GPA
                                    <span v-if="sortField === 'gpa'">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
                                </button>
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="(std, idx) in (result?.details || [])" :key="'std-' + std.id">
                            <td class="px-3 py-2 text-slate-800">{{ idx + 1 }}</td>
                            <td class="px-3 py-2 text-slate-800">
                                <button type="button" class="text-sky-700 hover:underline" @click="goMarksheet(std.id)">{{ std.student_id || '' }}</button>
                            </td>
                            <td class="px-3 py-2 text-slate-800">{{ std.name || '' }}</td>
                            <td class="px-3 py-2 text-slate-800">{{ std.college_roll || '' }}</td>

                            <template v-if="filters.type === 'unmerit'">
                                <td class="px-3 py-2 text-slate-800">{{ std.failed_subject_count ?? '' }}</td>
                                <td class="px-3 py-2 text-slate-800">
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="m in (std.marks || [])" :key="'m-f-' + std.id + '-' + m.subject_id" class="rounded-md border border-rose-200 bg-rose-50 px-2 py-0.5 text-xs text-rose-700">
                                            {{ m?.subject?.name_en || '' }}
                                        </span>
                                    </div>
                                </td>
                            </template>

                            <template v-if="filters.type === 'all'">
                                <td class="px-3 py-2 text-slate-800">{{ std.passed_subject_count ?? '' }}</td>
                                <td class="px-3 py-2 text-slate-800">
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="m in (std.marks || [])" :key="'m-p-' + std.id + '-' + m.subject_id" class="rounded-md border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs text-emerald-700">
                                            {{ m?.subject?.name_en || '' }}
                                        </span>
                                    </div>
                                </td>
                            </template>

                            <td class="px-3 py-2 text-slate-800">{{ std.total_mark ?? '' }}</td>
                            <td class="px-3 py-2 text-slate-800">{{ std.letter_grade ?? '' }}</td>
                            <td class="px-3 py-2 text-slate-800">{{ std.gpa ?? '' }}</td>
                        </tr>

                        <tr v-if="!(result?.details || []).length">
                            <td :colspan="tableColspan" class="px-3 py-10 text-center text-slate-500">No data found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ResultReport',
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
            printing: false,
            exportingExcel: false,
            downloadingSingle: false,
            downloadingAll: false,
            bulkExportId: null,
            bulkExportStatus: null,
            bulkExportMessage: '',
            bulkPollTimer: null,
            result: null,
            excelHeader: [],
            sortField: '',
            sortOrder: 'asc',
            filters: {
                result_id: '',
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
                exam_id: '',
                type: 'select',
                failed_subject: '',
                passed_subject: '',
                exclude_fourth_subject: '',
                field_name: '',
                search_keyword: '',
            },
        }
    },
    computed: {
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
        exams() {
            const list = this.systems?.global?.exams
            return Array.isArray(list) ? list : []
        },
        tableColspan() {
            let n = 0
            n += 4 // #, student_id, name, roll
            if (this.filters.type === 'unmerit') n += 2
            if (this.filters.type === 'all') n += 2
            n += 3 // total, grade, gpa
            return n
        },
    },
    async mounted() {
        const p = new URLSearchParams(window.location.search || '')
        const resultId = p.get('result_id')
        if (resultId) {
            this.filters.result_id = String(resultId)
            await this.bootstrapResultById(String(resultId))
        }
    },
    methods: {
        triggerDownload(url) {
            try {
                const iframe = document.createElement('iframe')
                iframe.style.display = 'none'
                iframe.src = url
                document.body.appendChild(iframe)
                setTimeout(() => {
                    try {
                        document.body.removeChild(iframe)
                    } catch (e) {
                        // ignore
                    }
                }, 60000)
            } catch (e) {
                window.location.href = url
            }
        },
        goBack() {
            window.location.href = '/admin/result'
        },
        goMarksheet(detailId) {
            window.location.href = `/admin/result-marksheet/${detailId}`
        },
        clearBulkPollTimer() {
            if (this.bulkPollTimer) {
                clearInterval(this.bulkPollTimer)
                this.bulkPollTimer = null
            }
        },
        async pollBulkMarksheet() {
            if (!this.bulkExportId) return
            this.clearBulkPollTimer()

            this.bulkPollTimer = setInterval(async () => {
                try {
                    const res = await window.axios.get(`/admin/bulk-marksheet-status/${this.bulkExportId}`)
                    const status = res?.data?.status
                    const message = res?.data?.message

                    this.bulkExportStatus = status || null
                    this.bulkExportMessage = message || ''

                    if (status === 'done') {
                        this.clearBulkPollTimer()
                        this.triggerDownload(`/admin/download-bulk-marksheet-file/${this.bulkExportId}`)
                    }

                    if (status === 'failed') {
                        this.clearBulkPollTimer()
                    }
                } catch (e) {
                    // ignore transient network issues
                }
            }, 3000)
        },
        async downloadMarksheet() {
            const rid = this.result?.id
            if (!rid) return

            this.downloadingSingle = true
            try {
                // If already generated, download directly
                if (this.bulkExportStatus === 'done' && this.bulkExportId) {
                    this.triggerDownload(`/admin/download-bulk-marksheet-file/${this.bulkExportId}`)
                    return
                }

                // If running, ignore
                if (this.bulkExportStatus === 'pending' || this.bulkExportStatus === 'processing') return

                const params = {
                    result_id: rid,
                    academic_session_id: this.filters.academic_session_id,
                    academic_qualification_id: this.filters.academic_qualification_id,
                    department_id: this.filters.department_id,
                    academic_class_id: this.filters.academic_class_id,
                    exam_id: this.filters.exam_id,
                    type: this.filters.type,
                }
                const jsonStr = JSON.stringify(params)

                this.bulkExportId = null
                this.bulkExportStatus = 'pending'
                this.bulkExportMessage = 'Queuing…'

                const res = await window.axios.get(`/admin/download-bulk-marksheet`, { params: { search_params: jsonStr } })
                this.bulkExportId = res?.data?.export_id || null
                this.bulkExportStatus = 'pending'
                this.bulkExportMessage = 'Queued — waiting to start…'

                await this.pollBulkMarksheet()
            } finally {
                setTimeout(() => {
                    this.downloadingSingle = false
                }, 400)
            }
        },
        async downloadAllMarksheet() {
            const rid = this.result?.id
            if (!rid) return
            this.downloadingAll = true
            try {
                window.open(`/admin/marksheet-all/${rid}`, '_blank')
            } finally {
                setTimeout(() => {
                    this.downloadingAll = false
                }, 400)
            }
        },
        exportExcel() {
            this.exportingExcel = true
            try {
                const rows = Array.isArray(this.result?.details) ? this.result.details : []
                const headerLines = (this.excelHeader || []).filter(Boolean)

                const safe = (v) => String(v ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')

                const cols = [
                    '#',
                    'Software ID',
                    'Student Name',
                    'College Roll',
                    this.filters.type === 'unmerit' ? 'Total Failed' : null,
                    this.filters.type === 'unmerit' ? 'Failed Subjects' : null,
                    this.filters.type === 'all' ? 'Total Passed' : null,
                    this.filters.type === 'all' ? 'Passed Subjects' : null,
                    'Total Mark',
                    'Grade',
                    'GPA',
                ].filter(Boolean)

                const html =
                    '<html><head><meta charset="UTF-8"></head><body>' +
                    (headerLines.length ? `<div>${headerLines.map((l) => `<div>${safe(l)}</div>`).join('')}</div><br/>` : '') +
                    '<table border="1"><thead><tr>' +
                    cols.map((c) => `<th>${safe(c)}</th>`).join('') +
                    '</tr></thead><tbody>' +
                    rows
                        .map((r, i) => {
                            const base = [
                                i + 1,
                                r.student_id,
                                r.name,
                                r.college_roll,
                            ]

                            if (this.filters.type === 'unmerit') {
                                base.push(r.failed_subject_count)
                                base.push((r.marks || []).map((m) => m?.subject?.name_en).filter(Boolean).join(', '))
                            }

                            if (this.filters.type === 'all') {
                                base.push(r.passed_subject_count)
                                base.push((r.marks || []).map((m) => m?.subject?.name_en).filter(Boolean).join(', '))
                            }

                            base.push(r.total_mark)
                            base.push(r.letter_grade)
                            base.push(r.gpa)

                            return '<tr>' + base.map((v) => `<td>${safe(v)}</td>`).join('') + '</tr>'
                        })
                        .join('') +
                    '</tbody></table></body></html>'

                const blob = new Blob([html], { type: 'application/vnd.ms-excel' })
                const url = URL.createObjectURL(blob)
                const a = document.createElement('a')
                a.href = url
                a.download = `result_report_${new Date().toISOString().slice(0, 19).replace(/[:T]/g, '_')}.xls`
                document.body.appendChild(a)
                a.click()
                document.body.removeChild(a)
                URL.revokeObjectURL(url)
            } finally {
                setTimeout(() => {
                    this.exportingExcel = false
                }, 150)
            }
        },
        sortBy(field) {
            if (!this.result || !Array.isArray(this.result.details)) return
            if (this.sortField === field) {
                this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc'
            } else {
                this.sortField = field
                this.sortOrder = 'asc'
            }

            const dir = this.sortOrder === 'asc' ? 1 : -1
            const num = (v) => {
                const n = Number(v)
                return Number.isFinite(n) ? n : 0
            }
            const str = (v) => String(v ?? '')

            this.result.details.sort((a, b) => {
                let va = a?.[field]
                let vb = b?.[field]

                if (field === 'college_roll') {
                    va = num(va)
                    vb = num(vb)
                } else if (field === 'total_mark' || field === 'gpa') {
                    va = num(va)
                    vb = num(vb)
                } else {
                    va = str(va)
                    vb = str(vb)
                }

                if (va === vb && field === 'gpa') {
                    const ta = num(a?.total_mark)
                    const tb = num(b?.total_mark)
                    if (ta !== tb) return ta > tb ? dir : -dir
                }

                if (va === vb) return 0
                return va > vb ? dir : -dir
            })
        },
        async bootstrapResultById(resultId) {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/result/${resultId}/details`)
                const r = res?.data || null
                if (r) {
                    this.filters.academic_session_id = String(r.academic_session_id || '')
                    this.filters.academic_qualification_id = String(r.academic_qualification_id || '')
                    this.filters.department_id = String(r.department_id || '')
                    this.filters.academic_class_id = String(r.academic_class_id || '')
                    this.filters.exam_id = String(r.exam_id || '')
                }
                this.result = null
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load result.'
            } finally {
                this.loading = false
            }
        },
        async search() {
            this.loading = true
            this.error = ''
            try {
                this.clearBulkPollTimer()
                this.bulkExportId = null
                this.bulkExportStatus = null
                this.bulkExportMessage = ''

                const res = await window.axios.get('/admin/result-report/list', { params: this.filters })
                this.result = res?.data?.result || null
                this.excelHeader = Array.isArray(res?.data?.excel_header) ? res.data.excel_header : []
                this.sortField = ''
                this.sortOrder = 'asc'
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load report.'
                this.result = null
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
    },
    beforeUnmount() {
        this.clearBulkPollTimer()
    },
}
</script>
