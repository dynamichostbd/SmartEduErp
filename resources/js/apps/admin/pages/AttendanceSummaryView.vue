<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Attendance Summary</div>
                    <div class="mt-1 text-sm text-slate-600">View summary</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goBack">
                        Back
                    </button>
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="!details.length" @click="printReport">
                        Print
                    </button>
                    <button type="button" class="rounded-sm bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700" :disabled="submitting || !details.length" @click="downloadAllAdmitCards">
                        {{ submitting ? 'Processing...' : 'Download All Admit Cards' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div id="printArea" class="flex flex-col gap-4">
            <div class=" border border-slate-300 bg-white p-5">
                <div class="text-sm font-semibold text-slate-900">Attendance Info</div>
                <div class="mt-3 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <tbody class="divide-y divide-slate-200">
                            <tr>
                                <th class="w-40 bg-slate-50 px-3 py-2 text-left font-semibold text-slate-700">From Date</th>
                                <td class="px-3 py-2 text-slate-800">{{ summary.from_date || 'N/A' }}</td>
                                <th class="w-40 bg-slate-50 px-3 py-2 text-left font-semibold text-slate-700">To Date</th>
                                <td class="px-3 py-2 text-slate-800">{{ summary.to_date || 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-slate-50 px-3 py-2 text-left font-semibold text-slate-700">Session</th>
                                <td class="px-3 py-2 text-slate-800">{{ summary.academic_session_name || 'N/A' }}</td>
                                <th class="bg-slate-50 px-3 py-2 text-left font-semibold text-slate-700">Academic Level</th>
                                <td class="px-3 py-2 text-slate-800">{{ summary.academic_qualification_name || 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-slate-50 px-3 py-2 text-left font-semibold text-slate-700">Department</th>
                                <td class="px-3 py-2 text-slate-800">{{ summary.department_name || 'N/A' }}</td>
                                <th class="bg-slate-50 px-3 py-2 text-left font-semibold text-slate-700">Class</th>
                                <td class="px-3 py-2 text-slate-800">{{ summary.academic_class_name || 'N/A' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class=" border border-slate-300 bg-white">
                <div class="border-b border-slate-300 px-5 py-4">
                    <div class="text-sm font-semibold text-slate-900">Students Attendance Summary</div>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-emerald-100">
                                <tr>
                                    <th class="px-3 py-2 text-center font-semibold text-slate-700">#</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-700">Software ID</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-700">Student Name</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-700">Mobile</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-700">Admission ID</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-700">College Roll</th>
                                    <th class="px-3 py-2 text-center font-semibold text-slate-700">Total Present</th>
                                    <th class="px-3 py-2 text-center font-semibold text-slate-700">Total Absent</th>
                                    <th class="px-3 py-2 text-center font-semibold text-slate-700">Present %</th>
                                    <th class="px-3 py-2 text-center font-semibold text-slate-700">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                <tr v-for="(d, idx) in details" :key="'d-' + String(d.student_id) + '-' + idx">
                                    <td class="px-3 py-2 text-center text-slate-600">{{ idx + 1 }}</td>
                                    <td class="px-3 py-2 text-slate-800">{{ d.student_id }}</td>
                                    <td class="px-3 py-2 text-slate-800">{{ d.student?.name || '--' }}</td>
                                    <td class="px-3 py-2 text-slate-800">{{ d.student?.mobile || '--' }}</td>
                                    <td class="px-3 py-2 text-slate-800">{{ d.student?.admission_id || '--' }}</td>
                                    <td class="px-3 py-2 text-slate-800">{{ d.student?.college_roll || '--' }}</td>
                                    <td class="px-3 py-2 text-center text-emerald-700"><b>{{ d.total_present }}</b></td>
                                    <td class="px-3 py-2 text-center text-rose-700"><b>{{ d.total_absent }}</b></td>
                                    <td class="px-3 py-2 text-center text-amber-700"><b>{{ d.present_percentage }}%</b></td>
                                    <td class="px-3 py-2 text-center">
                                        <span class="rounded-sm px-2 py-1 text-xs font-semibold" :class="d.status === 'P' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'">
                                            {{ d.status }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="!details.length">
                                    <td colspan="10" class="px-3 py-10 text-center text-slate-500">No Students Found</td>
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
    name: 'AttendanceSummaryView',
    props: {
        summaryId: {
            type: [Number, String],
            required: true,
        },
    },
    data() {
        return {
            loading: false,
            submitting: false,
            error: '',
            summary: {},
            details: [],
        }
    },
    async mounted() {
        await this.load()
    },
    methods: {
        goBack() {
            window.location.href = '/admin/attendanceSummary'
        },
        async load() {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/attendanceSummary/${this.summaryId}/details`)
                this.summary = res?.data?.summary || {}
                this.details = Array.isArray(res?.data?.details) ? res.data.details : []
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load.'
                this.summary = {}
                this.details = []
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
            w.document.write('<html><head><title>Attendance Summary</title>')
            w.document.write('<style>body{font-family: Arial, sans-serif; padding: 20px;} table{width:100%; border-collapse: collapse;} th,td{border:1px solid #333; padding:6px; font-size:12px;} th{text-align:left;} .text-center{text-align:center;}</style>')
            w.document.write('</head><body>')
            w.document.write(html)
            w.document.write('</body></html>')
            w.document.close()
            w.focus()
            w.print()
            w.close()
        },

        async downloadAllAdmitCards() {
            if (!this.details.length) {
                return
            }

            const studentIds = this.details
                .map((d) => d.student_id)
                .filter((id) => id !== null && id !== undefined && String(id) !== '')

            if (!studentIds.length) {
                this.error = 'No valid student IDs found.'
                return
            }

            const payload = {
                student_ids: studentIds,
                academic_session_id: this.summary?.academic_session_id,
                academic_class_id: this.summary?.academic_class_id,
            }

            this.submitting = true
            this.error = ''
            try {
                const response = await window.axios.post('/admin/download-all-admit-cards', payload, { responseType: 'blob' })
                const url = window.URL.createObjectURL(new Blob([response.data]))
                const link = document.createElement('a')
                link.href = url
                link.setAttribute('download', `admit-cards-${new Date().getTime()}.zip`)
                document.body.appendChild(link)
                link.click()
                link.remove()
                window.URL.revokeObjectURL(url)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Error downloading admit cards.'
            } finally {
                this.submitting = false
            }
        },
    },
}
</script>
