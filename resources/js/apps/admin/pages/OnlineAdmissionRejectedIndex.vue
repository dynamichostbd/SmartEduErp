<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Online Admission (Rejected)</div>
                    <div class="mt-1 text-sm text-slate-600">Rejected applicant list</div>
                </div>
            </div>
        </div>

        <div v-if="message" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
            {{ message }}
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-12">
                <div class="xl:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Session</div>
                    <select v-model="filters.academic_session_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm">
                        <option value="">All</option>
                        <option v-for="s in sessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                    </select>
                </div>

                <div class="xl:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Department</div>
                    <select v-model="filters.department_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm">
                        <option value="">All</option>
                        <option v-for="d in departments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                    </select>
                </div>

                <div class="xl:col-span-4">
                    <div class="text-xs font-semibold text-slate-600">Search</div>
                    <div class="mt-1 flex gap-2">
                        <select v-model="filters.field_name" class="h-10 w-40 rounded-lg border border-slate-200 bg-white px-2 text-sm">
                            <option value="mobile">Mobile</option>
                            <option value="name">Name</option>
                            <option value="admission_roll">Admission Roll</option>
                            <option value="registration_no">Reg No</option>
                        </select>
                        <input v-model="filters.value" type="text" class="h-10 flex-1 rounded-lg border border-slate-200 bg-white px-3 text-sm" placeholder="Search..." />
                    </div>
                </div>

                <div class="xl:col-span-2 flex items-end justify-end">
                    <button
                        type="button"
                        class="h-10 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white hover:bg-slate-800"
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
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Applicant</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Academic</th>
                            <th class="px-3 py-2 text-right font-semibold text-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="row in rows" :key="'r-' + row.id">
                            <td class="px-3 py-2">
                                <div class="font-semibold text-slate-900">{{ row.name || '--' }}</div>
                                <div class="text-xs text-slate-500">{{ row.mobile || '' }}</div>
                                <div class="text-xs text-slate-500">Roll: {{ row.admission_roll || '--' }} | Reg: {{ row.registration_no || '--' }}</div>
                            </td>
                            <td class="px-3 py-2 text-slate-800">
                                <div>{{ row.academic_qualification_name || '--' }} ({{ row.academic_class_name || '--' }})</div>
                                <div class="text-xs text-slate-500">{{ row.academic_session_name || '--' }} | {{ row.department_name || '--' }}</div>
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <button
                                        type="button"
                                        class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                        @click="openView(row.id)"
                                    >
                                        View
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                        @click="goEdit(row.id)"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100"
                                        :disabled="approvingId === row.id"
                                        @click="approve(row.id)"
                                    >
                                        {{ approvingId === row.id ? 'Approving...' : 'Approve' }}
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="!rows.length">
                            <td colspan="3" class="px-3 py-10 text-center text-sm text-slate-500">
                                {{ loading ? 'Loading...' : 'No data found' }}
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

        <div v-if="viewOpen" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-slate-900/40 p-4" @click.self="closeView">
            <div class="w-full max-w-3xl rounded-2xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div class="text-sm font-semibold text-slate-900">Applicant Details</div>
                    <button class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50" @click="closeView">Close</button>
                </div>

                <div class="p-5">
                    <div v-if="viewLoading" class="text-sm text-slate-600">Loading...</div>
                    <div v-else-if="viewError" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ viewError }}</div>
                    <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="text-xs font-semibold text-slate-500">Name</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ viewItem?.name || '--' }}</div>
                            <div class="mt-2 text-xs font-semibold text-slate-500">Mobile</div>
                            <div class="mt-1 text-sm text-slate-900">{{ viewItem?.mobile || '--' }}</div>
                            <div class="mt-2 text-xs font-semibold text-slate-500">Email</div>
                            <div class="mt-1 text-sm text-slate-900">{{ viewItem?.email || '--' }}</div>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="text-xs font-semibold text-slate-500">Academic</div>
                            <div class="mt-1 text-sm text-slate-900">{{ viewItem?.qualification?.name || '--' }}</div>
                            <div class="mt-1 text-xs text-slate-500">{{ viewItem?.academic_session?.name || '--' }} | {{ viewItem?.department?.name || '--' }} | {{ viewItem?.academic_class?.name || '--' }}</div>
                            <div class="mt-3 text-xs font-semibold text-slate-500">Roll / Reg</div>
                            <div class="mt-1 text-sm text-slate-900">{{ viewItem?.admission_roll || '--' }} / {{ viewItem?.registration_no || '--' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="approvedOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4" @click.self="approvedOpen = false">
            <div class="w-full max-w-md rounded-2xl bg-white shadow-xl">
                <div class="border-b border-slate-200 px-5 py-4">
                    <div class="text-sm font-semibold text-slate-900">Approved Successfully</div>
                </div>
                <div class="p-5">
                    <div class="text-sm text-slate-700">Password: <span class="font-semibold">{{ approvedPassword }}</span></div>
                    <div class="mt-4 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                            @click="approvedOpen = false"
                        >
                            Close
                        </button>
                        <button
                            type="button"
                            class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800"
                            :disabled="smsSending"
                            @click="sendApprovedSms"
                        >
                            {{ smsSending ? 'Sending...' : 'Send SMS' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'OnlineAdmissionRejectedIndex',
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
            message: '',
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
                academic_session_id: '',
                department_id: '',
                field_name: 'mobile',
                value: '',
                pagination: 10,
                page: 1,
            },
            approvingId: null,
            viewOpen: false,
            viewLoading: false,
            viewError: '',
            viewItem: null,
            approvedOpen: false,
            approvedApplicantMobile: '',
            approvedPassword: '',
            smsSending: false,
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
        departments() {
            const list = this.systems?.global?.departments
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
        goEdit(id) {
            window.location.href = `/admin/onlineAdmission/${id}/edit`
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
            this.message = ''
            try {
                const res = await window.axios.get('/admin/onlineAdmission/rejected-list', { params: this.filters })
                this.rows = Array.isArray(res?.data?.data) ? res.data.data : []
                this.meta = res?.data?.meta || this.meta
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load data.'
                this.rows = []
            } finally {
                this.loading = false
            }
        },
        async approve(id) {
            const ok = window.confirm('Approve this applicant?')
            if (!ok) return

            this.approvingId = id
            this.error = ''
            this.message = ''
            try {
                const res = await window.axios.post('/admin/onlineAdmission-approved', { id })

                if (Number(res?.status) === 201) {
                    this.error = res?.data?.message || 'Conflict found. Please update mobile and try again.'
                    return
                }

                const password = res?.data?.password || ''
                const student = res?.data?.data || null

                this.approvedApplicantMobile = student?.mobile || ''
                this.approvedPassword = String(password || '')
                this.approvedOpen = true

                await this.search(false)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to approve.'
            } finally {
                this.approvingId = null
            }
        },
        async sendApprovedSms() {
            if (!this.approvedApplicantMobile || !this.approvedPassword) {
                this.approvedOpen = false
                return
            }

            this.smsSending = true
            this.error = ''
            try {
                const res = await window.axios.get('/admin/approved-sms-send', {
                    params: {
                        mobile: this.approvedApplicantMobile,
                        password: this.approvedPassword,
                    },
                })
                this.message = res?.data?.message || 'SMS sent successfully'
                this.approvedOpen = false
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to send SMS.'
            } finally {
                this.smsSending = false
            }
        },
        async openView(id) {
            this.viewOpen = true
            this.viewLoading = true
            this.viewError = ''
            this.viewItem = null

            try {
                const res = await window.axios.get(`/admin/onlineAdmission/${id}/details`)
                this.viewItem = res?.data || null
            } catch (e) {
                this.viewError = e?.response?.data?.message || 'Failed to load details.'
            } finally {
                this.viewLoading = false
            }
        },
        closeView() {
            this.viewOpen = false
            this.viewError = ''
            this.viewItem = null
        },
    },
}
</script>
