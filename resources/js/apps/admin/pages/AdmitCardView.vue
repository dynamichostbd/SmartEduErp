<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Admit Card</div>
                    <div class="mt-1 text-sm text-slate-600">View admit card</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goBack">
                        Back
                    </button>
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goEdit">
                        Edit
                    </button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="text-sm font-semibold text-slate-900">Details</div>
            <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <tbody class="divide-y divide-slate-200">
                        <tr>
                            <th class="w-40 bg-slate-50 px-3 py-2 text-left font-semibold text-slate-700">Name</th>
                            <td class="px-3 py-2 text-slate-800">{{ admitCard.name || 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-3 py-2 text-left font-semibold text-slate-700">Session</th>
                            <td class="px-3 py-2 text-slate-800">{{ admitCard.academic_session_name || 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-3 py-2 text-left font-semibold text-slate-700">Academic Level</th>
                            <td class="px-3 py-2 text-slate-800">{{ admitCard.academic_qualification_name || 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-3 py-2 text-left font-semibold text-slate-700">Department</th>
                            <td class="px-3 py-2 text-slate-800">{{ admitCard.department_name || 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-3 py-2 text-left font-semibold text-slate-700">Class</th>
                            <td class="px-3 py-2 text-slate-800">{{ admitCard.academic_class_name || 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-3 py-2 text-left font-semibold text-slate-700">Exam</th>
                            <td class="px-3 py-2 text-slate-800">{{ admitCard.exam_name || 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-3 py-2 text-left font-semibold text-slate-700">Issue Date</th>
                            <td class="px-3 py-2 text-slate-800">{{ admitCard.issue_date || 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-3 py-2 text-left font-semibold text-slate-700">Expired Date</th>
                            <td class="px-3 py-2 text-slate-800">{{ admitCard.expired_date || 'N/A' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AdmitCardView',
    props: {
        admitCardId: {
            type: [Number, String],
            required: true,
        },
    },
    data() {
        return {
            loading: false,
            error: '',
            admitCard: {},
        }
    },
    async mounted() {
        await this.load()
    },
    methods: {
        goBack() {
            window.location.href = '/admin/admitCard'
        },
        goEdit() {
            window.location.href = `/admin/admitCard/${this.admitCardId}/edit`
        },
        async load() {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/admitCard/${this.admitCardId}/details`)
                this.admitCard = res?.data?.admit_card || {}
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load.'
                this.admitCard = {}
            } finally {
                this.loading = false
            }
        },
    },
}
</script>
