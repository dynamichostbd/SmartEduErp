<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="text-xl font-semibold text-slate-900">Teacher Attendance</div>
                </div>

                <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                    <input v-model="filters.from_date" type="date" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200" :disabled="loading" />
                    <input v-model="filters.to_date" type="date" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200" :disabled="loading" />

                    <div class="flex items-center gap-2">
                        <button class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-600 text-white hover:bg-emerald-700" :disabled="loading" @click="load(1)">
                            <span class="sr-only">Search</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.35-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                        <button class="h-10 rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading" @click="reset">Reset</button>
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
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3">Date</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3 text-center">Total Teacher</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3 text-center">Total Present</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading" class="text-sm text-slate-600">
                            <td class="border border-slate-200 px-4 py-6" colspan="4">Loading...</td>
                        </tr>
                        <tr v-else-if="rows.length === 0" class="text-sm text-slate-600">
                            <td class="border border-slate-200 px-4 py-6" colspan="4">No data found.</td>
                        </tr>
                        <tr v-for="(row, idx) in rows" v-else :key="row.id" class="text-sm text-slate-800" :class="idx % 2 === 0 ? 'bg-white' : 'bg-slate-50'">
                            <td class="border border-slate-200 px-4 py-3 text-slate-600">{{ rowSerial(idx) }}</td>
                            <td class="border border-slate-200 px-4 py-3">{{ row.date || '—' }}</td>
                            <td class="border border-slate-200 px-4 py-3 text-center">{{ row.total_teacher ?? '—' }}</td>
                            <td class="border border-slate-200 px-4 py-3 text-center">{{ row.total_present ?? '—' }}</td>
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
    name: 'TeacherAttendanceIndex',
    data() {
        return {
            loading: false,
            error: '',
            rows: [],
            filters: {
                pagination: 10,
                from_date: '',
                to_date: '',
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
    created() {
        this.load(1)
    },
    methods: {
        rowSerial(idx) {
            const from = Number(this.meta.from || 0)
            return from + idx
        },
        reset() {
            this.filters.pagination = 10
            this.filters.from_date = ''
            this.filters.to_date = ''
            this.load(1)
        },
        async load(page = 1) {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get('/admin/teacherAttendance', {
                    params: {
                        page,
                        pagination: this.filters.pagination,
                        from_date: this.filters.from_date || null,
                        to_date: this.filters.to_date || null,
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
                this.error = e?.response?.data?.message || 'Failed to load teacher attendance.'
            } finally {
                this.loading = false
            }
        },
    },
}
</script>
