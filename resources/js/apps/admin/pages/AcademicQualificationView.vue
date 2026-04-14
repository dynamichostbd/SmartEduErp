<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Academic Level Details</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading" @click="goIndex">Back</button>
                    <button type="button" class="rounded-sm bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" :disabled="loading || !qualificationId" @click="goEdit">Edit</button>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class=" border border-slate-300 bg-white p-5">
            <div v-if="loading" class="text-sm text-slate-600">Loading...</div>
            <div v-else class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-300 p-4">
                    <div class="text-xs font-semibold text-slate-500">Qualification Name</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.name || '—' }}</div>
                </div>

                <div class="rounded-xl border border-slate-300 p-4">
                    <div class="text-xs font-semibold text-slate-500">Status</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.status || '—' }}</div>
                </div>

                <div class="rounded-xl border border-slate-300 p-4">
                    <div class="text-xs font-semibold text-slate-500">Registration</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ yesNo(row.registration) }}</div>
                </div>

                <div class="rounded-xl border border-slate-300 p-4">
                    <div class="text-xs font-semibold text-slate-500">Online Admission</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ yesNo(row.online_admission) }}</div>
                </div>

                <div class="rounded-xl border border-slate-300 p-4">
                    <div class="text-xs font-semibold text-slate-500">Check Application Fees</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ yesNo(row.application_fees) }}</div>
                </div>

                <div class="rounded-xl border border-slate-300 p-4">
                    <div class="text-xs font-semibold text-slate-500">Admission Roll Verify</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ yesNo(row.admission_roll_verify) }}</div>
                </div>

                <div class="rounded-xl border border-slate-300 p-4">
                    <div class="text-xs font-semibold text-slate-500">Subject Choose</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ yesNo(row.subject_choose) }}</div>
                </div>

                <div class="rounded-xl border border-slate-300 p-4">
                    <div class="text-xs font-semibold text-slate-500">Migration</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ yesNo(row.migration) }}</div>
                </div>

                <div class="rounded-xl border border-slate-300 p-4">
                    <div class="text-xs font-semibold text-slate-500">Application Fee</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ yesNo(row.application_fee) }}</div>
                </div>

                <div class="rounded-xl border border-slate-300 p-4 sm:col-span-2">
                    <div class="text-xs font-semibold text-slate-500">Description</div>
                    <div class="mt-1 whitespace-pre-wrap text-sm text-slate-800">{{ row.description || '—' }}</div>
                </div>

                <div class="rounded-xl border border-slate-300 p-4 sm:col-span-2">
                    <div class="text-xs font-semibold text-slate-500">Commitment</div>
                    <div class="mt-1 whitespace-pre-wrap text-sm text-slate-800">{{ row.commitment || '—' }}</div>
                </div>

                <div class="rounded-xl border border-slate-300 p-4 sm:col-span-2">
                    <div class="text-xs font-semibold text-slate-500">Admission Files</div>
                    <div v-if="!admissionFiles.length" class="mt-1 text-sm text-slate-600">—</div>
                    <div v-else class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <div v-for="(f, idx) in admissionFiles" :key="f.key || idx" class="rounded-sm border border-slate-300 bg-slate-50 p-3">
                            <div class="text-sm font-semibold text-slate-900">{{ f.name_bn || f.name_en || f.key }}</div>
                            <div class="mt-1 text-xs text-slate-600">Optional: {{ f.optional ? 'Yes' : 'No' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AcademicQualificationView',
    props: {
        qualificationId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            loading: false,
            error: '',
            row: {},
            admissionFiles: [],
        }
    },
    created() {
        this.load()
    },
    methods: {
        go(path) {
            window.history.pushState({}, '', path)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        goIndex() {
            this.go('/admin/academicQualification')
        },
        goEdit() {
            this.go(`/admin/academicQualification/${this.qualificationId}/edit`)
        },
        yesNo(v) {
            return String(v) === '1' ? 'Yes' : 'No'
        },
        parseAdmissionFiles(raw) {
            if (!raw) return []
            if (Array.isArray(raw)) return raw
            try {
                const parsed = JSON.parse(String(raw))
                return Array.isArray(parsed) ? parsed : []
            } catch {
                return []
            }
        },
        async load() {
            if (!this.qualificationId) return
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/academicQualification/${this.qualificationId}`)
                this.row = res?.data?.academic_qualification || res?.data || {}
                this.admissionFiles = this.parseAdmissionFiles(this.row?.admission_files)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load.'
            } finally {
                this.loading = false
            }
        },
    },
}
</script>
