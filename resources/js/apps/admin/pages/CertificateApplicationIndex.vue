<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Certificate Application</div>
                    <div class="mt-1 text-sm text-slate-600">Review, approve, print and manage certificate applications</div>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-12">
                <div class="xl:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Payment Status</div>
                    <select v-model="filters.payment_status" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" @change="search(true)">
                        <option value="">All</option>
                        <option value="pending">Dues</option>
                        <option value="success">Paid</option>
                        <option value="failed">Failed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="xl:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Application Status</div>
                    <select v-model="filters.application_status" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" @change="search(true)">
                        <option value="">All</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <div class="xl:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Session</div>
                    <select v-model="filters.academic_session_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm">
                        <option value="">All</option>
                        <option v-for="s in sessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                    </select>
                </div>

                <div class="xl:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Academic Level</div>
                    <select v-model="filters.academic_qualification_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm">
                        <option value="">All</option>
                        <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                    </select>
                </div>

                <div class="xl:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Search</div>
                    <div class="mt-1 flex gap-2">
                        <select v-model="filters.field_name" class="h-10 w-40 rounded-lg border border-slate-200 bg-white px-2 text-sm">
                            <option value="mobile">Mobile</option>
                            <option value="student_name_en">Student Name</option>
                            <option value="registration_no_en">Reg No</option>
                            <option value="college_roll_en">College Roll</option>
                            <option value="invoice_number">Invoice Number</option>
                        </select>
                        <input v-model="filters.value" type="text" class="h-10 flex-1 rounded-lg border border-slate-200 bg-white px-3 text-sm" placeholder="Search..." />
                    </div>
                </div>

                <div class="xl:col-span-1">
                    <div class="text-xs font-semibold text-slate-600">Per Page</div>
                    <select v-model.number="filters.pagination" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm">
                        <option :value="10">10</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                    </select>
                </div>

                <div class="xl:col-span-12 flex justify-end">
                    <button type="button" class="h-10 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white hover:bg-slate-800" :disabled="loading" @click="search(true)">
                        {{ loading ? 'Loading...' : 'Search' }}
                    </button>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Student Info</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">College Roll/Reg. No</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Academic Info</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Application Info</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Payment Info</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Fees</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">No. of Print</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Pay. Status</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">App. Status</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="row in rows" :key="'a-' + row.id">
                            <td class="px-3 py-2">
                                <div class="text-slate-900">
                                    <div>{{ row.student_name_en || row.student_name_bn || '' }}</div>
                                    <div class="text-xs text-slate-500">{{ row.mobile || '' }}</div>
                                </div>
                            </td>
                            <td class="px-3 py-2 text-slate-800">
                                <div>{{ row.college_roll_en || row.college_roll_bn || '' }}</div>
                                <div class="text-xs text-slate-500">{{ row.registration_no_en || row.registration_no_bn || '' }}</div>
                            </td>
                            <td class="px-3 py-2 text-slate-800">
                                <div>{{ row.qualification?.name || '--' }}</div>
                                <div class="text-xs text-slate-500">({{ row.academic_session?.name || '--' }})</div>
                            </td>
                            <td class="px-3 py-2 text-slate-800">
                                <div>{{ row.application_date || '' }}</div>
                                <div class="text-xs text-slate-500">{{ row.template?.title || '' }}</div>
                            </td>
                            <td class="px-3 py-2 text-slate-800">
                                <div>{{ row.invoice_number || '' }}</div>
                                <div class="text-xs text-slate-500">{{ row.invoice_date || '' }}</div>
                            </td>
                            <td class="px-3 py-2 text-center uppercase">
                                <div>{{ row.certificate_fees || '' }}</div>
                                <div v-if="String(row.certificate_fees) !== 'no'" class="font-semibold">{{ row.amount }}</div>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <div>{{ row.number_of_print ?? 0 }}</div>
                                <div class="text-xs text-slate-500">{{ row.print_date || '' }}</div>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase" :class="statusClass(row.payment_status)">
                                    {{ row.payment_status }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase" :class="statusClass(row.application_status)">
                                    {{ row.application_status }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button
                                        v-if="String(row.application_status) !== 'approved'"
                                        type="button"
                                        class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100"
                                        :disabled="approvingId === row.id"
                                        @click="approved(row.id)"
                                    >
                                        {{ approvingId === row.id ? '...' : 'Approve' }}
                                    </button>
                                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50" @click="goView(row.id)">View</button>
                                    <button type="button" class="rounded-lg border border-sky-200 bg-sky-50 px-3 py-1.5 text-xs font-semibold text-sky-700 hover:bg-sky-100" @click="goEdit(row.id)">Edit</button>
                                    <button
                                        v-if="String(row.application_status) !== 'approved' && Number(row.number_of_print || 0) === 0"
                                        type="button"
                                        class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100"
                                        :disabled="deletingId === row.id"
                                        @click="destroyRow(row)"
                                    >
                                        {{ deletingId === row.id ? '...' : (String(row.application_status) === 'pending' ? 'Reject' : 'Delete') }}
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="!rows.length">
                            <td colspan="10" class="px-3 py-10 text-center text-sm text-slate-500">{{ loading ? 'Loading...' : 'No data found' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="meta.total" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-slate-600">Showing {{ meta.from || 0 }} to {{ meta.to || 0 }} of {{ meta.total || 0 }} entries</div>
                <div class="flex flex-wrap items-center gap-1">
                    <button type="button" class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="filters.page <= 1 || loading" @click="goPage(filters.page - 1)">Prev</button>
                    <button v-for="p in pageNumbers" :key="'p-' + p" type="button" class="h-9 rounded-lg border px-3 text-sm font-semibold" :class="p === filters.page ? 'border-emerald-300 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'" :disabled="loading" @click="goPage(p)">{{ p }}</button>
                    <button type="button" class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="filters.page >= meta.last_page || loading" @click="goPage(filters.page + 1)">Next</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'CertificateApplicationIndex',
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
            approvingId: null,
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
                field_name: 'mobile',
                value: '',
                payment_status: '',
                application_status: '',
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                certificate_template_id: '',
                page: 1,
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
        goView(id) {
            window.location.href = `/admin/certificateApplication/${id}`
        },
        goEdit(id) {
            window.location.href = `/admin/certificateApplication/${id}/edit`
        },
        goPage(p) {
            const page = Math.max(1, Number(p || 1))
            this.filters.page = page
            this.search(false)
        },
        statusClass(status) {
            const s = String(status || '')
            if (s === 'pending') return 'bg-amber-50 text-amber-700'
            if (s === 'rejected' || s === 'failed' || s === 'cancelled') return 'bg-rose-50 text-rose-700'
            return 'bg-emerald-50 text-emerald-700'
        },
        async search(resetPage) {
            if (resetPage) this.filters.page = 1
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get('/admin/certificateApplication/list', { params: this.filters })
                this.rows = Array.isArray(res?.data?.data) ? res.data.data : []
                this.meta = res?.data?.meta || this.meta
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load data.'
                this.rows = []
            } finally {
                this.loading = false
            }
        },
        async approved(id) {
            if (this.approvingId) return
            const ok = window.confirm('Are you sure want to approved?')
            if (!ok) return

            this.approvingId = id
            try {
                const res = await window.axios.post('/admin/certificateApplication-approved', { id })
                if (res?.data?.message) {
                    await this.search(false)
                }
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to approve.'
            } finally {
                this.approvingId = null
            }
        },
        async destroyRow(row) {
            const ok = window.confirm('Are you sure ?')
            if (!ok) return

            this.deletingId = row.id
            try {
                await window.axios.delete(`/admin/certificateApplication/${row.id}`)
                await this.search(false)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed.'
            } finally {
                this.deletingId = null
            }
        },
    },
}
</script>
