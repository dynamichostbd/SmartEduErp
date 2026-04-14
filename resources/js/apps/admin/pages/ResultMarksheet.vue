<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5 print:hidden">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Marksheet</div>
                    <div class="mt-1 text-sm text-slate-600">Student marksheet</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goBack">Back</button>
                    <button type="button" class="rounded-sm bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" :disabled="printing" @click="print">{{ printing ? '...' : 'Print' }}</button>
                    <button type="button" class="rounded-sm bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700" :disabled="downloading" @click="download">{{ downloading ? '...' : 'Download Marksheet' }}</button>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800 print:hidden">{{ error }}</div>

        <div class=" border border-slate-300 bg-white p-6">
            <div class="relative flex flex-col gap-2">
                <img v-if="marksheetBgUrl" :src="marksheetBgUrl" alt="" class="pointer-events-none absolute inset-0 h-full w-full object-contain opacity-20" />
                <div class="relative">
                <div class="flex items-center justify-between gap-3 border-b border-slate-300 pb-3">
                    <div class="flex items-center gap-3">
                        <img v-if="siteLogoUrl" :src="siteLogoUrl" alt="" class="h-9 w-10 object-contain" />
                        <div class="text-lg font-semibold text-slate-900">{{ siteName || '' }}</div>
                    </div>
                    <div class="text-sm font-semibold text-slate-700">{{ data?.result?.exam?.name || '' }}</div>
                </div>

                <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3 text-sm text-slate-700">
                    <div><span class="font-semibold">Software ID:</span> {{ data?.student?.student_id || '' }}</div>
                    <div><span class="font-semibold">Name:</span> {{ data?.student?.name || '' }}</div>
                    <div><span class="font-semibold">College Roll:</span> {{ data?.student?.college_roll || '' }}</div>
                </div>

                <div class="mt-3 overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm text-slate-700">
                        <tbody>
                            <tr>
                                <td class="w-40 border border-slate-200 bg-slate-50 px-3 py-2 font-semibold">Session</td>
                                <td class="border border-slate-200 px-3 py-2">{{ data?.result?.academic_session?.name || '' }}</td>
                                <td class="w-40 border border-slate-200 bg-slate-50 px-3 py-2 font-semibold">Academic Level</td>
                                <td class="border border-slate-200 px-3 py-2">{{ data?.result?.qualification?.name || '' }}</td>
                            </tr>
                            <tr>
                                <td class="w-40 border border-slate-200 bg-slate-50 px-3 py-2 font-semibold">Department/Group</td>
                                <td class="border border-slate-200 px-3 py-2">{{ data?.result?.department?.name || '' }}</td>
                                <td class="w-40 border border-slate-200 bg-slate-50 px-3 py-2 font-semibold">Class</td>
                                <td class="border border-slate-200 px-3 py-2">{{ data?.result?.academic_class?.name || '' }}</td>
                            </tr>
                            <tr>
                                <td class="w-40 border border-slate-200 bg-slate-50 px-3 py-2 font-semibold">Exam</td>
                                <td class="border border-slate-200 px-3 py-2" colspan="3">{{ data?.result?.exam?.name || '' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4 text-sm text-slate-700">
                    <div><span class="font-semibold">Total Mark:</span> {{ data?.result_details?.total_mark ?? '' }}</div>
                    <div><span class="font-semibold">GPA:</span> {{ data?.result_details?.gpa ?? '' }}</div>
                    <div><span class="font-semibold">Grade:</span> {{ data?.result_details?.letter_grade ?? '' }}</div>
                    <div><span class="font-semibold">Status:</span> {{ data?.result_details?.result_status ?? '' }}</div>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm">
                        <thead class="bg-emerald-100">
                            <tr>
                                <th class="border border-slate-300 px-3 py-2 text-left font-semibold text-slate-700">SI.</th>
                                <th class="border border-slate-300 px-3 py-2 text-left font-semibold text-slate-700" colspan="2">Name of Subjects</th>
                                <th v-if="data?.result?.has_ct_mark" class="border border-slate-300 px-3 py-2 text-left font-semibold text-slate-700">CT</th>
                                <th class="border border-slate-300 px-3 py-2 text-center font-semibold text-slate-700">CQ</th>
                                <th class="border border-slate-300 px-3 py-2 text-center font-semibold text-slate-700">MCQ</th>
                                <th class="border border-slate-300 px-3 py-2 text-center font-semibold text-slate-700">Prac.</th>
                                <th class="border border-slate-300 px-3 py-2 text-center font-semibold text-slate-700">Obtained</th>
                                <th class="border border-slate-300 px-3 py-2 text-center font-semibold text-slate-700">Total Obtained</th>
                                <th class="border border-slate-300 px-3 py-2 text-center font-semibold text-slate-700">Letter Grade</th>
                                <th class="border border-slate-300 px-3 py-2 text-center font-semibold text-slate-700">Grade Point</th>
                                <th class="border border-slate-300 px-3 py-2 text-center font-semibold text-slate-700">GPA</th>
                                <th class="border border-slate-300 px-3 py-2 text-center font-semibold text-slate-700">GRADE</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <tr v-for="(mark, key) in (data?.marks || [])" :key="'mk-row-' + key" :class="key % 2 === 1 ? 'bg-slate-50' : 'bg-white'">
                                <td v-if="shouldShowMergedCell(data?.marks, key)" :rowspan="getMergedCellRowspan(data?.marks, key)" class="border border-slate-300 px-3 py-2 text-center text-slate-800">
                                    {{ getGroupIndex(data?.marks, key) }}
                                </td>

                                <td
                                    v-if="mark?.parent_subject_name && shouldShowMergedCell(data?.marks, key)"
                                    :rowspan="getMergedCellRowspan(data?.marks, key)"
                                    class="border border-slate-300 px-3 py-2 text-slate-800"
                                    :class="Number(mark?.is_fourth_subject || 0) === 1 ? 'text-red-600' : ''"
                                >
                                    {{ mark?.parent_subject_name || '' }}
                                    <span v-if="Number(mark?.is_fourth_subject || 0) === 1">(4th)</span>
                                </td>

                                <td v-if="!mark?.parent_subject_name" colspan="2" class="border border-slate-300 px-3 py-2 text-slate-800" :class="Number(mark?.is_fourth_subject || 0) === 1 ? 'text-red-600' : ''">
                                    {{ mark?.subject?.name_en || '' }}
                                    <span v-if="Number(mark?.is_fourth_subject || 0) === 1">(4th)</span>
                                </td>
                                <td v-else class="border border-slate-300 px-3 py-2 text-slate-800">{{ mark?.subject?.name_en || '' }}</td>

                                <td v-if="data?.result?.has_ct_mark" class="border border-slate-300 px-3 py-2 text-center text-slate-800">
                                    <span v-if="Number(mark?.is_absent || 0) === 1 && (mark?.ct_mark ?? null) === null && mark?.ct_allocated" class="text-red-600">ABS</span>
                                    <span v-else>{{ mark?.ct_mark ?? '' }}</span>
                                </td>
                                <td class="border border-slate-300 px-3 py-2 text-center text-slate-800">
                                    <span v-if="Number(mark?.is_absent || 0) === 1 && (mark?.cq_mark ?? null) === null && mark?.cq_allocated" class="text-red-600">ABS</span>
                                    <span v-else>{{ mark?.cq_mark ?? '' }}</span>
                                </td>
                                <td class="border border-slate-300 px-3 py-2 text-center text-slate-800">
                                    <span v-if="Number(mark?.is_absent || 0) === 1 && (mark?.mcq_mark ?? null) === null && mark?.mcq_allocated" class="text-red-600">ABS</span>
                                    <span v-else>{{ mark?.mcq_mark ?? '' }}</span>
                                </td>
                                <td class="border border-slate-300 px-3 py-2 text-center text-slate-800">
                                    <span v-if="Number(mark?.is_absent || 0) === 1 && (mark?.practical_mark ?? null) === null && mark?.practical_allocated" class="text-red-600">ABS</span>
                                    <span v-else>{{ mark?.practical_mark ?? '' }}</span>
                                </td>
                                <td class="border border-slate-300 px-3 py-2 text-center text-slate-800">{{ mark?.obtained_mark ?? '' }}</td>

                                <td v-if="shouldShowMergedCell(data?.marks, key)" :rowspan="getMergedCellRowspan(data?.marks, key)" class="border border-slate-300 px-3 py-2 text-center text-slate-800">
                                    {{ mark?.total_mark ?? '' }}
                                </td>
                                <td v-if="shouldShowMergedCell(data?.marks, key)" :rowspan="getMergedCellRowspan(data?.marks, key)" class="border border-slate-300 px-3 py-2 text-center text-slate-800">
                                    {{ mark?.letter_grade ?? '' }}
                                </td>
                                <td v-if="shouldShowMergedCell(data?.marks, key)" :rowspan="getMergedCellRowspan(data?.marks, key)" class="border border-slate-300 px-3 py-2 text-center text-slate-800">
                                    {{ mark?.gpa ?? '' }}
                                </td>

                                <td v-if="key === 0" class="border border-slate-300 px-3 py-2 text-center text-slate-800" :rowspan="(data?.marks || []).length">
                                    {{ data?.result_details?.gpa ?? '' }}
                                </td>
                                <td v-if="key === 0" class="border border-slate-300 px-3 py-2 text-center text-slate-800" :rowspan="(data?.marks || []).length">
                                    {{ data?.result_details?.letter_grade ?? '' }}
                                </td>
                            </tr>

                            <tr v-if="!(data?.marks || []).length">
                                <td colspan="13" class="border border-slate-300 px-3 py-10 text-center text-slate-500">No marks found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ResultMarksheet',
    props: {
        detailId: {
            type: [Number, String],
            default: null,
        },
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
            downloading: false,
            data: null,
            _abortController: null,
            _loadSeq: 0,
            _inflightKey: null,
        }
    },
    computed: {
        siteName() {
            return this.systems?.site?.college_name || this.systems?.site?.school_name || ''
        },
        marksheetBgRaw() {
            const s = this.systems?.site || {}
            return s.marksheet_image || ''
        },
        siteLogoRaw() {
            const s = this.systems?.site || {}
            return s.logo || s.site_logo || s.college_logo || s.school_logo || s.logo_path || ''
        },
        marksheetBgUrl() {
            const raw = String(this.marksheetBgRaw || '').trim()
            if (!raw) return ''
            if (/^https?:\/\//i.test(raw)) return raw
            if (raw.startsWith('/')) return raw
            return `/storage/upload/${raw.replace(/^storage\//, '').replace(/^upload\//, '')}`
        },
        siteLogoUrl() {
            const raw = String(this.siteLogoRaw || '').trim()
            if (!raw) return ''
            if (/^https?:\/\//i.test(raw)) return raw
            if (raw.startsWith('/')) return raw
            return `/storage/upload/${raw.replace(/^storage\//, '').replace(/^upload\//, '')}`
        },
    },
    async mounted() {
        await this.load()
    },
    watch: {
        detailId() {
            this.load()
        },
    },
    beforeUnmount() {
        this.cancelLoad()
    },
    methods: {
        cancelLoad() {
            try {
                if (this._abortController) {
                    this._abortController.abort()
                }
            } catch (e) {
                // ignore
            } finally {
                this._abortController = null
                this._inflightKey = null
            }
        },
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
        shouldShowMergedCell(marks, index) {
            const list = Array.isArray(marks) ? marks : []
            if (!list[index]) return true
            if (index === 0) return true

            const current = list[index]
            const previous = list[index - 1]

            if (!current?.subject || !current?.subject?.parent_id) return true
            if (!previous?.subject || current.subject.parent_id !== previous.subject.parent_id) return true

            return false
        },
        getMergedCellRowspan(marks, index) {
            const list = Array.isArray(marks) ? marks : []
            if (!list[index]) return 1
            const parentId = list[index]?.subject?.parent_id
            if (!parentId) return 1

            let count = 1
            for (let i = index + 1; i < list.length; i++) {
                if (list[i]?.subject?.parent_id === parentId) count++
                else break
            }
            return count
        },
        getGroupIndex(marks, index) {
            const list = Array.isArray(marks) ? marks : []
            let count = 0
            for (let i = 0; i <= index; i++) {
                if (this.shouldShowMergedCell(list, i)) count++
            }
            return count
        },
        goBack() {
            window.history.back()
        },
        async load() {
            if (!this.detailId) {
                this.data = null
                return
            }

            const key = String(this.detailId)
            if (this.loading && this._inflightKey === key) return

            this.cancelLoad()
            this._abortController = new AbortController()
            this._inflightKey = key
            const seq = (this._loadSeq += 1)

            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/result-marksheet-data/${this.detailId}`, {
                    signal: this._abortController.signal,
                })
                if (seq !== this._loadSeq) return
                this.data = res?.data || null
            } catch (e) {
                if (e?.name === 'CanceledError' || e?.name === 'AbortError' || window.axios?.isCancel?.(e)) return
                this.error = e?.response?.data?.message || 'Failed to load marksheet.'
                this.data = null
            } finally {
                if (seq === this._loadSeq) {
                    this.loading = false
                    this._inflightKey = null
                }
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
        async download() {
            if (!this.detailId) return
            this.downloading = true
            try {
                const url = `/admin/download-marksheet/${this.detailId}?view=1`
                const win = window.open(url, '_blank')
                if (!win) window.location.href = url
            } finally {
                setTimeout(() => {
                    this.downloading = false
                }, 400)
            }
        },
    },
}
</script>
