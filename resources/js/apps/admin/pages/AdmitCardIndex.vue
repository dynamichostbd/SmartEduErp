<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Admit Card</div>
                    <div class="mt-1 text-sm text-slate-600">Admit card list</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" @click="goCreate">
                        Add Admit Card
                    </button>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div class=" border border-slate-300 bg-white p-5">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Session</div>
                    <select v-model="filters.academic_session_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="s in sessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Academic Level</div>
                    <select v-model="filters.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Department</div>
                    <select v-model="filters.department_id" :disabled="!filters.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="d in filteredDepartments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Class</div>
                    <select v-model="filters.academic_class_id" :disabled="!filters.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="c in filteredClasses" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Search</div>
                    <input v-model="filters.value" type="text" placeholder="Name" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-1">
                    <div class="text-xs font-semibold text-slate-600">Per Page</div>
                    <select v-model.number="filters.pagination" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option :value="10">10</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                    </select>
                </div>

                <div class="lg:col-span-1 flex items-end">
                    <button type="button" class="h-9 w-full rounded-sm bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800" :disabled="loading" @click="search(true)">
                        {{ loading ? '...' : 'Go' }}
                    </button>
                </div>
            </div>
        </div>

        <div class=" border border-slate-300 bg-white">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-slate-300 text-sm">
                    <thead class="bg-emerald-100">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-700">
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Session</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Department</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Academic Level / Class</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Exam</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Admit Card Name</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Issue</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Expired</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <tr v-for="(row, idx) in rows" :key="'ac-' + row.id" :class="idx % 2 === 0 ? 'bg-white' : 'bg-slate-50'">
                            <td class="border border-slate-300 px-1 py-1 text-slate-800">{{ row.academic_session_name || '--' }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-slate-800">{{ row.department_name || '--' }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-slate-800">{{ (row.academic_qualification_name || '') + (row.academic_class_name ? ' (' + row.academic_class_name + ')' : '') }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-slate-800">{{ row.exam_name || '--' }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-slate-800">{{ row.name || '--' }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-slate-800">{{ row.issue_date || '--' }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-slate-800">{{ row.expired_date || '--' }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-slate-300 bg-white text-slate-700 hover:bg-slate-50" title="View" @click="goView(row.id)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-slate-300 bg-white text-slate-700 hover:bg-slate-50" title="Edit" @click="goEdit(row.id)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-red-200 bg-white text-red-700 hover:bg-red-50" title="Delete" :disabled="deletingId === row.id" @click="destroyRow(row.id)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!rows.length">
                            <td colspan="8" class="px-1 py-10 text-center text-slate-500">No data found</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="meta && meta.last_page > 1" class="mt-4 flex flex-wrap items-center justify-between gap-2">
                <div class="text-sm text-slate-600">Showing {{ meta.from || 0 }}-{{ meta.to || 0 }} of {{ meta.total || 0 }}</div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="h-9 rounded-sm border border-slate-300 bg-white px-3 text-sm hover:bg-slate-50" :disabled="Number(meta.current_page || 1) <= 1" @click="goPage(Number(meta.current_page || 1) - 1)">
                        Prev
                    </button>
                    <button v-for="p in pageNumbers" :key="'p-' + p" type="button" class="h-9 rounded-sm border px-3 text-sm" :class="p === Number(meta.current_page || 1) ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'" @click="goPage(p)">
                        {{ p }}
                    </button>
                    <button type="button" class="h-9 rounded-sm border border-slate-300 bg-white px-3 text-sm hover:bg-slate-50" :disabled="Number(meta.current_page || 1) >= Number(meta.last_page || 1)" @click="goPage(Number(meta.current_page || 1) + 1)">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AdmitCardIndex',
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
            rows: [],
            meta: { current_page: 1, last_page: 1, total: 0, from: null, to: null },
            searchTimer: null,
            filters: {
                pagination: 10,
                page: 1,
                field_name: 'name',
                value: '',
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
        departmentQualidactions() {
            const list = this.systems?.global?.department_qualidactions || this.systems?.global?.department_qualifications
            return Array.isArray(list) ? list : []
        },
        filteredDepartments() {
            const qid = this.filters?.academic_qualification_id
            if (!qid) return []
            const allowed = new Set(this.departmentQualidactions.filter((r) => String(r.academic_qualification_id) === String(qid)).map((r) => String(r.department_id)))
            return this.departments.filter((d) => allowed.has(String(d.id)))
        },
        filteredClasses() {
            const qid = this.filters?.academic_qualification_id
            if (!qid) return []
            return this.classes.filter((c) => String(c.academic_qualification_id) === String(qid))
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
    watch: {
        'filters.academic_qualification_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.filters.department_id = ''
                this.filters.academic_class_id = ''
                this.scheduleSearch(true)
            }
        },
        'filters.academic_session_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch(true)
        },
        'filters.department_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch(true)
        },
        'filters.academic_class_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch(true)
        },
        'filters.value'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch(true)
        },
        'filters.pagination'(next, prev) {
            if (Number(next || 0) !== Number(prev || 0)) this.scheduleSearch(true)
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
            window.location.href = '/admin/admitCard/create'
        },
        goView(id) {
            window.location.href = `/admin/admitCard/${id}`
        },
        goEdit(id) {
            window.location.href = `/admin/admitCard/${id}/edit`
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
                const res = await window.axios.get('/admin/admitCard/list', { params: this.filters })
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
            const ok = window.confirm('Are you sure you want to delete?')
            if (!ok) return

            this.deletingId = id
            this.error = ''
            try {
                await window.axios.delete(`/admin/admitCard/${id}`)
                await this.search(false)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to delete.'
            } finally {
                this.deletingId = null
            }
        },
    },
}
</script>
