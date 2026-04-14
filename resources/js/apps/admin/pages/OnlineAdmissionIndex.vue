<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Online Admission</div>
                    <div class="mt-1 text-sm text-slate-600">Applicant list</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        :disabled="downloading || !selectedIds.length"
                        @click="downloadForms"
                    >
                        {{ downloading ? 'Preparing...' : 'Download Form' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="message" class=" border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
            {{ message }}
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div class=" border border-slate-300 bg-white p-5">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-12">
                <div class="xl:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Status</div>
                    <select v-model="filters.status" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                        <option value="">All</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <div class="xl:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Session</div>
                    <select v-model="filters.academic_session_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                        <option value="">All</option>
                        <option v-for="s in sessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                    </select>
                </div>

                <div class="xl:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Academic Level</div>
                    <select v-model="filters.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                        <option value="">All</option>
                        <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                    </select>
                </div>

                <div class="xl:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Department</div>
                    <select v-model="filters.department_id" :disabled="!filters.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                        <option value="">All</option>
                        <option v-for="d in filteredDepartments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                    </select>
                </div>

                <div class="xl:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Class</div>
                    <select v-model="filters.academic_class_id" :disabled="!filters.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                        <option value="">All</option>
                        <option v-for="c in filteredClasses" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                    </select>
                </div>

                <div class="xl:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Gender</div>
                    <select v-model="filters.gender" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                        <option value="">All</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>

                <div class="xl:col-span-4">
                    <div class="text-xs font-semibold text-slate-600">Search</div>
                    <div class="mt-1 flex gap-2">
                        <select v-model="filters.field_name" class="h-9 w-44 rounded-sm border border-slate-300 bg-white px-2 text-sm">
                            <option value="mobile">Mobile</option>
                            <option value="name">Name</option>
                            <option value="admission_roll">Admission Roll</option>
                            <option value="registration_no">Reg No</option>
                        </select>
                        <input v-model="filters.value" type="text" class="h-9 flex-1 rounded-sm border border-slate-300 bg-white px-3 text-sm" placeholder="Search..." />
                    </div>
                </div>

                <div class="xl:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Per Page</div>
                    <select v-model.number="filters.pagination" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                        <option :value="10">10</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                    </select>
                </div>

                <div class="xl:col-span-4 flex items-end justify-end">
                    <button
                        type="button"
                        class="h-9 rounded-sm bg-slate-900 px-5 text-sm font-semibold text-white hover:bg-slate-800"
                        :disabled="loading"
                        @click="search(true)"
                    >
                        {{ loading ? 'Loading...' : 'Search' }}
                    </button>
                </div>
            </div>
        </div>

        <div class=" border border-slate-300 bg-white">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-slate-300 text-sm">
                    <thead class="bg-emerald-100">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-700">
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center font-semibold text-slate-700" style="width: 44px;">
                                <input type="checkbox" :checked="allSelected" @change="toggleAll" />
                            </th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-left font-semibold text-slate-700">Applicant</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-left font-semibold text-slate-700">Academic</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-left font-semibold text-slate-700">Status</th>
                            <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-right font-semibold text-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <tr v-for="(row, idx) in rows" :key="'r-' + row.id" :class="idx % 2 === 0 ? 'bg-white' : 'bg-slate-50'">
                            <td class="border border-slate-300 px-1 py-1 text-center">
                                <input type="checkbox" :value="row.id" v-model="selectedIds" />
                            </td>
                            <td class="border border-slate-300 px-1 py-1">
                                <div class="font-semibold text-slate-900">{{ row.name || '--' }}</div>
                                <div class="text-xs text-slate-500">{{ row.mobile || '' }}</div>
                                <div class="text-xs text-slate-500">Roll: {{ row.admission_roll || '--' }} | Reg: {{ row.registration_no || '--' }}</div>
                            </td>
                            <td class="border border-slate-300 px-1 py-1 text-slate-800">
                                <div>{{ row.academic_qualification_name || '--' }} ({{ row.academic_class_name || '--' }})</div>
                                <div class="text-xs text-slate-500">{{ row.academic_session_name || '--' }} | {{ row.department_name || '--' }}</div>
                            </td>
                            <td class="border border-slate-300 px-1 py-1">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="badgeClass(row.status)"
                                >
                                    {{ row.status || 'pending' }}
                                </span>
                            </td>
                            <td class="border border-slate-300 px-1 py-1">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-slate-300 bg-white text-slate-700 hover:bg-slate-50"
                                        title="View"
                                        @click="openView(row.id)"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-slate-300 bg-white text-slate-700 hover:bg-slate-50"
                                        title="Edit"
                                        @click="goEdit(row.id)"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100"
                                        :disabled="approvingId === row.id"
                                        title="Approve"
                                        @click="approve(row.id)"
                                    >
                                        <i v-if="approvingId === row.id" class="fas fa-spinner fa-spin"></i>
                                        <i v-else class="fas fa-check"></i>
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100"
                                        title="Subject Choice"
                                        @click="openSubjectChoice(row.id)"
                                    >
                                        <i class="fas fa-list"></i>
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-red-200 bg-red-50 text-red-700 hover:bg-red-100"
                                        :disabled="rejectingId === row.id"
                                        title="Reject"
                                        @click="reject(row.id)"
                                    >
                                        <i v-if="rejectingId === row.id" class="fas fa-spinner fa-spin"></i>
                                        <i v-else class="fas fa-times"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="!rows.length">
                            <td colspan="5" class="border border-slate-300 px-3 py-10 text-center text-sm text-slate-500">
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
                        class="h-9 rounded-sm border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        :disabled="filters.page <= 1 || loading"
                        @click="goPage(filters.page - 1)"
                    >
                        Prev
                    </button>

                    <button
                        v-for="p in pageNumbers"
                        :key="'p-' + p"
                        type="button"
                        class="h-9 rounded-sm border px-3 text-sm font-semibold"
                        :class="p === filters.page ? 'border-emerald-300 bg-emerald-50 text-emerald-700' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'"
                        :disabled="loading"
                        @click="goPage(p)"
                    >
                        {{ p }}
                    </button>

                    <button
                        type="button"
                        class="h-9 rounded-sm border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        :disabled="filters.page >= meta.last_page || loading"
                        @click="goPage(filters.page + 1)"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>

        <div v-if="viewOpen" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-slate-900/40 p-4" @click.self="closeView">
            <div class="w-full max-w-3xl  bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-300 px-5 py-4">
                    <div class="text-sm font-semibold text-slate-900">Applicant Details</div>
                    <button class="rounded-sm border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50" @click="closeView">Close</button>
                </div>

                <div class="p-5">
                    <div v-if="viewLoading" class="text-sm text-slate-600">Loading...</div>
                    <div v-else-if="viewError" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ viewError }}</div>
                    <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="rounded-xl border border-slate-300 p-4">
                            <div class="text-xs font-semibold text-slate-500">Name</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ viewItem?.name || '--' }}</div>
                            <div class="mt-2 text-xs font-semibold text-slate-500">Mobile</div>
                            <div class="mt-1 text-sm text-slate-900">{{ viewItem?.mobile || '--' }}</div>
                            <div class="mt-2 text-xs font-semibold text-slate-500">Email</div>
                            <div class="mt-1 text-sm text-slate-900">{{ viewItem?.email || '--' }}</div>
                        </div>

                        <div class="rounded-xl border border-slate-300 p-4">
                            <div class="text-xs font-semibold text-slate-500">Academic</div>
                            <div class="mt-1 text-sm text-slate-900">{{ viewItem?.qualification?.name || '--' }}</div>
                            <div class="mt-1 text-xs text-slate-500">{{ viewItem?.academic_session?.name || '--' }} | {{ viewItem?.department?.name || '--' }} | {{ viewItem?.academic_class?.name || '--' }}</div>
                            <div class="mt-3 text-xs font-semibold text-slate-500">Roll / Reg</div>
                            <div class="mt-1 text-sm text-slate-900">{{ viewItem?.admission_roll || '--' }} / {{ viewItem?.registration_no || '--' }}</div>
                        </div>

                        <div class="md:col-span-2 rounded-xl border border-slate-300 p-4">
                            <div class="text-xs font-semibold text-slate-500">Address</div>
                            <div class="mt-1 text-sm text-slate-900">{{ viewItem?.address || '--' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="subjectOpen" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-slate-900/40 p-4" @click.self="closeSubjectChoice">
            <div class="w-full max-w-4xl  bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-300 px-5 py-4">
                    <div class="text-sm font-semibold text-slate-900">Subject Choice</div>
                    <div class="flex items-center gap-2">
                        <button
                            class="rounded-sm bg-emerald-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700"
                            :disabled="subjectSaving || subjectLoading"
                            @click="saveSubjectChoice"
                        >
                            {{ subjectSaving ? 'Saving...' : 'Save' }}
                        </button>
                        <button class="rounded-sm border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50" @click="closeSubjectChoice">Close</button>
                    </div>
                </div>

                <div class="p-5">
                    <div v-if="subjectLoading" class="text-sm text-slate-600">Loading...</div>
                    <div v-else-if="subjectError" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ subjectError }}</div>
                    <div v-else class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                        <div class="lg:col-span-4 rounded-xl border border-slate-300 p-4">
                            <div class="text-xs font-semibold text-slate-600">Compulsory Subjects</div>
                            <div class="mt-3 space-y-2">
                                <div v-for="s in compulsorySubjects" :key="'c-' + s.id" class="rounded-sm bg-slate-50 px-3 py-2 text-sm text-slate-800">
                                    {{ s.subject_name || '' }}
                                </div>
                                <div v-if="!compulsorySubjects.length" class="text-sm text-slate-500">No compulsory subjects</div>
                            </div>
                        </div>

                        <div class="lg:col-span-4 rounded-xl border border-slate-300 p-4">
                            <div class="text-xs font-semibold text-slate-600">4th Subject</div>
                            <div class="mt-3 space-y-2">
                                <label v-for="s in fourthSubjects" :key="'f-' + s.id" class="flex cursor-pointer items-center gap-2 rounded-sm border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50">
                                    <input type="radio" name="fourth" :value="s.subject_id" v-model.number="selectedFourthId" />
                                    <span class="text-slate-800">{{ s.subject_name || '' }}</span>
                                </label>
                                <div v-if="!fourthSubjects.length" class="text-sm text-slate-500">No 4th subject available</div>
                            </div>
                        </div>

                        <div class="lg:col-span-4 rounded-xl border border-slate-300 p-4">
                            <div class="flex items-center justify-between">
                                <div class="text-xs font-semibold text-slate-600">Main Subjects</div>
                                <div class="text-xs text-slate-500">Max: {{ mainSubjectLimit }}</div>
                            </div>
                            <div class="mt-3 space-y-2">
                                <label
                                    v-for="s in mainSubjects"
                                    :key="'m-' + s.id"
                                    class="flex cursor-pointer items-center gap-2 rounded-sm border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50"
                                    :class="isMainDisabled(s) ? 'opacity-50' : ''"
                                >
                                    <input
                                        type="checkbox"
                                        :value="s.subject_id"
                                        v-model="selectedMainIds"
                                        :disabled="isMainDisabled(s)"
                                    />
                                    <span class="text-slate-800">{{ s.subject_name || '' }}</span>
                                </label>
                                <div v-if="!mainSubjects.length" class="text-sm text-slate-500">No main subjects available</div>
                            </div>
                        </div>

                        <div v-if="subjectNote" class="lg:col-span-12 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                            {{ subjectNote }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="approvedOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4" @click.self="approvedOpen = false">
            <div class="w-full max-w-md  bg-white shadow-xl">
                <div class="border-b border-slate-300 px-5 py-4">
                    <div class="text-sm font-semibold text-slate-900">Approved Successfully</div>
                </div>
                <div class="p-5">
                    <div class="text-sm text-slate-700">Password: <span class="font-semibold">{{ approvedPassword }}</span></div>
                    <div class="mt-4 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                            @click="approvedOpen = false"
                        >
                            Close
                        </button>
                        <button
                            type="button"
                            class="rounded-sm bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800"
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
    name: 'OnlineAdmissionIndex',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            loading: false,
            downloading: false,
            searchTimer: null,
            message: '',
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
                status: '',
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
                gender: '',
                field_name: 'mobile',
                value: '',
                pagination: 10,
                page: 1,
            },
            selectedIds: [],
            approvingId: null,
            rejectingId: null,
            viewOpen: false,
            viewLoading: false,
            viewError: '',
            viewItem: null,
            subjectOpen: false,
            subjectLoading: false,
            subjectSaving: false,
            subjectError: '',
            subjectApplicantId: null,
            subjectAssign: null,
            selectedFourthId: null,
            selectedMainIds: [],
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
        qualifications() {
            const list = this.systems?.global?.academic_qualifications
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
        classes() {
            const list = this.systems?.global?.academic_classes
            return Array.isArray(list) ? list : []
        },
        filteredDepartments() {
            const qid = this.filters?.academic_qualification_id
            if (!qid) return []
            const allowed = new Set(this.departmentQualidactions.filter((r) => String(r?.academic_qualification_id) === String(qid)).map((r) => String(r?.department_id)))
            return this.departments.filter((d) => allowed.has(String(d?.id)))
        },
        filteredClasses() {
            const qid = this.filters?.academic_qualification_id
            if (!qid) return []
            return this.classes.filter((c) => String(c?.academic_qualification_id) === String(qid))
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
        allSelected() {
            if (!this.rows.length) return false
            const set = new Set(this.selectedIds.map((v) => Number(v)))
            return this.rows.every((r) => set.has(Number(r.id)))
        },
        compulsorySubjects() {
            const list = Array.isArray(this.subjectAssign?.details) ? this.subjectAssign.details : []
            return list.filter((d) => Number(d?.fourth_subject || 0) === 0 && Number(d?.main_subject || 0) === 0)
        },
        fourthSubjects() {
            const list = Array.isArray(this.subjectAssign?.details) ? this.subjectAssign.details : []
            return list.filter((d) => Number(d?.fourth_subject || 0) === 1)
        },
        mainSubjects() {
            const list = Array.isArray(this.subjectAssign?.details) ? this.subjectAssign.details : []
            return list.filter((d) => Number(d?.main_subject || 0) === 1)
        },
        mainSubjectLimit() {
            const n = Number(this.subjectAssign?.main_subject || 0)
            return Number.isFinite(n) && n > 0 ? n : 0
        },
        subjectNote() {
            return String(this.subjectAssign?.note || '').trim()
        },
    },
    watch: {
        'filters.status'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch(true)
        },
        'filters.academic_session_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch(true)
        },
        'filters.academic_qualification_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.filters.department_id = ''
                this.filters.academic_class_id = ''
                this.scheduleSearch(true)
            }
        },
        'filters.department_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch(true)
        },
        'filters.academic_class_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch(true)
        },
        'filters.gender'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch(true)
        },
        'filters.field_name'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch(true)
        },
        'filters.value'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch(true)
        },
        'filters.pagination'(next, prev) {
            if (Number(next || 0) !== Number(prev || 0)) this.scheduleSearch(true)
        },
        selectedMainIds() {
            const limit = this.mainSubjectLimit
            if (!limit) return
            if ((this.selectedMainIds || []).length <= limit) return
            this.selectedMainIds = (this.selectedMainIds || []).slice(0, limit)
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
        badgeClass(status) {
            const s = String(status || 'pending')
            if (s === 'approved') return 'border border-emerald-200 bg-emerald-50 text-emerald-700'
            if (s === 'rejected') return 'border border-red-200 bg-red-50 text-red-700'
            return 'border border-amber-200 bg-amber-50 text-amber-800'
        },
        toggleAll(e) {
            const checked = !!e?.target?.checked
            if (!checked) {
                const current = new Set(this.selectedIds.map((v) => Number(v)))
                for (const r of this.rows) current.delete(Number(r.id))
                this.selectedIds = Array.from(current)
                return
            }
            const set = new Set(this.selectedIds.map((v) => Number(v)))
            for (const r of this.rows) set.add(Number(r.id))
            this.selectedIds = Array.from(set)
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
                const res = await window.axios.get('/admin/onlineAdmission/list', { params: this.filters })
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
        async reject(id) {
            const ok = window.confirm('Reject this applicant?')
            if (!ok) return

            this.rejectingId = id
            this.error = ''
            this.message = ''
            try {
                await window.axios.delete(`/admin/onlineAdmission/${id}`)
                await this.search(false)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to reject.'
            } finally {
                this.rejectingId = null
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
        async openSubjectChoice(applicantId) {
            this.subjectOpen = true
            this.subjectLoading = true
            this.subjectSaving = false
            this.subjectError = ''
            this.subjectApplicantId = applicantId
            this.subjectAssign = null
            this.selectedFourthId = null
            this.selectedMainIds = []

            try {
                const res = await window.axios.get(`/admin/get-applicant-subjects/${applicantId}`)
                this.subjectAssign = res?.data?.subjectAssign || null

                const current = Array.isArray(res?.data?.assign_subjects) ? res.data.assign_subjects : []
                const fourth = current.find((x) => Number(x?.main_subject || 0) === 0)
                this.selectedFourthId = fourth ? Number(fourth.subject_id) : null
                this.selectedMainIds = current.filter((x) => Number(x?.main_subject || 0) === 1).map((x) => Number(x.subject_id))
            } catch (e) {
                this.subjectError = e?.response?.data?.message || 'Failed to load subject data.'
            } finally {
                this.subjectLoading = false
            }
        },
        closeSubjectChoice() {
            this.subjectOpen = false
            this.subjectError = ''
            this.subjectApplicantId = null
            this.subjectAssign = null
            this.selectedFourthId = null
            this.selectedMainIds = []
        },
        isMainDisabled(s) {
            const limit = this.mainSubjectLimit
            const chosen = Array.isArray(this.selectedMainIds) ? this.selectedMainIds : []
            const sid = Number(s?.subject_id || 0)

            const fourth = this.fourthSubjects.find((f) => Number(f?.subject_id || 0) === Number(this.selectedFourthId || 0))
            if (fourth && Number(fourth?.except_subject_id || 0) && Number(fourth.except_subject_id) === sid) {
                return true
            }

            if (!limit) return false
            if (chosen.includes(sid)) return false
            return chosen.length >= limit
        },
        async saveSubjectChoice() {
            if (!this.subjectApplicantId) return

            const payload = []
            if (this.selectedFourthId) {
                payload.push({ subject_id: Number(this.selectedFourthId), main_subject: 0 })
            }
            for (const sid of this.selectedMainIds || []) {
                payload.push({ subject_id: Number(sid), main_subject: 1 })
            }

            this.subjectSaving = true
            this.subjectError = ''
            try {
                const res = await window.axios.post('/admin/applicant-subject-assign', {
                    student_id: this.subjectApplicantId,
                    data: payload,
                })
                this.message = res?.data?.message || 'Subject Assign Successfully'
                this.closeSubjectChoice()
                await this.search(false)
            } catch (e) {
                this.subjectError = e?.response?.data?.message || 'Failed to save.'
            } finally {
                this.subjectSaving = false
            }
        },
        async downloadForms() {
            if (!this.selectedIds.length) return
            this.downloading = true
            this.error = ''
            try {
                const ids = this.selectedIds.map((v) => Number(v)).filter(Boolean)
                const url = `/admin/download-online-admission-form?applications_id=${encodeURIComponent(JSON.stringify(ids))}`
                window.open(url, '_blank')
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to download.'
            } finally {
                this.downloading = false
            }
        },
        rowSerial(idx) {
            const from = Number(this.meta.from || 0)
            return from + idx
        },
    },
}
</script>
