<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Certificate Template</div>
                    <div class="mt-1 text-sm text-slate-600">Manage certificate templates and design sections</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" @click="goCreate">Add New</button>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class=" border border-slate-300 bg-white p-5">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
                <div class="xl:col-span-1">
                    <div class="text-xs font-semibold text-slate-600">Status</div>
                    <select v-model="filters.status" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="(label, key) in statuses" :key="'st-' + key" :value="key">{{ label }}</option>
                    </select>
                </div>

                <div class="xl:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Title</div>
                    <input v-model="filters.value" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="Search by title" />
                </div>

                <div class="xl:col-span-1">
                    <div class="text-xs font-semibold text-slate-600">Per Page</div>
                    <select v-model.number="filters.pagination" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option :value="10">10</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                    </select>
                </div>

                <div class="xl:col-span-1 flex items-end">
                    <button type="button" class="h-9 w-full rounded-sm bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800" :disabled="loading" @click="search(true)">
                        {{ loading ? 'Loading...' : 'Search' }}
                    </button>
                </div>
            </div>
        </div>

        <div class=" border border-slate-300 bg-white p-5">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-slate-300 text-sm">
                    <thead class="bg-emerald-100">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-700">
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Academic Level</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Account Head</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Title</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Certificate Fees</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Amount</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Gateway</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-right text-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <tr v-for="(row, idx) in rows" :key="'t-' + row.id" :class="idx % 2 === 0 ? 'bg-white' : 'bg-slate-50'">
                            <td class="border border-slate-300 px-1 py-1 text-slate-800">{{ row.academic_qualification_name || '--' }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-slate-800">{{ row.account_head_name || '--' }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-slate-800">{{ row.title || '--' }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-slate-800 uppercase">{{ row.certificate_fees || '--' }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-slate-800">{{ row.amount ?? '--' }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-slate-800">{{ row.gateway_account_no || '--' }}</td>
                            <td class="border border-slate-300 px-1 py-1">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-slate-300 bg-white text-slate-700 hover:bg-slate-50" title="View" @click="goView(row.id)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100" title="Edit" @click="goEdit(row.id)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-red-200 bg-red-50 text-red-700 hover:bg-red-100" title="Delete" :disabled="deletingId === row.id" @click="destroyRow(row.id)">
                                        <i v-if="deletingId === row.id" class="fas fa-spinner fa-spin"></i>
                                        <i v-else class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="!rows.length">
                            <td colspan="7" class="px-3 py-10 text-center text-sm text-slate-500">{{ loading ? 'Loading...' : 'No data found' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="meta.total" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-slate-600">Showing {{ meta.from || 0 }} to {{ meta.to || 0 }} of {{ meta.total || 0 }} entries</div>
                <div class="flex flex-wrap items-center gap-1">
                    <button type="button" class="h-9 rounded-sm border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="filters.page <= 1 || loading" @click="goPage(filters.page - 1)">Prev</button>
                    <button v-for="p in pageNumbers" :key="'p-' + p" type="button" class="h-9 rounded-sm border px-3 text-sm font-semibold" :class="p === filters.page ? 'border-emerald-300 bg-emerald-50 text-emerald-700' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'" :disabled="loading" @click="goPage(p)">{{ p }}</button>
                    <button type="button" class="h-9 rounded-sm border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="filters.page >= meta.last_page || loading" @click="goPage(filters.page + 1)">Next</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'CertificateTemplateIndex',
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
            deletingId: null,
            searchTimer: null,
            rows: [],
            meta: {
                current_page: 1,
                from: 0,
                to: 0,
                per_page: 10,
                total: 0,
                last_page: 1,
            },
            filters: {
                pagination: 10,
                field_name: 'title',
                value: '',
                status: 'active',
                page: 1,
            },
        }
    },
    computed: {
        statuses() {
            return this.systems?.global?.status || { draft: 'Draft', active: 'Active', deactive: 'Deactive' }
        },
        pageNumbers() {
            const cur = Number(this.meta.current_page || 1)
            const last = Number(this.meta.last_page || 1)
            const start = Math.max(1, cur - 2)
            const end = Math.min(last, cur + 2)
            const out = []
            for (let i = start; i <= end; i++) out.push(i)
            return out
        },
    },
    mounted() {
        this.search(true)
    },
    watch: {
        'filters.status'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch(true)
        },
        'filters.value'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch(true)
        },
        'filters.pagination'(next, prev) {
            if (Number(next || 0) !== Number(prev || 0)) this.scheduleSearch(true)
        },
    },
    methods: {
        scheduleSearch(resetPage) {
            if (this.searchTimer) clearTimeout(this.searchTimer)
            this.searchTimer = setTimeout(() => {
                this.search(resetPage)
            }, 250)
        },
        goCreate() {
            window.location.href = '/admin/certificateTemplate/create'
        },
        goView(id) {
            window.location.href = `/admin/certificateTemplate/${id}`
        },
        goEdit(id) {
            window.location.href = `/admin/certificateTemplate/${id}/edit`
        },
        goPage(p) {
            const page = Math.max(1, Number(p || 1))
            this.filters.page = page
            this.search(false)
        },
        async search(resetPage) {
            if (resetPage) this.filters.page = 1
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get('/admin/certificateTemplate/list', { params: this.filters })
                this.rows = Array.isArray(res?.data?.data) ? res.data.data : []
                this.meta = res?.data?.meta || this.meta
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load data.'
                this.rows = []
            } finally {
                this.loading = false
            }
        },
        async destroyRow(id) {
            const ok = window.confirm('Are you sure ?')
            if (!ok) return

            this.deletingId = id
            this.error = ''
            try {
                await window.axios.delete(`/admin/certificateTemplate/${id}`)
                this.search(true)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to delete.'
            } finally {
                this.deletingId = null
            }
        },
    },
}
</script>
