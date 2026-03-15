<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 print:hidden">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Class Test Marksheet</div>
                    <div class="mt-1 text-sm text-slate-600">Student marksheet</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goBack">Back</button>
                    <button type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" :disabled="printing" @click="print">{{ printing ? '...' : 'Print' }}</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 print:hidden">{{ error }}</div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <div class="flex flex-col gap-2">
                <div class="text-lg font-semibold text-slate-900">Marksheet</div>

                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3 text-sm text-slate-700">
                    <div><span class="font-semibold">Software ID:</span> {{ data?.student?.student_id || '' }}</div>
                    <div><span class="font-semibold">Name:</span> {{ data?.student?.name || '' }}</div>
                    <div><span class="font-semibold">College Roll:</span> {{ data?.student?.college_roll || '' }}</div>
                    <div><span class="font-semibold">Session:</span> {{ data?.class_test_result?.academic_session?.name || '' }}</div>
                    <div><span class="font-semibold">Level:</span> {{ data?.class_test_result?.qualification?.name || '' }}</div>
                    <div><span class="font-semibold">Department:</span> {{ data?.class_test_result?.department?.name || '' }}</div>
                    <div><span class="font-semibold">Class:</span> {{ data?.class_test_result?.academic_class?.name || '' }}</div>
                    <div><span class="font-semibold">Exam:</span> {{ data?.class_test_result?.exam?.name || '' }}</div>
                </div>

                <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Subject</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Exam Mark</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Pass Mark</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Obtain Mark</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            <tr v-for="m in (data?.marks || [])" :key="'m-' + m.subject_id">
                                <td class="px-3 py-2 text-slate-800">{{ m?.subject?.name_en || '' }}</td>
                                <td class="px-3 py-2 text-slate-800">{{ m.exam_mark ?? '' }}</td>
                                <td class="px-3 py-2 text-slate-800">{{ m.pass_mark ?? '' }}</td>
                                <td class="px-3 py-2 text-slate-800">{{ m.mark ?? '' }}</td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" :class="m.result_status === 'PASSED' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'">{{ m.result_status || '' }}</span>
                                </td>
                            </tr>
                            <tr v-if="!(data?.marks || []).length">
                                <td colspan="5" class="px-3 py-10 text-center text-slate-500">No marks found</td>
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
    name: 'ClassTestResultMarksheet',
    props: {
        detailId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            loading: false,
            error: '',
            printing: false,
            data: null,
        }
    },
    async mounted() {
        await this.load()
    },
    methods: {
        goBack() {
            window.history.back()
        },
        async load() {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/classTestResult-marksheet/${this.detailId}`)
                this.data = res?.data || null
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load marksheet.'
                this.data = null
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
}
</script>
