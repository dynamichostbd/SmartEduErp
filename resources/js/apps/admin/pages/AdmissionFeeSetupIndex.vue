<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Admission Fee Setup</div>
                    <div class="mt-1 text-sm text-slate-600">Index</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goDashboard">Dashboard</button>
                    <button type="button" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" @click="goCreate">Create</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="grid grid-cols-1 gap-3 lg:grid-cols-12">
                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Search Field</div>
                    <select v-model="filters.field_name" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="store_id">Store ID</option>
                    </select>
                </div>
                <div class="lg:col-span-4">
                    <div class="text-xs font-semibold text-slate-600">Search</div>
                    <input v-model="filters.value" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" @keydown.enter.prevent="load(1)" />
                </div>
                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Per Page</div>
                    <select v-model="filters.pagination" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option :value="10">10</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                    </select>
                </div>
                <div class="lg:col-span-1 flex items-end">
                    <button type="button" class="h-10 w-full rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="loading" @click="load(1)">{{ loading ? '...' : 'Go' }}</button>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Purpose</th>
                            <th class="px-3 py-2 text-right font-semibold text-slate-700">Amount</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Store ID</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Store Password</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Account No</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="row in rows" :key="'r-' + row.id">
                            <td class="px-3 py-2">{{ row.head ? row.head.name : '' }}</td>
                            <td class="px-3 py-2 text-right font-semibold">{{ money(row.amount) }}</td>
                            <td class="px-3 py-2">{{ row.store_id }}</td>
                            <td class="px-3 py-2">{{ row.store_password }}</td>
                            <td class="px-3 py-2">{{ row.account_no }}</td>
                            <td class="px-3 py-2 text-center">
                                <button type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50" @click="goEdit(row.id)">Edit</button>
                                <button type="button" class="ml-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100" @click="destroy(row.id)">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!rows.length">
                            <td colspan="6" class="px-3 py-10 text-center text-slate-500">No Data Found</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="meta.total > 0" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-slate-600">Showing {{ meta.from }} to {{ meta.to }} of {{ meta.total }} entries</div>

                <div class="flex items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="meta.current_page <= 1" @click="load(meta.current_page - 1)">Prev</button>
                    <div class="text-sm font-semibold text-slate-700">{{ meta.current_page }} / {{ meta.last_page }}</div>
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="meta.current_page >= meta.last_page" @click="load(meta.current_page + 1)">Next</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AdmissionFeeSetupIndex',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            loading: false,
            error: '',
            rows: [],
            meta: { from: 0, to: 0, total: 0, current_page: 1, last_page: 1 },
            filters: {
                pagination: 10,
                field_name: 'store_id',
                value: '',
            },
        }
    },
    created() {
        this.load(1)
    },
    methods: {
        money(v) {
            const n = Number(v || 0)
            const val = Number.isFinite(n) ? n : 0
            return val.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        },
        goDashboard() {
            window.history.pushState({}, '', '/admin/dashboard')
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        goCreate() {
            window.history.pushState({}, '', '/admin/admissionFeeSetup/create')
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        goEdit(id) {
            if (!id) return
            window.history.pushState({}, '', `/admin/admissionFeeSetup/${id}/edit`)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        async destroy(id) {
            if (!id) return
            if (!confirm('Are you sure want to delete?')) return
            try {
                await window.axios.delete(`/admin/admissionFeeSetup/${id}`)
                this.load(1)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Delete failed.'
            }
        },
        async load(page) {
            this.loading = true
            this.error = ''
            try {
                const params = { ...this.filters, page: page || 1 }
                const res = await window.axios.get('/admin/admissionFeeSetup', { params })
                const data = res?.data || null
                this.rows = Array.isArray(data?.data) ? data.data : []
                this.meta = {
                    from: data?.from || 0,
                    to: data?.to || 0,
                    total: data?.total || 0,
                    current_page: data?.current_page || 1,
                    last_page: data?.last_page || 1,
                }
            } catch (e) {
                this.rows = []
                this.meta = { from: 0, to: 0, total: 0, current_page: 1, last_page: 1 }
                this.error = e?.response?.data?.message || 'Failed to load admission fee setups.'
            } finally {
                this.loading = false
            }
        },
    },
}
</script>
