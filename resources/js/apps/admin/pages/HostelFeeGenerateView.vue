<template>
    <div class="flex flex-col gap-4">
        <div class="border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Hostel Fee Generate Details</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading" @click="goIndex">Back</button>
                    <button type="button" class="rounded-sm bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" :disabled="loading" @click="goEdit">Edit</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div v-if="loading" class="border border-slate-300 bg-white p-5 text-sm text-slate-600">Loading...</div>

        <div v-else class="border border-slate-300 bg-white p-5">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-6">
                    <div class="text-xs font-semibold text-slate-600">Hostel</div>
                    <div class="mt-1 text-sm text-slate-900">{{ row.hostel_name || '—' }}</div>
                </div>
                <div class="lg:col-span-6">
                    <div class="text-xs font-semibold text-slate-600">Financial Year</div>
                    <div class="mt-1 text-sm text-slate-900">{{ row.financial_year_name || '—' }}</div>
                </div>
                <div class="lg:col-span-6">
                    <div class="text-xs font-semibold text-slate-600">Created At</div>
                    <div class="mt-1 text-sm text-slate-900">{{ formatDate(row.created_at) }}</div>
                </div>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full border-collapse border border-slate-300">
                    <thead class="bg-emerald-100">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-700">
                            <th class="border border-slate-300 bg-slate-50 px-2 py-2 text-slate-700">Date</th>
                            <th class="border border-slate-300 bg-slate-50 px-2 py-2 text-slate-700">Purpose</th>
                            <th class="border border-slate-300 bg-slate-50 px-2 py-2 text-slate-700">Establishment</th>
                            <th class="border border-slate-300 bg-slate-50 px-2 py-2 text-slate-700">Sorting</th>
                            <th class="border border-slate-300 bg-slate-50 px-2 py-2 text-slate-700">Active</th>
                            <th class="border border-slate-300 bg-slate-50 px-2 py-2 text-right text-slate-700">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="details.length === 0" class="text-sm text-slate-600">
                            <td class="border border-slate-300 px-4 py-6" colspan="6">No details.</td>
                        </tr>
                        <tr v-for="(d, i) in details" v-else :key="'det-' + i" class="text-sm text-slate-800" :class="i % 2 === 0 ? 'bg-white' : 'bg-slate-50'">
                            <td class="border border-slate-300 px-2 py-2">{{ d.date || '—' }}</td>
                            <td class="border border-slate-300 px-2 py-2">{{ d.purpose_name || '—' }}</td>
                            <td class="border border-slate-300 px-2 py-2">{{ Number(d.is_establishment || 0) === 1 ? 'Yes' : 'No' }}</td>
                            <td class="border border-slate-300 px-2 py-2">{{ d.sorting ?? 0 }}</td>
                            <td class="border border-slate-300 px-2 py-2">{{ Number(d.status || 0) === 1 ? 'Yes' : 'No' }}</td>
                            <td class="border border-slate-300 px-2 py-2 text-right">{{ formatMoney(d.amount) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'HostelFeeGenerateView',
    props: {
        generateId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            loading: false,
            error: '',
            row: {},
            details: [],
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
            this.go('/admin/hostelFeeGenerate')
        },
        goEdit() {
            this.go(`/admin/hostelFeeGenerate/${this.generateId}/edit`)
        },
        formatDate(d) {
            if (!d) return '—'
            try {
                return new Date(d).toLocaleString('en-US', { year: 'numeric', month: 'short', day: '2-digit' })
            } catch {
                return String(d)
            }
        },
        formatMoney(amount) {
            const n = Number(amount || 0)
            return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n)
        },
        async load() {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/hostelFeeGenerate/${this.generateId}`)
                this.row = res?.data?.data || {}
                this.details = Array.isArray(res?.data?.details) ? res.data.details : []
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load.'
            } finally {
                this.loading = false
            }
        },
    },
}
</script>
