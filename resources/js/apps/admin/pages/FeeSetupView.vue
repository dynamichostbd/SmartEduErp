<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Fee Setup</div>
                    <div class="mt-1 text-sm text-slate-600">View</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading" @click="goIndex">Back</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div v-if="!loading" class="rounded-2xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 p-4 text-sm font-semibold text-slate-900">Academic Information</div>
            <div class="p-5">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="text-xs font-semibold text-slate-500">Department</div>
                        <div class="mt-1 text-sm font-semibold text-slate-900">{{ department?.name || '—' }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="text-xs font-semibold text-slate-500">Academic Level</div>
                        <div class="mt-1 text-sm font-semibold text-slate-900">{{ qualification?.name || '—' }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="text-xs font-semibold text-slate-500">Class</div>
                        <div class="mt-1 text-sm font-semibold text-slate-900">{{ academicClass?.name || '—' }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="text-xs font-semibold text-slate-500">Remarks</div>
                        <div class="mt-1 whitespace-pre-wrap text-sm font-semibold text-slate-900">{{ feeSetup?.description || '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="!loading" class="rounded-2xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 p-4 text-sm font-semibold text-slate-900">Fee Setup Details</div>
            <div class="p-5">
                <div v-if="details.length === 0" class="text-sm text-slate-600">No details found.</div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full border-collapse border border-slate-200">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <th class="border border-slate-200 bg-slate-50 px-4 py-3">Account Head</th>
                                <th class="border border-slate-200 bg-slate-50 px-4 py-3 text-right">Amount</th>
                                <th class="border border-slate-200 bg-slate-50 px-4 py-3">Gateway Account</th>
                                <th class="border border-slate-200 bg-slate-50 px-4 py-3">Exam</th>
                                <th class="border border-slate-200 bg-slate-50 px-4 py-3">Start Date</th>
                                <th class="border border-slate-200 bg-slate-50 px-4 py-3">Expired Date</th>
                                <th class="border border-slate-200 bg-slate-50 px-4 py-3">Additional Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(d, idx) in details" :key="'d-' + idx" class="text-sm text-slate-800" :class="idx % 2 === 0 ? 'bg-white' : 'bg-slate-50'">
                                <td class="border border-slate-200 px-4 py-3">{{ d.account_head_name || '—' }}</td>
                                <td class="border border-slate-200 px-4 py-3 text-right">{{ money(d.amount) }}</td>
                                <td class="border border-slate-200 px-4 py-3">{{ d.gateway_title || '—' }}</td>
                                <td class="border border-slate-200 px-4 py-3">
                                    <div v-if="d.exam_name">
                                        <div>{{ d.exam_name }}</div>
                                        <div class="text-xs text-slate-500">{{ d.examination_year || '' }}</div>
                                    </div>
                                    <div v-else>—</div>
                                </td>
                                <td class="border border-slate-200 px-4 py-3">{{ d.start_date || '—' }}</td>
                                <td class="border border-slate-200 px-4 py-3">{{ d.expire_date || '—' }}</td>
                                <td class="border border-slate-200 px-4 py-3">{{ d.additional_date || '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-if="loading" class="rounded-2xl border border-slate-200 bg-white p-5 text-sm text-slate-600">Loading...</div>
    </div>
</template>

<script>
export default {
    name: 'FeeSetupView',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        feeSetupId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            loading: false,
            error: '',
            feeSetup: null,
            department: null,
            qualification: null,
            academicClass: null,
            details: [],
        }
    },
    async created() {
        await this.load()
    },
    methods: {
        money(v) {
            const n = Number(v || 0)
            return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        },
        go(path) {
            window.history.pushState({}, '', path)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        goIndex() {
            this.go('/admin/feeSetup')
        },
        async load() {
            if (!this.feeSetupId) return
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/feeSetup/${this.feeSetupId}`)
                this.feeSetup = res?.data?.fee_setup || null
                this.department = res?.data?.department || null
                this.qualification = res?.data?.qualification || null
                this.academicClass = res?.data?.academic_class || null
                this.details = Array.isArray(res?.data?.details) ? res.data.details : []
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load fee setup.'
            } finally {
                this.loading = false
            }
        },
    },
}
</script>
