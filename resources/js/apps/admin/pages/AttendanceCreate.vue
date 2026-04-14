<template>
    <form class="flex flex-col gap-4" @submit.prevent="submit">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">{{ isEdit ? 'Edit Attendance' : 'Attendance' }}</div>
                    <div class="mt-1 text-sm text-slate-600">Search students and mark present/absent</div>
                </div>
            </div>
        </div>

        <div v-if="message" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
            {{ message }}
        </div>

        <div v-if="error" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="flex flex-col gap-4 lg:col-span-8">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-900">Search Filters</div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Date <span class="text-rose-600">*</span></div>
                            <input
                                v-model="form.date"
                                type="date"
                                required
                                class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Session <span class="text-rose-600">*</span></div>
                            <select
                                v-model="form.academic_session_id"
                                required
                                class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            >
                                <option value="">Select</option>
                                <option v-for="s in sessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Academic Level <span class="text-rose-600">*</span></div>
                            <select
                                v-model="form.academic_qualification_id"
                                required
                                class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            >
                                <option value="">Select</option>
                                <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Department <span class="text-rose-600">*</span></div>
                            <select
                                v-model="form.department_id"
                                required
                                class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            >
                                <option value="">Select</option>
                                <option v-for="d in filteredDepartments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Class <span class="text-rose-600">*</span></div>
                            <select
                                v-model="form.academic_class_id"
                                required
                                class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            >
                                <option value="">Select</option>
                                <option v-for="c in filteredClasses" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2 lg:col-span-3 flex justify-end">
                            <button
                                type="button"
                                class="h-9 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white hover:bg-slate-800"
                                :disabled="loadingStudents"
                                @click="loadStudents"
                            >
                                {{ loadingStudents ? 'Loading...' : 'Search Students' }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-sm font-semibold text-slate-900">Students</div>
                        <div class="text-sm text-slate-700">
                            <span class="font-semibold">Total:</span> {{ totals.total }}
                            <span class="mx-2 text-slate-300">|</span>
                            <span class="font-semibold text-emerald-700">Present:</span> {{ totals.present }}
                            <span class="mx-2 text-slate-300">|</span>
                            <span class="font-semibold text-rose-700">Absent:</span> {{ totals.absent }}
                        </div>
                    </div>

                    <div class="p-5">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <thead class="bg-emerald-100">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold text-slate-700">#</th>
                                        <th class="px-3 py-2 text-left font-semibold text-slate-700">Profile</th>
                                        <th class="px-3 py-2 text-left font-semibold text-slate-700">Software ID</th>
                                        <th class="px-3 py-2 text-left font-semibold text-slate-700">Student</th>
                                        <th class="px-3 py-2 text-left font-semibold text-slate-700">Mobile</th>
                                        <th class="px-3 py-2 text-left font-semibold text-slate-700">Admission ID</th>
                                        <th class="px-3 py-2 text-left font-semibold text-slate-700">College Roll</th>
                                        <th class="px-3 py-2 text-center font-semibold text-slate-700">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 bg-white">
                                    <tr v-for="(row, idx) in students" :key="'std-' + String(row.student_id || row.id) + '-' + idx">
                                        <td class="px-3 py-2 text-slate-600">{{ idx + 1 }}</td>
                                        <td class="px-3 py-2">
                                            <div class="h-9 w-9 overflow-hidden rounded-lg border border-slate-300 bg-slate-50 shadow-sm">
                                                <img v-if="row.profile" :src="row.profile" class="h-9 w-9 object-cover" />
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 text-slate-800">{{ row.student_id || '--' }}</td>
                                        <td class="px-3 py-2 text-slate-800">{{ row.name || '--' }}</td>
                                        <td class="px-3 py-2 text-slate-700">{{ row.mobile || '--' }}</td>
                                        <td class="px-3 py-2 text-slate-700">{{ row.admission_id || '--' }}</td>
                                        <td class="px-3 py-2 text-slate-700">{{ row.college_roll || '--' }}</td>
                                        <td class="px-3 py-2 text-center">
                                            <div class="inline-flex overflow-hidden rounded-lg border border-slate-300 shadow-sm">
                                                <button
                                                    type="button"
                                                    class="px-3 py-1 text-xs font-semibold"
                                                    :class="row.status === 'A' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 hover:bg-slate-50'"
                                                    @click="setStatus(idx, 'A')"
                                                >
                                                    A
                                                </button>
                                                <button
                                                    type="button"
                                                    class="px-3 py-1 text-xs font-semibold"
                                                    :class="row.status === 'P' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-700 hover:bg-slate-50'"
                                                    @click="setStatus(idx, 'P')"
                                                >
                                                    P
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!students.length">
                                        <td colspan="8" class="px-3 py-10 text-center text-slate-500">No students found</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-4 lg:col-span-4">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-900">Status</div>
                    <div class="mt-2 text-sm text-slate-600">Search students using the filters, then mark each student as Present (P) or Absent (A) and save.</div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-900">Actions</div>

                    <div class="mt-4 flex flex-col gap-2">
                        <button
                            type="button"
                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                            :disabled="submitting"
                            @click="goBack"
                        >
                            Back
                        </button>
                        <button
                            type="submit"
                            class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                            :disabled="submitting || !students.length"
                        >
                            {{ submitting ? 'Processing...' : 'Save' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
export default {
    name: 'AttendanceCreate',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        attendanceId: {
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
                date: today,
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
            },
            students: [],
        }
    },
    computed: {
        isEdit() {
            const id = this.attendanceId
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
        totals() {
            const total = this.students.length
            const present = this.students.filter((s) => String(s.status || 'P') === 'P').length
            return { total, present, absent: total - present }
        },
    },
    watch: {
        'form.academic_qualification_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.form.department_id = ''
                this.form.academic_class_id = ''
            }
        },
    },
    async mounted() {
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
            window.location.href = '/admin/attendance'
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
                id: s.id ?? null,
                student_id: s.student_id,
                name: s.name,
                mobile: s.mobile,
                admission_id: s.admission_id,
                college_roll: s.college_roll,
                profile: s.profile,
                status: s.status || 'P',
            }))
        },
        buildDetailsPayload() {
            return this.students.map((s) => ({
                student_id: s.student_id,
                in_time: null,
                out_time: null,
                status: s.status === 'A' ? 'A' : 'P',
            }))
        },
        async loadForEdit() {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/attendance/${this.attendanceId}/details`)
                const attendance = res?.data?.attendance || null
                const details = Array.isArray(res?.data?.details) ? res.data.details : []

                if (attendance) {
                    this.form.id = attendance.id
                    this.form.date = attendance.date || this.form.date
                    this.form.academic_session_id = attendance.academic_session_id ? String(attendance.academic_session_id) : ''
                    this.form.academic_qualification_id = attendance.academic_qualification_id ? String(attendance.academic_qualification_id) : ''
                    this.form.department_id = attendance.department_id ? String(attendance.department_id) : ''
                    this.form.academic_class_id = attendance.academic_class_id ? String(attendance.academic_class_id) : ''
                }

                const rows = details
                    .map((d) => {
                        const std = d.student || {}
                        return {
                            student_id: d.student_id,
                            name: std.name,
                            mobile: std.mobile,
                            admission_id: std.admission_id,
                            college_roll: std.college_roll,
                            profile: std.profile,
                            status: d.status || 'A',
                        }
                    })
                    .sort((a, b) => Number(a.college_roll || 0) - Number(b.college_roll || 0))

                this.students = this.normalizeStudents(rows)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load attendance.'
            } finally {
                this.loading = false
            }
        },
        async loadStudents() {
            if (!this.form.academic_session_id || !this.form.academic_qualification_id || !this.form.department_id || !this.form.academic_class_id) {
                this.error = 'Please select Session, Academic Level, Department and Class.'
                return
            }

            this.loadingStudents = true
            this.error = ''
            this.message = ''
            try {
                const res = await window.axios.get('/admin/attendance/students', {
                    params: {
                        academic_session_id: this.form.academic_session_id,
                        academic_qualification_id: this.form.academic_qualification_id,
                        department_id: this.form.department_id,
                        academic_class_id: this.form.academic_class_id,
                    },
                })

                const list = Array.isArray(res?.data?.students) ? res.data.students : []
                const normalized = this.normalizeStudents(list)
                this.students = normalized.map((s) => ({ ...s, status: 'P' }))
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load students.'
                this.students = []
            } finally {
                this.loadingStudents = false
            }
        },
        async submit() {
            if (!this.students.length) {
                this.error = 'No students found.'
                return
            }

            this.submitting = true
            this.error = ''
            this.message = ''

            const payload = {
                date: this.form.date,
                academic_session_id: this.form.academic_session_id,
                academic_qualification_id: this.form.academic_qualification_id,
                department_id: this.form.department_id,
                academic_class_id: this.form.academic_class_id,
                details: this.buildDetailsPayload(),
            }

            try {
                if (this.isEdit) {
                    const res = await window.axios.put(`/admin/attendance/${this.attendanceId}`, payload)
                    const msg = res?.data?.message || res?.data?.error || 'Update Successfully!'
                    this.message = msg
                } else {
                    const res = await window.axios.post('/admin/attendance', payload)
                    const msg = res?.data?.message || res?.data?.error || 'Create Successfully!'
                    this.message = msg
                }

                setTimeout(() => {
                    window.location.href = '/admin/attendance'
                }, 300)
            } catch (e) {
                const msg = e?.response?.data?.message || e?.response?.data?.error
                this.error = msg || 'Failed to save attendance.'
            } finally {
                this.submitting = false
            }
        },
    },
}
</script>
