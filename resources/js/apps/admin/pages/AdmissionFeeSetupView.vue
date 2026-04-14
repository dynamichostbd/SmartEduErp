<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Admission Fee Setup</div>
                    <div class="mt-1 text-sm text-slate-600">View</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goBack">Back</button>
                    <button type="button" class="rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="!feeSetupId" @click="goEdit">Edit</button>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class=" border border-slate-300 bg-white p-5">
            <div v-if="loading" class="py-10 text-center text-sm text-slate-600">Loading...</div>

            <div v-else-if="!row" class="rounded-xl border border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-600">No data Found</div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr>
                            <td class="w-64 px-3 py-2 font-semibold text-slate-700">Purpose</td>
                            <td class="px-3 py-2">{{ row?.head?.name || '' }}</td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2 font-semibold text-slate-700">Amount</td>
                            <td class="px-3 py-2">{{ row?.amount }}</td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2 font-semibold text-slate-700">Store ID</td>
                            <td class="px-3 py-2">{{ row?.store_id }}</td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2 font-semibold text-slate-700">Store Password</td>
                            <td class="px-3 py-2">{{ row?.store_password }}</td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2 font-semibold text-slate-700">Account No</td>
                            <td class="px-3 py-2">{{ row?.account_no }}</td>
                        </tr>
                        <tr v-if="row.enable_date != null">
                            <td class="px-3 py-2 font-semibold text-slate-700">Timeline</td>
                            <td class="px-3 py-2">{{ Number(row.enable_date) ? 'Enabled' : 'Disabled' }}</td>
                        </tr>
                        <tr v-if="row.start_date">
                            <td class="px-3 py-2 font-semibold text-slate-700">Start Date</td>
                            <td class="px-3 py-2">{{ row.start_date }}</td>
                        </tr>
                        <tr v-if="row.expire_date">
                            <td class="px-3 py-2 font-semibold text-slate-700">Expire Date</td>
                            <td class="px-3 py-2">{{ row.expire_date }}</td>
                        </tr>
                        <tr v-if="row.additional_date">
                            <td class="px-3 py-2 font-semibold text-slate-700">Additional Date</td>
                            <td class="px-3 py-2">{{ row.additional_date }}</td>
                        </tr>
                        <tr v-if="row.enable_roll != null">
                            <td class="px-3 py-2 font-semibold text-slate-700">Roll Validation</td>
                            <td class="px-3 py-2">{{ Number(row.enable_roll) ? 'Enabled' : 'Disabled' }}</td>
                        </tr>
                        <tr v-if="row.admission_roll_min_value != null">
                            <td class="px-3 py-2 font-semibold text-slate-700">Admission Roll Min</td>
                            <td class="px-3 py-2">{{ row.admission_roll_min_value }}</td>
                        </tr>
                        <tr v-if="row.admission_roll_max_value != null">
                            <td class="px-3 py-2 font-semibold text-slate-700">Admission Roll Max</td>
                            <td class="px-3 py-2">{{ row.admission_roll_max_value }}</td>
                        </tr>
                        <tr v-if="row.session != null">
                            <td class="px-3 py-2 font-semibold text-slate-700">Session Applicable</td>
                            <td class="px-3 py-2">{{ Number(row.session) ? 'Yes' : 'No' }}</td>
                        </tr>
                        <tr v-if="Array.isArray(row.academic_session_ids) && row.academic_session_ids.length">
                            <td class="px-3 py-2 font-semibold text-slate-700">Sessions</td>
                            <td class="px-3 py-2">{{ row.academic_session_ids.join(', ') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AdmissionFeeSetupView',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        feeSetupId: {
            type: [String, Number],
            default: null,
        },
    },
    data() {
        return {
            loading: false,
            error: '',
            row: null,
        }
    },
    created() {
        this.load()
    },
    watch: {
        feeSetupId() {
            this.load()
        },
    },
    methods: {
        goBack() {
            window.history.back()
        },
        goEdit() {
            if (!this.feeSetupId) return
            window.history.pushState({}, '', `/admin/admissionFeeSetup/${this.feeSetupId}/edit`)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        async load() {
            this.error = ''
            this.row = null
            if (!this.feeSetupId) return

            this.loading = true
            try {
                const res = await window.axios.get(`/admin/admissionFeeSetup/${this.feeSetupId}`)
                this.row = res?.data || null
            } catch (e) {
                this.row = null
                this.error = e?.response?.data?.message || 'Failed to load fee setup.'
            } finally {
                this.loading = false
            }
        },
    },
}
</script>
