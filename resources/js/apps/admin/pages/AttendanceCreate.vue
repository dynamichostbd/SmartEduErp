<template>
    <form class="flex flex-col gap-4" @submit.prevent="submit">
        <div v-if="message" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
            {{ message }}
        </div>

        <div v-if="error" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div class="flex flex-col gap-4">
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-slate-50 px-4 py-3 text-sm font-bold uppercase text-slate-800">
                    Search Students For Attendance</div>

                <div class="p-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                    <div>
                        <div class="text-xs font-semibold text-slate-600">Date <span class="text-rose-600">*</span>
                        </div>
                        <input v-model="form.date" type="date" required
                            class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">Select Session <span
                                class="text-rose-600">*</span></div>
                        <select v-model="form.academic_session_id" required
                            class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                            <option value="">Select</option>
                            <option v-for="s in sessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">Academic Level <span
                                class="text-rose-600">*</span></div>
                        <select v-model="form.academic_qualification_id" required
                            class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                            <option value="">Select</option>
                            <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">Department/Group <span
                                class="text-rose-600">*</span></div>
                        <select v-model="form.department_id" required
                            class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                            <option value="">Select</option>
                            <option v-for="d in filteredDepartments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name
                                }}</option>
                        </select>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">Class <span class="text-rose-600">*</span>
                        </div>
                        <select v-model="form.academic_class_id" required
                            class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                            <option value="">Select</option>
                            <option v-for="c in filteredClasses" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}
                            </option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button type="button"
                            class="h-9 w-12 rounded-sm bg-emerald-600 text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm flex items-center justify-center"
                            :disabled="loadingStudents" @click="loadStudents">
                            <span v-if="loadingStudents"
                                class="h-4 w-4 rounded-full border-2 border-white/30 border-t-white animate-spin"></span>
                            <span v-else><i class="fa fa-search"></i></span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div
                    class="flex flex-col gap-2 border-b border-slate-100 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm font-bold uppercase text-slate-800">Students</div>
                    <div class="text-sm text-slate-800">
                        <span class="font-bold">Total Student : {{ totals.total }}</span>
                        <span class="mx-2 text-slate-300">|</span>
                        <span class="font-bold text-emerald-600">Present : {{ totals.present }}</span>
                        <span class="mx-2 text-slate-300">|</span>
                        <span class="font-bold text-rose-600">Absent : {{ totals.absent }}</span>
                    </div>
                </div>

                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table v-if="students.length"
                            class="min-w-full text-sm border-collapse border border-slate-200">
                            <thead class="bg-white">
                                <tr>
                                    <th class="border border-slate-200 px-3 py-2 text-center font-bold text-slate-800">#
                                    </th>
                                    <th class="border border-slate-200 px-3 py-2 text-center font-bold text-slate-800">
                                        Profile</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-bold text-slate-800">
                                        Software ID</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-bold text-slate-800">
                                        Student Name</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-bold text-slate-800">
                                        Mobile</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-bold text-slate-800">
                                        Admission ID</th>
                                    <th class="border border-slate-200 px-3 py-2 text-left font-bold text-slate-800">
                                        College Roll</th>
                                    <th class="border border-slate-200 px-3 py-2 text-center font-bold text-slate-800">
                                        Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                <tr v-for="(row, idx) in students"
                                    :key="'std-' + String(row.student_id || row.id) + '-' + idx"
                                    :class="(idx % 2 !== 0) ? 'bg-slate-50' : 'bg-white'"
                                    class="hover:bg-slate-100 transition-colors">
                                    <td class="border border-slate-200 px-3 py-2 text-center text-slate-700">{{ idx + 1
                                        }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-center p-0">
                                        <div
                                            class="mx-auto flex h-[37px] w-9 items-center justify-center overflow-hidden">
                                            <img v-if="row.profile" :src="row.profile"
                                                class="h-full w-full object-cover" />
                                        </div>
                                    </td>
                                    <td class="border border-slate-200 px-3 py-2 text-slate-700">{{ row.student_id ||
                                        '--' }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-slate-700">{{ row.name || '--' }}
                                    </td>
                                    <td class="border border-slate-200 px-3 py-2 text-slate-700">{{ row.mobile || '--'
                                        }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-slate-700">{{ row.admission_id ||
                                        '--' }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-slate-700">{{ row.college_roll ||
                                        '--' }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-center p-0">
                                        <div class="flex items-center justify-center h-[37px]">
                                            <button type="button" class="btn_choose_sent bg_btn_chose_1">
                                                <input type="radio" :name="'status' + idx" value="A"
                                                    v-model="row.status"
                                                    :class="row.status === 'A' ? 'absent-radio' : ''" />A
                                            </button>
                                            <button type="button" class="btn_choose_sent bg_btn_chose_2">
                                                <input type="radio" :name="'status' + idx" value="P"
                                                    v-model="row.status"
                                                    :class="row.status === 'P' ? 'present-radio' : ''" />P
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-else class="text-center my-6 text-slate-700 font-semibold">No Students Found</div>
                    </div>
                </div>
            </div>

            <div v-if="students.length" class="flex justify-center mt-4 mb-8">
                <button type="submit"
                    class="rounded-sm bg-emerald-600 px-8 py-2 text-sm font-bold text-white hover:bg-emerald-700 shadow-sm disabled:opacity-70 flex items-center"
                    :disabled="submitting">
                    <span v-if="submitting"
                        class="mr-2 h-4 w-4 rounded-full border-2 border-white/30 border-t-white animate-spin"></span>
                    <i v-else class="bx bx-save mr-1"></i> Save
                </button>
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
            if (this.loading) return
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
                await this.$nextTick()
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

<style scoped>
.btn_choose_sent input {
    -webkit-appearance: none;
    display: block;
    margin: 10px;
    width: 15px;
    height: 15px;
    border-radius: 12px;
    cursor: pointer;
    box-shadow: hsla(0, 0%, 100%, 0.15) 0 1px 1px,
        inset hsla(0, 0%, 0%, 0.5) 0 0 0 1px;
    background-color: hsla(0, 0%, 0%, 0.1);
    background-repeat: no-repeat;
    -webkit-transition: background-position 0.15s cubic-bezier(0.8, 0, 1, 1),
        -webkit-transform 0.25s cubic-bezier(0.8, 0, 1, 1);
    outline: none;
}

.present-radio {
    background-image: -webkit-radial-gradient(#2eff01 0%,
            #2eff01 15%,
            #2eff01 28%,
            #2eff01 70%);
}

.absent-radio {
    background-image: -webkit-radial-gradient(#fa6868 0%,
            #fa6868 15%,
            #fa6868 28%,
            #fa6868 70%);
}

.btn_choose_sent input:checked {
    -webkit-transition: background-position 0.2s 0.15s cubic-bezier(0, 0, 0.2, 1),
        -webkit-transform 0.25s cubic-bezier(0, 0, 0.2, 1);
}

.btn_choose_sent input:active {
    -webkit-transform: scale(1.5);
    -webkit-transition: -webkit-transform 0.1s cubic-bezier(0, 0, 0.2, 1);
}

/* The up/down direction logic */

.btn_choose_sent input,
.btn_choose_sent input:active {
    background-position: 0 24px;
}

.btn_choose_sent input:checked {
    background-position: 0 0;
}

.btn_choose_sent input:checked~input,
.btn_choose_sent input:checked~input:active {
    background-position: 0 -24px;
}

.btn_choose_sent {
    background: #ef2d56;
    color: #fff;
    box-shadow: 0 10px 20px rgb(125 147 178 / 30%);
    border: none;
    border-radius: 3px;
    font-size: 13px;
    font-weight: bold;
    padding: 3px 11px 3px 30px;
    text-align: center;
    display: inline-block;
    text-decoration: none;
    margin-right: 15px;
    transition: all 0.3s;
    height: auto;
    cursor: pointer;
    position: relative;
    outline: none;
}

.btn_choose_sent input {
    position: absolute;
    left: 0;
    right: 0;
    top: -5px;
}

.btn_choose_sent input:after {
    position: absolute;
    content: "";
    width: 15rem;
    left: 0;
    right: 0;
}

.bg_btn_chose_1 {
    background-color: #a5b5be !important;
}

.bg_btn_chose_2 {
    background-color: #21a700 !important;
}

.btn-disable {
    background: #cacaca;
}
</style>
