<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="text-xl font-semibold text-slate-900">{{ heading }}</div>
                </div>

                <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                    <select
                        v-model="filters.field_name"
                        class="h-9 rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                        :disabled="loading"
                    >
                        <option value="title">Title</option>
                    </select>

                    <input
                        v-model="filters.value"
                        type="text"
                        class="h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 sm:w-72"
                        placeholder="Search..."
                        :disabled="loading"
                        @keyup.enter="load(1)"
                    />

                    <div class="flex items-center gap-2">
                        <button class="inline-flex h-9 w-10 items-center justify-center rounded-sm bg-emerald-600 text-white hover:bg-emerald-700" :disabled="loading" @click="load(1)">
                            <span class="sr-only">Search</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.35-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>

                        <button v-if="canCreate" class="h-9 rounded-sm bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800" :disabled="loading" @click="goCreate">
                            Add
                        </button>

                        <button class="h-9 rounded-sm border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading" @click="reset">
                            Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div class="overflow-hidden  border border-slate-300 bg-white">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-slate-300 text-sm">
                    <thead class="bg-emerald-100">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-700">
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">#</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Title</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">Type</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">File</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">Status</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <tr v-if="loading" class="text-sm text-slate-600">
                            <td class="border border-slate-300 px-4 py-6" colspan="6">Loading...</td>
                        </tr>
                        <tr v-else-if="rows.length === 0" class="text-sm text-slate-600">
                            <td class="border border-slate-300 px-4 py-6" colspan="6">No data found.</td>
                        </tr>
                        <tr v-for="(row, idx) in rows" v-else :key="'r-' + row.id" :class="idx % 2 === 0 ? 'bg-white' : 'bg-slate-50'">
                            <td class="border border-slate-300 px-1 py-1 text-slate-600">{{ rowSerial(idx) }}</td>
                            <td class="border border-slate-300 px-1 py-1 font-medium text-slate-900">
                                <a href="#" class="text-emerald-700 hover:underline" @click.prevent="openViewModal(row)">{{ row.title || '—' }}</a>
                            </td>
                            <td class="border border-slate-300 px-1 py-1 text-center">{{ row.file_type || '—' }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-center">
                                <template v-if="row.file">
                                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100" title="View File" @click="viewFile(row.file)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 ml-2" title="Download File" @click="downloadFile(row.file)">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </template>
                                <span v-else>—</span>
                            </td>
                            <td class="border border-slate-300 px-1 py-1 text-center">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" :class="String(row.status || '') === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700'">
                                    {{ row.status || '—' }}
                                </span>
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

    <!-- View Modal -->
    <div v-if="showViewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50">
        <div class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-sm bg-white p-6">
            <div class="mb-4 flex items-center justify-between border-b pb-4">
                <h3 class="text-lg font-semibold text-slate-900">{{ modalTitle }} Details</h3>
                <button type="button" class="text-slate-400 hover:text-slate-600" @click="closeViewModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div class="border-b pb-3">
                    <h4 class="text-sm font-semibold text-slate-700 mb-2">Title</h4>
                    <p class="text-slate-900">{{ viewItem.title || '—' }}</p>
                </div>

                <div class="border-b pb-3">
                    <h4 class="text-sm font-semibold text-slate-700 mb-2">Type</h4>
                    <p class="text-slate-900">{{ viewItem.file_type || '—' }}</p>
                </div>

                <div class="border-b pb-3">
                    <h4 class="text-sm font-semibold text-slate-700 mb-2">File</h4>
                    <template v-if="viewItem.file">
                        <div class="flex items-center gap-3">
                            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100" title="View File" @click="viewFile(viewItem.file)">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100" title="Download File" @click="downloadFile(viewItem.file)">
                                <i class="fas fa-download"></i>
                            </button>
                            <span class="text-sm text-slate-600">{{ viewItem.file }}</span>
                        </div>
                    </template>
                    <span v-else class="text-slate-500">No File</span>
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-slate-700 mb-2">Status</h4>
                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" :class="String(viewItem.status || '') === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700'">
                        {{ viewItem.status || '—' }}
                    </span>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="closeViewModal">Close</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'WebsiteFileModuleIndex',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        module: {
            type: String,
            default: '',
        },
    },
    data() {
        return {
            loading: false,
            deletingId: null,
            error: '',
            showViewModal: false,
            viewItem: {
                title: '',
                file_type: '',
                file: '',
                status: '',
            },
            rows: [],
            searchTimer: null,
            filters: {
                pagination: 10,
                field_name: 'title',
                value: '',
                module_type: '',
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
        heading() {
            const map = {
                slider: 'Slider',
                videoSlider: 'Video Slider',
                bus: 'Bus',
                calender: 'Calender',
                class: 'Class Routine',
                examR: 'Exam Routine',
            }
            return map[this.module] || 'Website'
        },
        canCreate() {
            return !this.perms.length || this.perms.includes(`${this.module}.create`)
        },
        canEdit() {
            return !this.perms.length || this.perms.includes(`${this.module}.edit`)
        },
        canDelete() {
            return !this.perms.length || this.perms.includes(`${this.module}.destroy`)
        },
        modalTitle() {
            const map = {
                slider: 'Slider',
                videoSlider: 'Video Slider',
                bus: 'Bus',
                calender: 'Calender',
                class: 'Class Routine',
                examR: 'Exam Routine',
            }
            return map[this.module] || 'Website'
        },
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
    watch: {
        module: {
            handler() {
                this.load(1)
            },
            immediate: true,
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
            this.go(`/admin/${this.module}/create`)
        },
        goView(id) {
            this.go(`/admin/${this.module}/${id}`)
        },
        goEdit(id) {
            this.go(`/admin/${this.module}/${id}/edit`)
        },
        reset() {
            this.filters.pagination = 10
            this.filters.field_name = 'title'
            this.filters.value = ''
            this.filters.module_type = ''
            this.load(1)
        },
        openViewModal(item) {
            this.viewItem = {
                title: item.title || '',
                file_type: item.file_type || '',
                file: item.file || '',
                status: item.status || '',
            }
            this.showViewModal = true
        },
        closeViewModal() {
            this.showViewModal = false
        },
        viewFile(file) {
            const url = this.resolveFile(file)
            window.open(url, '_blank')
        },
        downloadFile(file) {
            const url = this.resolveFile(file)
            const link = document.createElement('a')
            link.href = url
            link.download = file
            link.target = '_blank'
            document.body.appendChild(link)
            link.click()
            document.body.removeChild(link)
        },
        resolveFile(file) {
            if (!file) return '#'
            // Check if it's already a full URL
            if (file.startsWith('http')) return file
            // For local files, construct the URL
            return `/storage/${file}`
        },
        async load(page = 1) {
            if (!this.module) return

            this.loading = true
            this.error = ''

            try {
                const res = await window.axios.get(`/admin/${this.module}`, {
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
                this.error = e?.response?.data?.message || 'Failed to load.'
            } finally {
                this.loading = false
            }
        },
        async destroy(id) {
            if (!this.canDelete) return
            if (!confirm('Are you sure?')) return

            this.deletingId = id
            try {
                const res = await window.axios.delete(`/admin/${this.module}/${id}`)
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
