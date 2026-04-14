<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="text-xl font-semibold text-slate-900">Notice List</div>
                </div>

                <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                    <select v-model="filters.type" class="h-9 rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" :disabled="loading">
                        <option value="">All</option>
                        <option value="public">Public</option>
                        <option value="office">Office</option>
                        <option value="student">Student</option>
                    </select>

                    <select v-model="filters.field_name" class="h-9 rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" :disabled="loading">
                        <option value="title">Title</option>
                    </select>

                    <input v-model="filters.value" type="text" class="h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 sm:w-72" placeholder="Search..." :disabled="loading" @keyup.enter="load(1)" />

                    <div class="flex items-center gap-2">
                        <button class="inline-flex h-9 w-10 items-center justify-center rounded-sm bg-emerald-600 text-white hover:bg-emerald-700" :disabled="loading" @click="load(1)">
                            <span class="sr-only">Search</span>
                            <i v-if="loading" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-search"></i>
                        </button>

                        <button v-if="canCreate" class="h-9 rounded-sm bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800" :disabled="loading" @click="goCreate">
                            <i class="fas fa-plus mr-2"></i>
                            Add Notice
                        </button>

                        <button class="h-9 rounded-sm border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading" @click="reset">
                            <i class="fas fa-redo mr-2"></i>
                            Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class="overflow-hidden  border border-slate-300 bg-white">
            <div class="overflow-x-auto">
                <table class="min-w-[700px] w-full border-collapse border border-slate-300">
                    <thead class="bg-emerald-100">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-700">
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">#</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Date</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Type</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Title</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">File</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading" class="text-sm text-slate-600">
                            <td class="border border-slate-300 px-1 py-1" colspan="6">Loading...</td>
                        </tr>
                        <tr v-else-if="rows.length === 0" class="text-sm text-slate-600">
                            <td class="border border-slate-300 px-1 py-1" colspan="6">No data found.</td>
                        </tr>
                        <tr v-for="(row, idx) in rows" v-else :key="row.id" class="text-sm text-slate-800" :class="idx % 2 === 0 ? 'bg-white' : 'bg-slate-50'">
                            <td class="border border-slate-300 px-1 py-1 text-slate-600">{{ rowSerial(idx) }}</td>
                            <td class="border border-slate-300 px-1 py-1">{{ row.notice_date || '' }}</td>
                            <td class="border border-slate-300 px-1 py-1">
                                <span class="inline-flex rounded-full px-1 py-0.5 text-xs font-semibold" :class="row.notice_type === 'general' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700'">{{ row.notice_type || '' }}</span>
                            </td>
                            <td class="border border-slate-300 px-1 py-1">
                                <div class="font-medium text-slate-900">{{ row.title || '' }}</div>
                            </td>
                            <td class="border border-slate-300 px-1 py-1 text-center">
                                <a v-if="row.file" :href="row.file" target="_blank" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-slate-300 bg-white text-slate-700 hover:bg-slate-50" title="Download">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                                        <polyline points="7 10 12 15 17 10" />
                                        <line x1="12" y1="15" x2="12" y2="3" />
                                    </svg>
                                </a>
                                <span v-else class="text-slate-400">—</span>
                            </td>
                            <td class="border border-slate-300 px-1 py-1 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-slate-300 bg-white text-slate-700 hover:bg-slate-50" title="View" @click="goView(row.id)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-slate-300 bg-white text-slate-700 hover:bg-slate-50" title="Edit" :disabled="!canEdit" @click="goEdit(row.id)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-red-200 bg-white text-red-700 hover:bg-red-50" title="Delete" :disabled="!canDelete || deletingId === row.id" @click="destroy(row.id)">
                                        <i v-if="deletingId === row.id" class="fas fa-spinner fa-spin"></i>
                                        <i v-else class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-300 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-xs text-slate-600">Showing {{ meta.from || 0 }} to {{ meta.to || 0 }} of {{ meta.total || 0 }} entries</div>

                <div class="flex items-center justify-end gap-1 overflow-x-auto">
                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-slate-300 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading || meta.current_page <= 1" @click="load(meta.current_page - 1)">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-slate-300 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading || meta.current_page >= meta.last_page" @click="load(meta.current_page + 1)">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'NoticeIndex',
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
            filters: {
                pagination: 10,
                field_name: 'title',
                value: '',
                type: '',
            },
            meta: {
                current_page: 1,
                last_page: 1,
                from: 0,
                to: 0,
                total: 0,
                per_page: 10,
            },
        }
    },
    computed: {
        perms() {
            return Array.isArray(this.systems?.permissions) ? this.systems.permissions : []
        },
        canCreate() {
            return !this.perms.length || this.perms.includes('notice.create')
        },
        canEdit() {
            return !this.perms.length || this.perms.includes('notice.edit')
        },
        canDelete() {
            return !this.perms.length || this.perms.includes('notice.destroy')
        },
    },
    created() {
        this.load(1)
    },
    watch: {
        'filters.type'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad(true)
        },
        'filters.field_name'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad(true)
        },
        'filters.value'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad(true)
        },
        'filters.pagination'(next, prev) {
            if (Number(next || 0) !== Number(prev || 0)) this.scheduleLoad(true)
        },
    },
    methods: {
        scheduleLoad(resetPage) {
            if (this.searchTimer) clearTimeout(this.searchTimer)
            this.searchTimer = setTimeout(() => {
                const page = resetPage ? 1 : Number(this.meta?.current_page || 1)
                this.load(page)
            }, 250)
        },
        toast(message, type = 'success') {
            window.dispatchEvent(new CustomEvent('app-toast', { detail: { message, type } }))
        },
        rowSerial(idx) {
            const from = Number(this.meta.from || 0)
            return from + idx
        },
        go(path) {
            window.history.pushState({}, '', path)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        goCreate() {
            this.go('/admin/notice/create')
        },
        goView(id) {
            this.go(`/admin/notice/${id}`)
        },
        goEdit(id) {
            this.go(`/admin/notice/${id}/edit`)
        },
        reset() {
            this.filters.pagination = 10
            this.filters.field_name = 'title'
            this.filters.value = ''
            this.filters.type = ''
            this.load(1)
        },
        resolveFile(v) {
            if (!v) return ''
            if (String(v).match(/^https?:\/\//i)) return v
            const path = String(v)
                .replace(/^storage\//i, '')
                .replace(/^upload\//i, '')
                .replace(/^public\//i, '')
                .replace(/^\//, '')
            return `/storage/upload/${path}`
        },
        async load(page = 1) {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get('/admin/notice', {
                    params: {
                        page,
                        pagination: this.filters.pagination,
                        field_name: this.filters.field_name,
                        value: this.filters.value,
                        type: this.filters.type,
                    },
                })
                const payload = res?.data || {}
                this.rows = Array.isArray(payload?.data) ? payload.data : []
                this.meta = {
                    current_page: payload?.current_page || 1,
                    last_page: payload?.last_page || 1,
                    from: payload?.from ?? 0,
                    to: payload?.to ?? 0,
                    total: payload?.total ?? 0,
                    per_page: payload?.per_page || this.filters.pagination,
                }
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load notices.'
            } finally {
                this.loading = false
            }
        },
        async destroy(id) {
            if (!this.canDelete) return
            if (!confirm('Are you sure?')) return
            this.deletingId = id
            try {
                const res = await window.axios.delete(`/admin/notice/${id}`)
                this.toast(res?.data?.message || 'Delete Successfully!', 'success')
                await this.load(this.meta.current_page || 1)
            } catch (e) {
                this.toast(e?.response?.data?.message || 'Delete failed.', 'error')
            } finally {
                this.deletingId = null
            }
        },
    },
}
</script>
