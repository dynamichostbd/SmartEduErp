<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="text-xl font-semibold text-slate-900">Subject Cluster</div>
                </div>

                <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                    <select v-model="filters.field_name" class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200" :disabled="loading">
                        <option value="name_en">Name (en)</option>
                        <option value="name_bn">Name (bn)</option>
                    </select>

                    <input v-model="filters.value" type="text" class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200 sm:w-72" placeholder="Search..." :disabled="loading" @keyup.enter="load(1)" />

                    <div class="flex items-center gap-2">
                        <button class="inline-flex h-9 w-10 items-center justify-center rounded-lg bg-emerald-600 text-white hover:bg-emerald-700" :disabled="loading" @click="load(1)">
                            <span class="sr-only">Search</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.35-5.15a7 7 0 11-14 0 7 0 0114 0z" />
                            </svg>
                        </button>

                        <button v-if="canCreate" class="h-9 rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800" :disabled="loading" @click="goCreate">Add</button>

                        <button class="h-9 rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading" @click="reset">Reset</button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-slate-200">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3">#</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3">Academic Level</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3">Department</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3">Name (en)</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3">Name (bn)</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3">Subjects</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3 text-center">Min. Select</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3 text-center">Fixed</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading" class="text-sm text-slate-600">
                            <td class="border border-slate-200 px-4 py-6" colspan="9">Loading...</td>
                        </tr>
                        <tr v-else-if="rows.length === 0" class="text-sm text-slate-600">
                            <td class="border border-slate-200 px-4 py-6" colspan="9">No data found.</td>
                        </tr>
                        <tr v-for="(row, idx) in rows" v-else :key="row.id" class="text-sm text-slate-800" :class="idx % 2 === 0 ? 'bg-white' : 'bg-slate-50'">
                            <td class="border border-slate-200 px-4 py-3 text-slate-600">{{ rowSerial(idx) }}</td>
                            <td class="border border-slate-200 px-4 py-3">{{ row.qualification_name || '—' }}</td>
                            <td class="border border-slate-200 px-4 py-3">{{ row.department_name || '—' }}</td>
                            <td class="border border-slate-200 px-4 py-3 font-medium text-slate-900">
                                <a href="#" class="text-emerald-700 hover:underline" @click.prevent="goView(row.id)">{{ row.name_en || '—' }}</a>
                            </td>
                            <td class="border border-slate-200 px-4 py-3">{{ row.name_bn || '—' }}</td>
                            <td class="border border-slate-200 px-4 py-3">
                                <div class="max-w-[480px] whitespace-normal">
                                    {{ subjectsText(row.subjects_json) || '—' }}
                                </div>
                            </td>
                            <td class="border border-slate-200 px-4 py-3 text-center">{{ row.minimum_select ?? '—' }}</td>
                            <td class="border border-slate-200 px-4 py-3 text-center">{{ Number(row.fixed_subject || 0) === 1 ? 'Yes' : 'No' }}</td>
                            <td class="border border-slate-200 px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-slate-50" title="View" @click="goView(row.id)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-7 9.5-7 9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
                                        </svg>
                                    </button>
                                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-slate-50" title="Edit" :disabled="!canEdit" @click="goEdit(row.id)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a2.25 2.25 0 013.182 3.182L8.25 18.463 3 21l2.538-5.25L16.862 3.487z" />
                                        </svg>
                                    </button>
                                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-red-200 bg-white text-red-700 hover:bg-red-50" title="Delete" :disabled="!canDelete || deletingId === row.id" @click="destroy(row.id)">
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

            <div class="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-xs text-slate-600">Showing {{ meta.from || 0 }} to {{ meta.to || 0 }} of {{ meta.total || 0 }} entries</div>

                <div class="flex items-center justify-end gap-1 overflow-x-auto">
                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading || meta.current_page <= 1" @click="load(meta.current_page - 1)">«</button>
                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading || meta.current_page >= meta.last_page" @click="load(meta.current_page + 1)">»</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'SubjectClusterIndex',
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
            subjectsById: {},
            filters: {
                pagination: 10,
                field_name: 'name_en',
                value: '',
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
            return !this.perms.length || this.perms.includes('subjectCluster.create')
        },
        canEdit() {
            return !this.perms.length || this.perms.includes('subjectCluster.edit')
        },
        canDelete() {
            return !this.perms.length || this.perms.includes('subjectCluster.destroy')
        },
    },
    async created() {
        await this.loadSubjects()
        await this.load(1)
    },
    methods: {
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
            this.go('/admin/subjectCluster/create')
        },
        goView(id) {
            this.go(`/admin/subjectCluster/${id}`)
        },
        goEdit(id) {
            this.go(`/admin/subjectCluster/${id}/edit`)
        },
        reset() {
            this.filters.pagination = 10
            this.filters.field_name = 'name_en'
            this.filters.value = ''
            this.load(1)
        },
        parseSubjectsJson(v) {
            if (!v) return []
            if (Array.isArray(v)) return v
            if (typeof v === 'string') {
                try {
                    const out = JSON.parse(v)
                    return Array.isArray(out) ? out : []
                } catch {
                    return []
                }
            }
            return []
        },
        subjectsText(subjectsJson) {
            const ids = this.parseSubjectsJson(subjectsJson)
            if (!ids.length) return ''
            const names = ids
                .map((id) => {
                    const s = this.subjectsById[String(id)]
                    return s?.name_bn || s?.name_en || s?.name || null
                })
                .filter(Boolean)
            return names.join(', ')
        },
        async loadSubjects() {
            try {
                const res = await window.axios.get('/admin/subject', { params: { allData: true } })
                const list = Array.isArray(res?.data) ? res.data : []
                const map = {}
                for (const s of list) {
                    map[String(s.id)] = s
                }
                this.subjectsById = map
            } catch {
                this.subjectsById = {}
            }
        },
        async load(page = 1) {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get('/admin/subjectCluster', {
                    params: {
                        page,
                        pagination: this.filters.pagination,
                        field_name: this.filters.field_name,
                        value: this.filters.value,
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
                this.error = e?.response?.data?.message || 'Failed to load subject clusters.'
            } finally {
                this.loading = false
            }
        },
        async destroy(id) {
            if (!this.canDelete) return
            if (!confirm('Are you sure?')) return
            this.deletingId = id
            try {
                const res = await window.axios.delete(`/admin/subjectCluster/${id}`)
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
