<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Student Migration</div>
                    <div class="mt-1 text-sm text-slate-600">Approve/Reject student migration requests</div>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Status</div>
                    <select v-model="filters.status" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" @change="search(true)">
                        <option value="">All</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="reject">Rejected</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Session</div>
                    <select v-model="filters.academic_session_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All Session</option>
                        <option v-for="s in sessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Academic Level</div>
                    <select v-model="filters.academic_qualification_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All Level</option>
                        <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Department</div>
                    <select v-model="filters.department_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All Department</option>
                        <option v-for="d in filteredDepartments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Class</div>
                    <select v-model="filters.academic_class_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All Class</option>
                        <option v-for="c in filteredClasses" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Migration Department</div>
                    <select v-model="filters.migrate_department_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="d in filteredDepartments" :key="'md-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">College Roll</div>
                    <input v-model="filters.value" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="Search college roll" />
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Per Page</div>
                    <select v-model.number="filters.pagination" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option :value="10">10</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                    </select>
                </div>

                <div class="lg:col-span-2 flex items-end">
                    <button
                        type="button"
                        class="h-10 w-full rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800"
                        :disabled="loading"
                        @click="search(true)"
                    >
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
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Session</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Department</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Academic Level / Class</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Migrate Department</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Student</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Admission ID</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">College Roll</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Payment Status</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Status</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="row in rows" :key="'m-' + row.id">
                            <td class="px-3 py-2 text-slate-800">{{ row.academic_session_name || '--' }}</td>
                            <td class="px-3 py-2 text-slate-800">{{ row.department_name || '--' }}</td>
                            <td class="px-3 py-2 text-slate-800">
                                {{ row.academic_qualification_name || '--' }}
                                <div class="text-xs text-slate-500">({{ row.academic_class_name || '--' }})</div>
                            </td>
                            <td class="px-3 py-2 text-slate-800">{{ row.migrate_department_name || '--' }}</td>
                            <td class="px-3 py-2 text-slate-800">
                                <div class="font-semibold">{{ row.student_name || '--' }}</div>
                                <div class="text-xs text-slate-500">{{ row.student_mobile || '' }}</div>
                            </td>
                            <td class="px-3 py-2 text-slate-800">{{ row.admission_id || '--' }}</td>
                            <td class="px-3 py-2 text-slate-800">{{ row.college_roll || '--' }}</td>
                            <td class="px-3 py-2 text-center">
                                <div v-if="row.invoices && row.invoices.length" class="flex flex-col gap-1">
                                    <span
                                        v-for="(inv, i) in row.invoices"
                                        :key="'inv-' + row.id + '-' + i"
                                        class="inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-semibold uppercase"
                                        :class="String(inv.status) === 'success' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'"
                                    >
                                        {{ inv.status }}
                                    </span>
                                </div>
                                <div v-else class="text-xs text-slate-500">No payments yet</div>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase" :class="statusClass(row.status)">
                                    {{ row.status || '--' }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button
                                        type="button"
                                        class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100"
                                        @click="openDetails(row)"
                                    >
                                        View
                                    </button>
                                    <button
                                        v-if="String(row.status) === 'pending'"
                                        type="button"
                                        class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100"
                                        :disabled="rejectingId === row.id"
                                        @click="reject(row.id)"
                                    >
                                        {{ rejectingId === row.id ? 'Rejecting...' : 'Reject' }}
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="!rows.length">
                            <td colspan="10" class="px-3 py-10 text-center text-sm text-slate-500">
                                {{ loading ? 'Loading...' : 'No data found' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="meta.total" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-slate-600">Showing {{ meta.from || 0 }} to {{ meta.to || 0 }} of {{ meta.total || 0 }} entries</div>

                <div class="flex flex-wrap items-center gap-1">
                    <button
                        type="button"
                        class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        :disabled="filters.page <= 1 || loading"
                        @click="goPage(filters.page - 1)"
                    >
                        Prev
                    </button>

                    <button
                        v-for="p in pageNumbers"
                        :key="'p-' + p"
                        type="button"
                        class="h-9 rounded-lg border px-3 text-sm font-semibold"
                        :class="p === filters.page ? 'border-emerald-300 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
                        :disabled="loading"
                        @click="goPage(p)"
                    >
                        {{ p }}
                    </button>

                    <button
                        type="button"
                        class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        :disabled="filters.page >= meta.last_page || loading"
                        @click="goPage(filters.page + 1)"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>

        <div v-if="detailsOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4" @click.self="closeDetails">
            <div class="w-full max-w-5xl rounded-2xl border border-slate-200 bg-white shadow-xl">
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
                    <div class="text-base font-semibold text-slate-900">Migrate Information</div>
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="closeDetails">Close</button>
                </div>

                <div class="p-5">
                    <div v-if="detailsLoading" class="py-10 text-center text-sm text-slate-600">Loading...</div>

                    <div v-else class="grid grid-cols-1 gap-4 xl:grid-cols-12">
                        <div class="xl:col-span-12 rounded-xl border border-slate-200">
                            <div class="border-b border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-800">PAYMENTS INFORMATION</div>
                            <div class="p-4">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                                        <thead class="bg-white">
                                            <tr>
                                                <th class="px-2 py-2 text-left font-semibold text-slate-700">#</th>
                                                <th class="px-2 py-2 text-left font-semibold text-slate-700">Invoice Date</th>
                                                <th class="px-2 py-2 text-left font-semibold text-slate-700">Invoice Number</th>
                                                <th class="px-2 py-2 text-left font-semibold text-slate-700">Purpose</th>
                                                <th class="px-2 py-2 text-left font-semibold text-slate-700">Amount</th>
                                                <th class="px-2 py-2 text-left font-semibold text-slate-700">Payment Date</th>
                                                <th class="px-2 py-2 text-center font-semibold text-slate-700">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200">
                                            <tr v-for="(inv, i) in details.invoices" :key="'inv2-' + i">
                                                <td class="px-2 py-2">{{ i + 1 }}</td>
                                                <td class="px-2 py-2">{{ inv.invoice_date || '' }}</td>
                                                <td class="px-2 py-2">{{ inv.invoice_number || '' }}</td>
                                                <td class="px-2 py-2">{{ inv.head_name || '' }}</td>
                                                <td class="px-2 py-2 font-semibold">{{ inv.amount || '' }}</td>
                                                <td class="px-2 py-2">{{ inv.payment_date || '' }}</td>
                                                <td class="px-2 py-2 text-center">
                                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase" :class="String(inv.status) === 'success' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'">
                                                        {{ inv.status || '' }}
                                                    </span>
                                                </td>
                                            </tr>

                                            <tr v-if="!details.invoices || !details.invoices.length">
                                                <td colspan="7" class="px-2 py-6 text-center text-sm text-slate-500">No payments</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-4 flex justify-end">
                                    <button
                                        v-if="canApprove"
                                        type="button"
                                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                                        :disabled="approving"
                                        @click="approved"
                                    >
                                        {{ approving ? 'Processing...' : 'APPROVED STUDENT' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="xl:col-span-6 rounded-xl border border-slate-200">
                            <div class="border-b border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-800">STUDENT INFO</div>
                            <div class="p-4">
                                <table class="w-full text-sm">
                                    <tbody class="divide-y divide-slate-200">
                                        <tr><th class="py-2 pr-3 text-left text-slate-600">Academic Session</th><td class="py-2">{{ details.academic_session?.name || '' }}</td></tr>
                                        <tr><th class="py-2 pr-3 text-left text-slate-600">Department</th><td class="py-2">{{ details.department?.name || '' }}</td></tr>
                                        <tr><th class="py-2 pr-3 text-left text-slate-600">Academic Level</th><td class="py-2">{{ details.qualification?.name || '' }}</td></tr>
                                        <tr><th class="py-2 pr-3 text-left text-slate-600">Class</th><td class="py-2">{{ details.academic_class?.name || '' }}</td></tr>
                                        <tr><th class="py-2 pr-3 text-left text-slate-600">Name</th><td class="py-2">{{ details.student?.name || '' }}</td></tr>
                                        <tr><th class="py-2 pr-3 text-left text-slate-600">Mobile</th><td class="py-2">{{ details.student?.mobile || '' }}</td></tr>
                                        <tr><th class="py-2 pr-3 text-left text-slate-600">College Roll</th><td class="py-2">{{ details.college_roll || '' }}</td></tr>
                                        <tr><th class="py-2 pr-3 text-left text-slate-600">Admission ID</th><td class="py-2">{{ details.student?.admission_id || '' }}</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="xl:col-span-6 rounded-xl border border-slate-200">
                            <div class="border-b border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-800">MIGRATE DEPARTMENT INFORMATION</div>
                            <div class="p-4">
                                <table class="w-full text-sm">
                                    <tbody class="divide-y divide-slate-200">
                                        <tr><th class="py-2 pr-3 text-left text-slate-600">Migrate Department</th><td class="py-2">{{ details.migrate_department?.name || '' }}</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'StudentMigrationIndex',
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
                field_name: 'college_roll',
                value: '',
                academic_class_id: '',
                academic_session_id: '',
                department_id: '',
                migrate_department_id: '',
                academic_qualification_id: '',
                status: 'pending',
                page: 1,
            },

            detailsOpen: false,
            detailsLoading: false,
            approving: false,
            rejectingId: null,
            details: {
                id: null,
                student_id: null,
                student: null,
                invoices: [],
                inoivce_paid: 0,
                inoivce_payable: 0,
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
            if (!qid) return this.departments

            const allowed = new Set(this.departmentQualidactions.filter((r) => String(r.academic_qualification_id) === String(qid)).map((r) => String(r.department_id)))
            return this.departments.filter((d) => allowed.has(String(d.id)))
        },
        filteredClasses() {
            const qid = this.filters?.academic_qualification_id
            if (!qid) return this.classes
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
        canApprove() {
            const paid = Number(this.details?.inoivce_paid || 0)
            const payable = Number(this.details?.inoivce_payable || 0)
            const status = String(this.details?.status || '')
            return paid === payable && status !== 'approved'
        },
    },
    watch: {
        'filters.academic_qualification_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.filters.department_id = ''
                this.filters.academic_class_id = ''
                this.filters.migrate_department_id = ''
            }
        },
    },
    mounted() {
        const adminDeptId = this.systems?.global?.auth_user?.department_id
        if (adminDeptId) {
            this.filters.migrate_department_id = String(adminDeptId)
        }
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
        statusClass(status) {
            const s = String(status || '')
            if (s === 'approved') return 'bg-emerald-50 text-emerald-700'
            if (s === 'reject') return 'bg-rose-50 text-rose-700'
            return 'bg-amber-50 text-amber-700'
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
                const res = await window.axios.get('/admin/studentMigration/list', { params: this.filters })
                this.rows = Array.isArray(res?.data?.data) ? res.data.data : []
                this.meta = res?.data?.meta || this.meta
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load data.'
                this.rows = []
            } finally {
                this.loading = false
            }
        },
        async openDetails(row) {
            this.detailsOpen = true
            this.detailsLoading = true
            this.error = ''
            this.details = { id: null, student_id: null, invoices: [], inoivce_paid: 0, inoivce_payable: 0 }
            try {
                const res = await window.axios.get(`/admin/studentMigration/${row.id}/details`)
                this.details = res?.data || this.details
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load details.'
            } finally {
                this.detailsLoading = false
            }
        },
        closeDetails() {
            this.detailsOpen = false
        },
        async approved() {
            if (this.approving) return
            if (!this.details?.id || !this.details?.student_id) {
                this.error = 'Student not selected'
                return
            }

            const ok = window.confirm('Are you sure want to approved?')
            if (!ok) return

            this.approving = true
            this.error = ''
            try {
                const payload = {
                    migrate_id: this.details.id,
                    student_id: this.details.student_id,
                }
                const res = await window.axios.post('/admin/studentMigration-approved', payload)
                const msg = res?.data?.message
                if (msg) {
                    this.closeDetails()
                    await this.search(false)
                } else {
                    this.error = res?.data?.error || 'Failed to approve.'
                }
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to approve.'
            } finally {
                this.approving = false
            }
        },
        async reject(id) {
            const ok = window.confirm('Reject?')
            if (!ok) return

            this.rejectingId = id
            this.error = ''
            try {
                await window.axios.get(`/admin/studentMigration-reject/${id}`)
                await this.search(false)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to reject.'
            } finally {
                this.rejectingId = null
            }
        },
    },
}
</script>
