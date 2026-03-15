<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Hostel Details</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading" @click="goIndex">Back</button>
                    <button type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" :disabled="loading || !hostelId" @click="goEdit">Edit</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div v-if="loading" class="text-sm text-slate-600">Loading...</div>
            <div v-else class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200 p-4 sm:col-span-2">
                    <div class="text-xs font-semibold text-slate-500">Hostel Name</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.name || '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs font-semibold text-slate-500">Fees</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.fees ?? '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs font-semibold text-slate-500">Status</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.status || '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4 sm:col-span-2">
                    <div class="text-xs font-semibold text-slate-500">Description</div>
                    <div class="mt-1 whitespace-pre-wrap text-sm font-semibold text-slate-900">{{ row.description || '—' }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'HostelView',
    props: {
        hostelId: {
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
    async created() {
        await this.load()
    },
    methods: {
        go(path) {
            window.history.pushState({}, '', path)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        goIndex() {
            this.go('/admin/hostel')
        },
        goEdit() {
            this.go(`/admin/hostel/${this.hostelId}/edit`)
        },
        async load() {
            if (!this.hostelId) return
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/hostel/${this.hostelId}`)
                this.row = res?.data?.hostel || res?.data || {}
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load hostel.'
            } finally {
                this.loading = false
            }
        },
    },
}
</script>
