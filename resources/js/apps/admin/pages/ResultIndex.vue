<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Result</div>
                    <div class="mt-1 text-sm text-slate-600">Result list</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                        @click="goCreate"
                    >
                        Result Entry
                    </button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Session</div>
                    <select
                        v-model="filters.academic_session_id"
                        class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                    >
                        <option value="">All</option>
                        <option v-for="s in sessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Academic Level</div>
                    <select
                        v-model="filters.academic_qualification_id"
                        class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                    >
                        <option value="">All</option>
                        <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Department</div>
                    <select
                        v-model="filters.department_id"
                        class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                    >
                        <option value="">All</option>
                        <option v-for="d in filteredDepartments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Class</div>
                    <select
                        v-model="filters.academic_class_id"
                        class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                    >
                        <option value="">All</option>
                        <option v-for="c in filteredClasses" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-1">
                    <div class="text-xs font-semibold text-slate-600">Per Page</div>
                    <select
                        v-model.number="filters.pagination"
                        class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                    >
                        <option :value="10">10</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                    </select>
                </div>

                <div class="lg:col-span-12 flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="h-9 rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800"
                        :disabled="loading"
                        @click="search(true)"
                    >
                        {{ loading ? '...' : 'Search' }}
                    </button>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Session</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Department</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Academic Level / Class</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Exam</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Published Date</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Status</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="row in rows" :key="'r-' + row.id">
                            <td class="px-3 py-2 text-slate-800">{{ row.academic_session_name || '--' }}</td>
                            <td class="px-3 py-2 text-slate-800">{{ row.department_name || '--' }}</td>
                            <td class="px-3 py-2 text-slate-800">
                                {{ row.academic_qualification_name || '' }}
                                <span v-if="row.academic_class_name" class="text-slate-500">({{ row.academic_class_name }})</span>
                            </td>
                            <td class="px-3 py-2 text-slate-800">{{ row.exam_name || '--' }}</td>
                            <td class="px-3 py-2 text-slate-800">{{ row.published_date || '--' }}</td>
                            <td class="px-3 py-2">
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                                    :class="row.status === 'published' ? 'bg-emerald-50 text-emerald-700' : row.status === 'deactive' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700'"
                                >
                                    {{ row.status || 'draft' }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <div class="flex flex-wrap items-center justify-center gap-2">
                                    <button
                                        type="button"
                                        class="rounded-lg border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                        :disabled="syncingId === row.id"
                                        @click="sync(row.id)"
                                    >
                                        {{ syncingId === row.id ? 'Syncing...' : 'Sync' }}
                                    </button>

                                    <button
                                        type="button"
                                        class="rounded-lg border px-3 py-1 text-xs font-semibold"
                                        :class="row.status === 'published' ? 'border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100' : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
                                        :disabled="publishingId === row.id"
                                        @click="togglePublished(row)"
                                    >
                                        {{ row.status === 'published' ? 'Unpublish' : 'Publish' }}
                                    </button>

                                    <button
                                        type="button"
                                        class="rounded-lg border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                        @click="goView(row.id)"
                                    >
                                        View
                                    </button>

                                    <button
                                        v-if="row.status !== 'published'"
                                        type="button"
                                        class="rounded-lg border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                        @click="goEdit(row.id)"
                                    >
                                        Edit
                                    </button>

                                    <button
                                        v-if="row.status !== 'published'"
                                        type="button"
                                        class="rounded-lg bg-rose-600 px-3 py-1 text-xs font-semibold text-white hover:bg-rose-700"
                                        :disabled="deletingId === row.id"
                                        @click="destroyRow(row)"
                                    >
                                        {{ deletingId === row.id ? 'Deleting...' : 'Delete' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!rows.length">
                            <td colspan="7" class="px-3 py-10 text-center text-slate-500">No data found</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="meta && meta.last_page > 1" class="mt-4 flex flex-wrap items-center justify-between gap-2">
                <div class="text-sm text-slate-600">Showing {{ meta.from || 0 }}-{{ meta.to || 0 }} of {{ meta.total || 0 }}</div>
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm hover:bg-slate-50"
                        :disabled="Number(meta.current_page || 1) <= 1"
                        @click="goPage(Number(meta.current_page || 1) - 1)"
                    >
                        Prev
                    </button>
                    <button
                        v-for="p in pageNumbers"
                        :key="'p-' + p"
                        type="button"
                        class="h-9 rounded-lg border px-3 text-sm"
                        :class="p === Number(meta.current_page || 1) ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
                        @click="goPage(p)"
                    >
                        {{ p }}
                    </button>
                    <button
                        type="button"
                        class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm hover:bg-slate-50"
                        :disabled="Number(meta.current_page || 1) >= Number(meta.last_page || 1)"
                        @click="goPage(Number(meta.current_page || 1) + 1)"
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
    name: 'ResultIndex',
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
            meta: { current_page: 1, last_page: 1, total: 0, from: null, to: null },
            deletingId: null,
            syncingId: null,
            publishingId: null,
            filters: {
                pagination: 10,
                page: 1,
                academic_session_id: '',
                academic_class_id: '',
                department_id: '',
                academic_qualification_id: '',
            },
        }
    },
    computed: {
        sessions() {
            const list = this.systems?.global?.academic_sessions
            return Array.isArray(list) ? list : []
        },
        sessionsSorted() {
            const list = Array.isArray(this.sessions) ? [...this.sessions] : []
            return list.sort((a, b) => this.sessionSortKey(b) - this.sessionSortKey(a))
        },
        qualifications() {
            const list = this.systems?.global?.academic_qualifications
            return Array.isArray(list) ? list : []
        },
        classes() {
            const list = this.systems?.global?.academic_classes
            return Array.isArray(list) ? list : []
        },
        departments() {
            const list = this.systems?.global?.departments
            return Array.isArray(list) ? list : []
        },
        departmentQualifications() {
            const list = this.systems?.global?.department_qualidactions || this.systems?.global?.department_qualifications
            return Array.isArray(list) ? list : []
        },
        filteredDepartments() {
            const qid = this.filters?.academic_qualification_id
            if (!qid) return this.departments
            const allowed = new Set(
                this.departmentQualifications
                    .filter((r) => String(r.academic_qualification_id) === String(qid))
                    .map((r) => String(r.department_id)),
            )
            return this.departments.filter((d) => allowed.has(String(d.id)))
        },
        filteredClasses() {
            const qid = this.filters?.academic_qualification_id
            if (!qid) return this.classes
            return this.classes.filter((c) => String(c.academic_qualification_id) === String(qid))
        },
        pageNumbers() {
            const current = Number(this.meta?.current_page || 1)
            const last = Number(this.meta?.last_page || 1)
            const spread = 2
            const start = Math.max(1, current - spread)
            const end = Math.min(last, current + spread)
            const out = []
            for (let i = start; i <= end; i += 1) out.push(i)
            return out
        },
    },
    async mounted() {
        await this.search(true)
    },
    methods: {
        sessionSortKey(s) {
            const name = String(s?.name || '')
            const m = name.match(/(\d{4})\s*[-/]\s*(\d{4})/)
            if (m) {
                const start = Number(m[1] || 0)
                const end = Number(m[2] || 0)
                return start * 10000 + end
            }
            const n = Number(name)
            if (!Number.isNaN(n) && Number.isFinite(n)) return n
            const id = Number(s?.id || 0)
            return Number.isFinite(id) ? id : 0
        },
        goCreate() {
            window.location.href = '/admin/result/create'
        },
        goEdit(id) {
            window.location.href = `/admin/result/${id}/edit`
        },
        goView(id) {
            window.location.href = `/admin/result-report?result_id=${id}`
        },
        goPage(p) {
            this.filters.page = p
            this.search(false)
        },
        async search(resetPage) {
            if (resetPage) this.filters.page = 1
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get('/admin/result/list', { params: this.filters })
                this.rows = Array.isArray(res?.data?.data) ? res.data.data : []
                this.meta = res?.data?.meta || this.meta
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load results.'
                this.rows = []
            } finally {
                this.loading = false
            }
        },
        async sync(id) {
            this.syncingId = id
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/result-sync/${id}`)
                const msg = res?.data?.message || 'Result Sync Successfully!'
                this.$toast?.success?.(msg)
                await this.search(false)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to sync.'
                this.$toast?.error?.(this.error)
            } finally {
                this.syncingId = null
            }
        },
        async togglePublished(row) {
            const pubDate = window.prompt('Published date (YYYY-MM-DD)', row?.published_date || new Date().toISOString().slice(0, 10))
            if (pubDate === null) return

            this.publishingId = row.id
            this.error = ''
            try {
                const res = await window.axios.post('/admin/result-published', { result_id: row.id, published_date: pubDate })
                const msg = res?.data?.message || 'Published Successfully!'
                this.$toast?.success?.(msg)
                await this.search(false)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to publish.'
                this.$toast?.error?.(this.error)
            } finally {
                this.publishingId = null
            }
        },
        async destroyRow(row) {
            const ok = window.confirm('Are you sure you want to delete?')
            if (!ok) return

            this.deletingId = row.id
            this.error = ''
            try {
                const res = await window.axios.delete(`/admin/result/${row.id}`)
                const msg = res?.data?.message || 'Delete Successfully!'
                this.$toast?.success?.(msg)
                await this.search(false)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to delete.'
                this.$toast?.error?.(this.error)
            } finally {
                this.deletingId = null
            }
        },
    },
}
</script>
