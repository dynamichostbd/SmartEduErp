<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Result</div>
                    <div class="mt-1 text-sm text-slate-600">View result list</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goBack">Back</button>
                    <button type="button" class="rounded-sm bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" :disabled="printing" @click="print">{{ printing ? '...' : 'Print' }}</button>
                    <button type="button" class="rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="exporting" @click="exportPdf">{{ exporting ? '...' : 'Export PDF' }}</button>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class=" border border-slate-300 bg-white p-5">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Type</div>
                    <select v-model="filters.type" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" @change="load(true)">
                        <option value="">ALL</option>
                        <option value="merit">Merit</option>
                        <option value="unmerit">Unmerit</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Search Field</div>
                    <select v-model="filters.field_name" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" @change="load(true)">
                        <option value="">--Select One--</option>
                        <option value="student_id">Software ID</option>
                        <option value="name">Name</option>
                        <option value="mobile">Mobile</option>
                        <option value="college_roll">College Roll</option>
                    </select>
                </div>

                <div class="lg:col-span-4">
                    <div class="text-xs font-semibold text-slate-600">Search</div>
                    <input v-model="filters.value" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" @keydown.enter.prevent="load(true)" />
                </div>

                <div class="lg:col-span-2 flex items-end">
                    <button type="button" class="h-9 w-full rounded-sm bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800" :disabled="loading" @click="load(true)">{{ loading ? '...' : 'Go' }}</button>
                </div>
            </div>
        </div>

        <div class=" border border-slate-300 bg-white p-5">
            <div class="mb-4 rounded-xl border border-slate-300 bg-slate-50 p-4 text-sm text-slate-700">
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-5">
                    <div><span class="font-semibold">Session:</span> {{ result?.academic_session?.name || '--' }}</div>
                    <div><span class="font-semibold">Level:</span> {{ result?.qualification?.name || '--' }}</div>
                    <div><span class="font-semibold">Dept:</span> {{ result?.department?.name || '--' }}</div>
                    <div><span class="font-semibold">Class:</span> {{ result?.academic_class?.name || '--' }}</div>
                    <div><span class="font-semibold">Exam:</span> {{ result?.exam?.name || '--' }}</div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table id="pdf-table" class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-emerald-100">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Roll</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Software ID</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Name</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Total Mark</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">GPA</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Grade</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Result</th>
                            <template v-if="filters.type === 'unmerit'">
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Total Failed</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Failed Subjects</th>
                            </template>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Marksheet</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="std in result?.details || []" :key="'std-' + std.id">
                            <td class="px-3 py-2 text-slate-800">{{ std.college_roll || '' }}</td>
                            <td class="px-3 py-2 text-slate-800">{{ std.student_id || '' }}</td>
                            <td class="px-3 py-2 text-slate-800">{{ std.name || '' }}</td>
                            <td class="px-3 py-2 text-slate-800">{{ std.total_mark ?? '' }}</td>
                            <td class="px-3 py-2 text-slate-800">{{ std.gpa ?? '' }}</td>
                            <td class="px-3 py-2 text-slate-800">{{ std.letter_grade ?? '' }}</td>
                            <td class="px-3 py-2">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" :class="std.result_status === 'PASSED' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'">{{ std.result_status || '' }}</span>
                            </td>
                            <template v-if="filters.type === 'unmerit'">
                                <td class="px-3 py-2 text-slate-800">{{ (std.marks || []).length }}</td>
                                <td class="px-3 py-2 text-slate-800">
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="m in (std.marks || [])" :key="'m-' + std.id + '-' + m.subject_id" class="rounded-sm border border-rose-200 bg-rose-50 px-2 py-0.5 text-xs text-rose-700">
                                            {{ m?.subject?.name_en || '' }}
                                        </span>
                                    </div>
                                </td>
                            </template>
                            <td class="px-3 py-2 text-center">
                                <button type="button" class="rounded-sm border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50" @click="goMarksheet(std.id)">
                                    Marksheet
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!(result?.details || []).length">
                            <td :colspan="filters.type === 'unmerit' ? 10 : 8" class="px-3 py-10 text-center text-slate-500">No data found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ResultView',
    props: {
        resultId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            loading: false,
            error: '',
            exporting: false,
            printing: false,
            result: null,
            excelHeader: [],
            filters: {
                result_view: true,
                type: '',
                field_name: 'college_roll',
                value: '',
            },
        }
    },
    async mounted() {
        await this.load(true)
    },
    methods: {
        goBack() {
            window.location.href = '/admin/result'
        },
        goMarksheet(detailId) {
            window.location.href = `/admin/result-marksheet/${detailId}`
        },
        async load(reset) {
            if (reset) {
                this.filters.value = this.filters.value || ''
            }

            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/result/${this.resultId}/details`, { params: this.filters })
                this.result = res?.data?.result || null
                this.excelHeader = Array.isArray(res?.data?.excel_header) ? res.data.excel_header : []
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load result.'
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
        async exportPdf() {
            this.exporting = true
            try {
                const mod = await import('jspdf')
                const jsPDF = mod?.jsPDF
                const auto = await import('jspdf-autotable')
                const autoTable = auto?.default
                if (!jsPDF || !autoTable) return

                const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' })

                const headerText = (this.excelHeader || []).filter(Boolean).join('\n')
                if (headerText) {
                    doc.setFontSize(10)
                    doc.text(headerText, 14, 12)
                }

                autoTable(doc, {
                    html: '#pdf-table',
                    startY: headerText ? 24 : 12,
                    margin: { left: 8, right: 8 },
                    styles: { fontSize: 8 },
                })

                doc.save('result.pdf')
            } finally {
                this.exporting = false
            }
        },
    },
}
</script>
