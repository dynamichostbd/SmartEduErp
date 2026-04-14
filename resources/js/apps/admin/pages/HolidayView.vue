<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Holiday Details</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading" @click="goIndex">Back</button>
                    <button type="button" class="rounded-sm bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" :disabled="loading || !holidayId" @click="goEdit">Edit</button>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class=" border border-slate-300 bg-white p-5">
            <div v-if="loading" class="text-sm text-slate-600">Loading...</div>
            <div v-else class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-300 p-4">
                    <div class="text-xs font-semibold text-slate-500">Holiday Type</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.holiday_type || '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-300 p-4">
                    <div class="text-xs font-semibold text-slate-500">Name</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.name || '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-300 p-4">
                    <div class="text-xs font-semibold text-slate-500">From Date</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.from_date || '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-300 p-4">
                    <div class="text-xs font-semibold text-slate-500">To Date</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.to_date || '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-300 p-4">
                    <div class="text-xs font-semibold text-slate-500">Total Days</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.total_days ?? '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-300 p-4">
                    <div class="text-xs font-semibold text-slate-500">Status</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.status || '—' }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'HolidayView',
    props: {
        holidayId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            loading: false,
            error: '',
            row: {},
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
            this.go('/admin/holiday')
        },
        goEdit() {
            this.go(`/admin/holiday/${this.holidayId}/edit`)
        },
        async load() {
            if (!this.holidayId) return
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/holiday/${this.holidayId}`)
                this.row = res?.data?.holiday || res?.data || {}
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load.'
            } finally {
                this.loading = false
            }
        },
    },
}
</script>
