<template>
    <form class="flex flex-col gap-4" @submit.prevent="submit">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">{{ isEdit ? 'Edit Attendance Summary' : 'Attendance Summary' }}</div>
                    <div class="mt-1 text-sm text-slate-600">Generate summary from attendance report</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="submitting" @click="goBack">
                        Back
                    </button>
                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="submitting || !students.length">
                        {{ submitting ? 'Processing...' : 'Save' }}
                    </button>
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
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">From Date <span class="text-rose-600">*</span></div>
                    <input v-model="form.from_date" type="date" required class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">To Date <span class="text-rose-600">*</span></div>
                    <input v-model="form.to_date" type="date" required class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Session <span class="text-rose-600">*</span></div>
                    <select v-model="form.academic_session_id" required class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="s in sessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Academic Level <span class="text-rose-600">*</span></div>
                    <select v-model="form.academic_qualification_id" required class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Department <span class="text-rose-600">*</span></div>
                    <select v-model="form.department_id" required class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="d in filteredDepartments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Class <span class="text-rose-600">*</span></div>
                    <select v-model="form.academic_class_id" required class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="c in filteredClasses" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Present Percent (%) <span class="text-rose-600">*</span></div>
                    <input v-model.number="form.present_percent" type="number" min="0" max="100" step="0.01" required class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-center outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Total Class <span class="text-rose-600">*</span></div>
                    <input v-model.number="form.total_class" type="number" min="0" step="1" required class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-center outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-6">
                    <div class="text-xs font-semibold text-slate-600">Admit Card <span class="text-rose-600">*</span></div>
                    <select v-model="form.admit_card_id" required class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="ac in admitCardsFiltered" :key="'ac-' + ac.id" :value="String(ac.id)">{{ ac.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-6 flex items-end justify-end">
                    <button type="button" class="h-10 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white hover:bg-slate-800" :disabled="loadingStudents" @click="loadStudents">
                        {{ loadingStudents ? 'Loading...' : 'Search Students' }}
                    </button>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-5 py-4">
                <div class="text-sm font-semibold text-slate-900">Students Attendance Summary</div>
            </div>

            <div class="p-5">
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 text-center font-semibold text-slate-700">#</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Software ID</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Student Name</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Mobile</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Admission ID</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">College Roll</th>
                                <th class="px-3 py-2 text-center font-semibold text-slate-700">Total Class</th>
                                <th class="px-3 py-2 text-center font-semibold text-slate-700">Total Present</th>
                                <th class="px-3 py-2 text-center font-semibold text-slate-700">Total Absent</th>
                                <th class="px-3 py-2 text-center font-semibold text-slate-700">Present %</th>
                                <th class="px-3 py-2 text-center font-semibold text-slate-700">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            <tr v-for="(s, idx) in students" :key="'std-' + String(s.student_id) + '-' + idx">
                                <td class="px-3 py-2 text-center text-slate-600">{{ idx + 1 }}</td>
                                <td class="px-3 py-2 text-slate-800">{{ s.student_id }}</td>
                                <td class="px-3 py-2 text-slate-800">{{ s.name }}</td>
                                <td class="px-3 py-2 text-slate-800">{{ s.mobile }}</td>
                                <td class="px-3 py-2 text-slate-800">{{ s.admission_id }}</td>
                                <td class="px-3 py-2 text-slate-800">{{ s.college_roll }}</td>
                                <td class="px-3 py-2 text-center text-slate-800"><b>{{ form.total_class }}</b></td>
                                <td class="px-3 py-2 text-center text-emerald-700"><b>{{ s.total_present }}</b></td>
                                <td class="px-3 py-2 text-center text-rose-700"><b>{{ s.total_absent }}</b></td>
                                <td class="px-3 py-2 text-center text-amber-700"><b>{{ s.present_percentage }}%</b></td>
                                <td class="px-3 py-2 text-center">
                                    <div class="inline-flex overflow-hidden rounded-lg border border-slate-200">
                                        <button type="button" class="px-3 py-1 text-xs font-semibold" :class="s.status === 'A' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 hover:bg-slate-50'" @click="setStatus(idx, 'A')">A</button>
                                        <button type="button" class="px-3 py-1 text-xs font-semibold" :class="s.status === 'P' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-700 hover:bg-slate-50'" @click="setStatus(idx, 'P')">P</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!students.length">
                                <td colspan="11" class="px-3 py-10 text-center text-slate-500">No Students Found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
export default {
    name: 'AttendanceSummaryCreate',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        summaryId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        const today = new Date().toISOString().slice(0, 10)
        return {
            loading: false,
            loadingStudents: false,
            submitting: false,
            error: '',
            message: '',
            form: {
                id: null,
                from_date: today,
                to_date: today,
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
                admit_card_id: '',
                present_percent: 100,
                total_class: 1,
            },
            students: [],
            admitCards: [],
        }
    },
    computed: {
        isEdit() {
            const id = this.summaryId
            return id !== null && id !== undefined && String(id) !== ''
        },
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
            const qid = this.form?.academic_qualification_id
            if (!qid) return this.departments
            const allowed = new Set(this.departmentQualidactions.filter((r) => String(r.academic_qualification_id) === String(qid)).map((r) => String(r.department_id)))
            return this.departments.filter((d) => allowed.has(String(d.id)))
        },
        filteredClasses() {
            const qid = this.form?.academic_qualification_id
            if (!qid) return this.classes
            return this.classes.filter((c) => String(c.academic_qualification_id) === String(qid))
        },

        admitCardsFiltered() {
            const ses = this.form?.academic_session_id
            const qid = this.form?.academic_qualification_id
            const dept = this.form?.department_id
            const cls = this.form?.academic_class_id

            return (Array.isArray(this.admitCards) ? this.admitCards : []).filter((e) => {
                return (
                    String(e.academic_session_id || '') === String(ses || '') &&
                    String(e.academic_qualification_id || '') === String(qid || '') &&
                    String(e.department_id || '') === String(dept || '') &&
                    String(e.academic_class_id || '') === String(cls || '')
                )
            })
        },
    },
    watch: {
        'form.academic_qualification_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.form.department_id = ''
                this.form.academic_class_id = ''
                this.form.admit_card_id = ''
            }
        },
        'form.academic_session_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.form.admit_card_id = ''
            }
        },
        'form.department_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.form.admit_card_id = ''
            }
        },
        'form.academic_class_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.form.admit_card_id = ''
            }
        },
    },
    async mounted() {
        await this.loadAdmitCards()
        if (this.isEdit) {
            await this.loadForEdit()
        }
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
        goBack() {
            window.location.href = '/admin/attendanceSummary'
        },
        setStatus(index, status) {
            const s = status === 'A' ? 'A' : 'P'
            const next = [...this.students]
            next[index] = { ...next[index], status: s }
            this.students = next
        },
        normalizeStudents(list) {
            const arr = Array.isArray(list) ? list : []
            return arr.map((s) => ({
                student_id: s.student_id,
                name: s?.student?.name ?? s.name,
                mobile: s?.student?.mobile ?? s.mobile,
                admission_id: s?.student?.admission_id ?? s.admission_id,
                college_roll: s?.student?.college_roll ?? s.college_roll,
                total_present: Number(s.total_present || 0),
                total_absent: Number(s.total_absent || 0),
                present_percentage: String(s.present_percentage ?? '0.00'),
                status: s.status === 'P' ? 'P' : 'A',
            }))
        },
        buildDetailsPayload() {
            return this.students.map((s) => ({
                student_id: s.student_id,
                total_present: s.total_present,
                total_absent: s.total_absent,
                present_percentage: parseFloat(String(s.present_percentage || 0)),
                status: s.status === 'P' ? 'P' : 'A',
            }))
        },
        async loadAdmitCards() {
            try {
                const res = await window.axios.get('/admin/admitCard/list', { params: { allData: true } })
                this.admitCards = Array.isArray(res?.data) ? res.data : []
            } catch (e) {
                this.admitCards = []
            }
        },

        async loadForEdit() {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/attendanceSummary/${this.summaryId}/details`)
                const summary = res?.data?.summary || null
                const details = Array.isArray(res?.data?.details) ? res.data.details : []
                if (!summary) return

                this.form.id = summary.id
                this.form.from_date = summary.from_date || this.form.from_date
                this.form.to_date = summary.to_date || this.form.to_date
                this.form.academic_session_id = summary.academic_session_id ? String(summary.academic_session_id) : ''
                this.form.academic_qualification_id = summary.academic_qualification_id ? String(summary.academic_qualification_id) : ''
                this.form.department_id = summary.department_id ? String(summary.department_id) : ''
                this.form.academic_class_id = summary.academic_class_id ? String(summary.academic_class_id) : ''
                this.form.admit_card_id = summary.admit_card_id ? String(summary.admit_card_id) : ''
                this.form.present_percent = Number(summary.present_percent ?? 100)
                this.form.total_class = Number(summary.total_class ?? 1)

                this.students = this.normalizeStudents(details)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load summary.'
            } finally {
                this.loading = false
            }
        },
        // ...
        async loadStudents() {
            this.loadingStudents = true
            this.error = ''
            this.message = ''

            try {
                const params = {
                    from_date: this.form.from_date,
                    to_date: this.form.to_date,
                    academic_session_id: this.form.academic_session_id,
                    academic_qualification_id: this.form.academic_qualification_id,
                    department_id: this.form.department_id,
                    academic_class_id: this.form.academic_class_id,
                    admit_card_id: this.form.admit_card_id,
                    present_percent: this.form.present_percent,
                    total_class: this.form.total_class,
                }

                const res = await window.axios.get('/admin/attendanceSummary/students', { params })

                const list = Array.isArray(res?.data?.students) ? res.data.students : []
                this.students = this.normalizeStudents(list)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load students.'
                this.students = []
            } finally {
                this.loadingStudents = false
            }
        },
        // ...
        async submit() {
            if (!this.students.length) {
                this.error = 'No students found.'
                return
            }

            this.submitting = true
            this.error = ''
            this.message = ''

            const payload = {
                from_date: this.form.from_date,
                to_date: this.form.to_date,
                academic_session_id: this.form.academic_session_id,
                academic_qualification_id: this.form.academic_qualification_id,
                department_id: this.form.department_id,
                academic_class_id: this.form.academic_class_id,
                admit_card_id: this.form.admit_card_id,
                present_percent: this.form.present_percent,
                total_class: this.form.total_class,
                details: this.students.map((s) => ({
                    student_id: s.student_id,
                    total_present: s.total_present,
                    total_absent: s.total_absent,
                    present_percentage: s.present_percentage,
                    status: s.status,
                })),
            }

            try {
                if (this.isEdit) {
                    const res = await window.axios.put(`/admin/attendanceSummary/${this.summaryId}`, payload)
                    const msg = res?.data?.message || res?.data?.error || 'Update Successfully!'
                    this.message = msg
                } else {
                    const res = await window.axios.post('/admin/attendanceSummary', payload)
                    const msg = res?.data?.message || res?.data?.error || 'Create Successfully!'
                    this.message = msg
                }

                setTimeout(() => {
                    window.location.href = '/admin/attendanceSummary'
                }, 300)
            } catch (e) {
                const msg = e?.response?.data?.message || e?.response?.data?.error
                this.error = msg || 'Failed to save summary.'
            } finally {
                this.submitting = false
            }
        },
    },
}
</script>
