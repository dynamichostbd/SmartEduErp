<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Approved Student</div>
                    <div class="mt-1 text-sm text-slate-600">Manage approved student batches</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <router-link
                        :to="{ name: 'approveStudent.create' }"
                        class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                    >
                        <i class="fas fa-plus mr-2"></i>New Batch
                    </router-link>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div class=" border border-slate-300 bg-white p-5">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3 xl:grid-cols-4">
                <div class="xl:col-span-1">
                    <div class="text-xs font-semibold text-slate-600">Search</div>
                    <div class="mt-1 flex gap-2">
                        <select v-model="filters.field_name" class="h-9 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm">
                            <option value="search_keyword">Search All</option>
                        </select>
                        <input
                            v-model="filters.value"
                            type="text"
                            class="h-9 flex-1 rounded-sm border border-slate-300 bg-white px-3 text-sm"
                            placeholder="Search..."
                        />
                    </div>
                </div>

                <div class="xl:col-span-1">
                    <div class="text-xs font-semibold text-slate-600">Per Page</div>
                    <select v-model.number="filters.pagination" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                        <option :value="10">10</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                    </select>
                </div>

                <div class="xl:col-span-2 flex items-end justify-end">
                    <button
                        type="button"
                        class="h-9 rounded-sm bg-slate-900 px-5 text-sm font-semibold text-white hover:bg-slate-800"
                        :disabled="loading"
                        @click="search(true)"
                    >
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
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">#</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Exam</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Session</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Class</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Academic Level</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Department</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">Status</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Created</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <tr v-if="loading" class="text-sm text-slate-600">
                            <td colspan="9" class="border border-slate-300 px-1 py-1 text-center">Loading...</td>
                        </tr>
                        <tr v-else-if="rows.length === 0" class="text-sm text-slate-600">
                            <td colspan="9" class="border border-slate-300 px-1 py-1 text-center">No data found</td>
                        </tr>
                        <tr v-for="(row, idx) in rows" v-else :key="'r-' + row.id" :class="idx % 2 === 0 ? 'bg-white' : 'bg-slate-50'">
                            <td class="border border-slate-300 px-1 py-1 text-slate-600">{{ rowSerial(idx) }}</td>
                            <td class="border border-slate-300 px-1 py-1 font-medium text-slate-900">
                                <a href="#" class="text-emerald-700 hover:underline" @click.prevent="goView(row.id)">
                                    {{ row.exam?.name || '—' }}
                                </a>
                            </td>
                            <td class="border border-slate-300 px-1 py-1">{{ row.academic_session?.name || '—' }}</td>
                            <td class="border border-slate-300 px-1 py-1">{{ row.academic_class?.name || '—' }}</td>
                            <td class="border border-slate-300 px-1 py-1">{{ row.qualification?.name || '—' }}</td>
                            <td class="border border-slate-300 px-1 py-1">{{ row.department?.name || '—' }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-center">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="statusBadgeClass(row.status)"
                                >
                                    {{ row.status || '—' }}
                                </span>
                            </td>
                            <td class="border border-slate-300 px-1 py-1 text-slate-600">{{ formatDate(row.created_at) }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-slate-300 bg-white text-slate-700 hover:bg-slate-50"
                                        title="View"
                                        @click="goView(row.id)"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-slate-300 bg-white text-slate-700 hover:bg-slate-50"
                                        title="Edit"
                                        @click="goEdit(row.id)"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-red-200 bg-white text-red-700 hover:bg-red-50"
                                        :disabled="deletingId === row.id"
                                        title="Delete"
                                        @click="destroy(row.id)"
                                    >
                                        <i v-if="deletingId === row.id" class="fas fa-spinner fa-spin"></i>
                                        <i v-else class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="meta.total" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-slate-600">
                    Showing {{ meta.from || 0 }} to {{ meta.to || 0 }} of {{ meta.total || 0 }} entries
                </div>

                <div class="flex flex-wrap items-center gap-1">
                    <button
                        type="button"
                        class="h-9 rounded-sm border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        :disabled="filters.page <= 1 || loading"
                        @click="goPage(filters.page - 1)"
                    >
                        Prev
                    </button>

                    <button
                        v-for="p in pageNumbers"
                        :key="'p-' + p"
                        type="button"
                        class="h-9 rounded-sm border px-3 text-sm font-semibold"
                        :class="p === filters.page ? 'border-emerald-300 bg-emerald-50 text-emerald-700' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'"
                        :disabled="loading"
                        @click="goPage(p)"
                    >
                        {{ p }}
                    </button>

                    <button
                        type="button"
                        class="h-9 rounded-sm border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        :disabled="filters.page >= meta.last_page || loading"
                        @click="goPage(filters.page + 1)"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ApproveStudentIndex',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            loading: false,
            deletingId: null,
            error: '',
            rows: [],
            searchTimer: null,
            meta: {
                current_page: 1,
                from: 0,
                to: 0,
                per_page: 10,
                total: 0,
                last_page: 1,
            },
            filters: {
                field_name: 'search_keyword',
                value: '',
                pagination: 10,
                page: 1,
            },
        }
    },
    watch: {
        'filters.field_name'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch(true)
        },
        'filters.value'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch(true)
        },
        'filters.pagination'(next, prev) {
            if (Number(next || 0) !== Number(prev || 0)) this.scheduleSearch(true)
        },
    },
    computed: {
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
    methods: {
        scheduleSearch(resetPage) {
            if (this.searchTimer) clearTimeout(this.searchTimer)
            this.searchTimer = setTimeout(() => {
                this.search(resetPage)
            }, 250)
        },
        rowSerial(idx) {
            const from = Number(this.meta.from || 0)
            return from + idx
        },
        go(path) {
            window.history.pushState({}, '', path)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        goView(id) {
            this.go(`/admin/approveStudent/${id}`)
        },
        goEdit(id) {
            this.go(`/admin/approveStudent/${id}/edit`)
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
                const res = await window.axios.get('/admin/approveStudent', { params: this.filters })
                this.rows = Array.isArray(res?.data?.data) ? res.data.data : []
                this.meta = res?.data?.meta || this.meta
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load data.'
                this.rows = []
            } finally {
                this.loading = false
            }
        },
        async destroy(id) {
            const ok = window.confirm('Delete this approved student batch?')
            if (!ok) return

            this.deletingId = id
            this.error = ''

            try {
                await window.axios.delete(`/admin/approveStudent/${id}`)
                await this.search(false)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to delete.'
            } finally {
                this.deletingId = null
            }
        },
        statusBadgeClass(status) {
            const s = String(status || '').toLowerCase()
            if (s === 'active') return 'border border-emerald-200 bg-emerald-50 text-emerald-700'
            if (s === 'inactive') return 'border border-red-200 bg-red-50 text-red-700'
            return 'border border-slate-300 bg-slate-50 text-slate-700'
        },
        formatDate(dateStr) {
            if (!dateStr) return '—'
            try {
                return new Date(dateStr).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                })
            } catch {
                return dateStr
            }
        },
    },
}
</script>
