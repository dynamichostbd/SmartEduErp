<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="text-xl font-semibold text-slate-900">Activity Log</div>
                </div>

                <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                    <input v-model="filters.from_date" type="date" class="h-9 rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" :disabled="loading" />
                    <input v-model="filters.to_date" type="date" class="h-9 rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" :disabled="loading" />

                    <select v-model="filters.description" class="h-9 rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" :disabled="loading">
                        <option value="">All User</option>
                        <option v-for="a in admins" :key="'a-' + a.id" :value="a.name">{{ a.name }}</option>
                    </select>

                    <select v-model="filters.action" class="h-9 rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" :disabled="loading">
                        <option value="">All</option>
                        <option value="Created">Created</option>
                        <option value="Updated">Updated</option>
                        <option value="Deleted">Deleted</option>
                    </select>

                    <select v-model="filters.status" class="h-9 rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" :disabled="loading">
                        <option value="">All</option>
                        <option value="r">Read</option>
                        <option value="ur">Unread</option>
                    </select>

                    <select
                        v-model="filters.field_name"
                        class="h-9 rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                        :disabled="loading"
                    >
                        <option value="log_name">Module</option>
                        <option value="description">Description</option>
                    </select>

                    <input
                        v-model="filters.value"
                        type="text"
                        class="h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 sm:w-64"
                        placeholder="Search..."
                        :disabled="loading"
                        @keyup.enter="load(1)"
                    />

                    <div class="flex items-center gap-2">
                        <button class="h-9 rounded-sm bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800" :disabled="loading" @click="allRead">All Read</button>
                        <button class="h-9 rounded-sm border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading" @click="reset">Reset</button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class="overflow-hidden  border border-slate-300 bg-white">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-slate-300">
                    <thead class="bg-emerald-100">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-700">
                            <th class="border border-slate-300 bg-slate-50 px-4 py-3">#</th>
                            <th class="border border-slate-300 bg-slate-50 px-4 py-3">Module</th>
                            <th class="border border-slate-300 bg-slate-50 px-4 py-3">Description</th>
                            <th class="border border-slate-300 bg-slate-50 px-4 py-3 text-center">Status</th>
                            <th class="border border-slate-300 bg-slate-50 px-4 py-3 text-center">Created at</th>
                            <th class="border border-slate-300 bg-slate-50 px-4 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading" class="text-sm text-slate-600">
                            <td class="border border-slate-300 px-4 py-6" colspan="6">Loading...</td>
                        </tr>
                        <tr v-else-if="rows.length === 0" class="text-sm text-slate-600">
                            <td class="border border-slate-300 px-4 py-6" colspan="6">No data found.</td>
                        </tr>
                        <tr v-for="(row, idx) in rows" v-else :key="row.id" class="text-sm text-slate-800" :class="idx % 2 === 0 ? 'bg-white' : 'bg-slate-50'">
                            <td class="border border-slate-300 px-4 py-3 text-slate-600">{{ rowSerial(idx) }}</td>
                            <td class="border border-slate-300 px-4 py-3">{{ row.log_name || '' }}</td>
                            <td class="border border-slate-300 px-4 py-3">{{ row.description || '' }}</td>
                            <td class="border border-slate-300 px-4 py-3 text-center">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" :class="String(row.status) === 'ur' ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700'">
                                    {{ String(row.status) === 'ur' ? 'Unread' : 'Read' }}
                                </span>
                            </td>
                            <td class="border border-slate-300 px-4 py-3 text-center">{{ row.created_at || '' }}</td>
                            <td class="border border-slate-300 px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-slate-300 bg-white text-slate-700 hover:bg-slate-50"
                                        title="View"
                                        @click="goView(row.id)"
                                    >
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-7 9.5-7 9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-red-200 bg-white text-red-700 hover:bg-red-50"
                                        title="Delete"
                                        :disabled="deletingId === row.id"
                                        @click="destroy(row.id)"
                                    >
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 11v6" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 11v6" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7l1 14h8l1-14" />
                                        </svg>
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
                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-slate-300 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading || meta.current_page <= 1" @click="load(meta.current_page - 1)">«</button>

                    <template v-for="p in paginationPages" :key="'p-' + p">
                        <span v-if="p === '...'" class="inline-flex h-8 min-w-8 items-center justify-center px-2 text-xs font-semibold text-slate-500">...</span>
                        <button
                            v-else
                            type="button"
                            class="inline-flex h-8 min-w-8 items-center justify-center rounded-sm border px-2 text-xs font-semibold"
                            :class="Number(p) === Number(meta.current_page) ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'"
                            :disabled="loading"
                            @click="load(Number(p))"
                        >
                            {{ p }}
                        </button>
                    </template>

                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-slate-300 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading || meta.current_page >= meta.last_page" @click="load(meta.current_page + 1)">»</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ActivityLogIndex',
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
            admins: [],
            searchTimer: null,
            filters: {
                pagination: 10,
                field_name: 'log_name',
                value: '',
                from_date: '',
                to_date: '',
                description: '',
                action: '',
                status: 'ur',
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
        paginationPages() {
            const current = Number(this.meta.current_page || 1)
            const last = Number(this.meta.last_page || 1)
            if (last <= 1) return [1]

            const pages = new Set([1, last, current, current - 1, current + 1, current - 2, current + 2])
            const sorted = Array.from(pages)
                .filter((p) => Number.isFinite(p) && p >= 1 && p <= last)
                .sort((a, b) => a - b)

            const out = []
            let prev = null
            for (const p of sorted) {
                if (prev !== null && p - prev > 1) out.push('...')
                out.push(p)
                prev = p
            }
            return out
        },
    },
    async created() {
        await this.loadAdmins()
        await this.load(1)
    },
    watch: {
        'filters.from_date'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad(true)
        },
        'filters.to_date'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad(true)
        },
        'filters.description'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad(true)
        },
        'filters.action'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad(true)
        },
        'filters.status'(next, prev) {
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
        goView(id) {
            this.go(`/admin/activityLog/${id}`)
        },
        reset() {
            this.filters.pagination = 10
            this.filters.field_name = 'log_name'
            this.filters.value = ''
            this.filters.from_date = ''
            this.filters.to_date = ''
            this.filters.description = ''
            this.filters.action = ''
            this.filters.status = 'ur'
            this.load(1)
        },
        async loadAdmins() {
            try {
                const res = await window.axios.get('/admin/activity-admins')
                this.admins = Array.isArray(res?.data) ? res.data : []
            } catch {
                this.admins = []
            }
        },
        async load(page = 1) {
            this.loading = true
            this.error = ''

            try {
                const res = await window.axios.get('/admin/activityLog', {
                    params: {
                        page,
                        ...this.filters,
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
                this.error = e?.response?.data?.message || 'Failed to load activity logs.'
            } finally {
                this.loading = false
            }
        },
        async allRead() {
            try {
                const res = await window.axios.get('/admin/allRead')
                this.toast(res?.data?.message || 'Read Successfully!', 'success')
                this.load(1)
            } catch (e) {
                this.toast(e?.response?.data?.message || 'Failed', 'error')
            }
        },
        async destroy(id) {
            const ok = window.confirm('Are you sure?')
            if (!ok) return

            this.deletingId = id
            try {
                const res = await window.axios.delete(`/admin/activityLog/${id}`)
                this.toast(res?.data?.message || 'Delete Successfully!', 'success')
                this.load(this.meta.current_page || 1)
            } catch (e) {
                this.toast(e?.response?.data?.message || 'Delete failed.', 'error')
            } finally {
                this.deletingId = null
            }
        },
    },
}
</script>
