<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Send SMS</div>
                    <div class="mt-1 text-xs text-slate-500">Balance: {{ smsBalanceText }}</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="sending" @click="goIndex">Back</button>
                    <button type="button" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="sending || !canSend" @click="submit">{{ sending ? 'Sending...' : 'Send' }}</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>
        <div v-if="success" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ success }}</div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">Academic Info</div>

                    <div class="mt-4 grid grid-cols-1 gap-3">
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Sending Type</div>
                            <select v-model="form.sending_type" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none">
                                <option value="all">All</option>
                                <option value="students">College Students</option>
                                <option value="applicants">Applicant Students</option>
                                <option value="any">Any Number</option>
                            </select>
                        </div>

                        <template v-if="form.sending_type !== 'any'">
                            <div>
                                <div class="text-xs font-semibold text-slate-600">Select Session</div>
                                <select v-model="form.academic_session_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none">
                                    <option value="">Select</option>
                                    <option v-for="s in sessions" :key="'s-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                                </select>
                            </div>

                            <div>
                                <div class="text-xs font-semibold text-slate-600">Academic Level</div>
                                <select v-model="form.academic_qualification_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none" @change="onQualChange">
                                    <option value="">Select</option>
                                    <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                                </select>
                            </div>

                            <div>
                                <div class="text-xs font-semibold text-slate-600">Department/Group</div>
                                <select v-model="form.department_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none">
                                    <option value="">Select</option>
                                    <option v-for="d in departmentsFiltered" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                                </select>
                            </div>

                            <div>
                                <div class="text-xs font-semibold text-slate-600">Class</div>
                                <select v-model="form.academic_class_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none">
                                    <option value="">Select</option>
                                    <option v-for="c in classesFiltered" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                                </select>
                            </div>

                            <button type="button" class="mt-2 h-10 rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800" :disabled="loadingStudents" @click="getStudents">
                                {{ loadingStudents ? 'Searching...' : 'Search Students' }}
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">SMS Info</div>

                    <div class="mt-4 grid grid-cols-1 gap-3">
                        <div>
                            <div class="text-xs font-semibold text-slate-600">SMS Template</div>
                            <select v-model="form.sms_template_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none">
                                <option value="">Select</option>
                                <option v-for="t in templates" :key="'t-' + t.id" :value="String(t.id)">{{ t.name }}</option>
                            </select>
                        </div>

                        <div v-if="form.sending_type === 'students' || form.sending_type === 'applicants'">
                            <div class="text-xs font-semibold text-slate-600">Student (optional)</div>
                            <select v-model="form.student_ids" multiple class="mt-1 h-40 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none">
                                <option v-for="s in studentLists" :key="'st-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                            </select>
                            <div class="mt-1 text-xs text-slate-500">If none selected, it will send to all found students.</div>
                        </div>

                        <div v-if="form.sending_type === 'any'">
                            <div class="text-xs font-semibold text-slate-600">Numbers</div>
                            <textarea v-model="form.any_numbers" rows="7" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none" placeholder="ex: 018xxxxxx1,018xxxxxx2,018xxxxxx3"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <button type="button" class="h-10 w-full rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50" :disabled="sending || !canSend" @click="submit">{{ sending ? 'Sending...' : 'Send' }}</button>
                    <button type="button" class="mt-2 h-10 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="sending" @click="goIndex">Cancel</button>
                    <div v-if="!canSend" class="mt-3 text-xs text-rose-700">Please recharge your SMS balance.</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'SmsHistoryManage',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            sending: false,
            loadingStudents: false,
            error: '',
            success: '',
            templates: [],
            students: [],
            form: {
                academic_class_id: '',
                academic_session_id: '',
                department_id: '',
                academic_qualification_id: '',
                sending_type: 'all',
                sms_template_id: '',
                student_ids: [],
                any_numbers: '',
            },
        }
    },
    computed: {
        sessions() {
            return Array.isArray(this.systems?.global?.academic_sessions) ? this.systems.global.academic_sessions : []
        },
        qualifications() {
            return Array.isArray(this.systems?.global?.academic_qualifications) ? this.systems.global.academic_qualifications : []
        },
        departments() {
            return Array.isArray(this.systems?.global?.departments) ? this.systems.global.departments : []
        },
        departmentQualidactions() {
            return Array.isArray(this.systems?.global?.department_qualidactions) ? this.systems.global.department_qualidactions : []
        },
        classes() {
            return Array.isArray(this.systems?.global?.academic_classes) ? this.systems.global.academic_classes : []
        },
        departmentsFiltered() {
            const qid = String(this.form.academic_qualification_id || '')
            if (!qid) return this.departments
            const allowed = new Set(
                this.departmentQualidactions
                    .filter((m) => String(m.academic_qualification_id || '') === qid)
                    .map((m) => String(m.department_id || ''))
            )
            return this.departments.filter((d) => allowed.has(String(d.id)))
        },
        classesFiltered() {
            const qid = String(this.form.academic_qualification_id || '')
            if (!qid) return this.classes
            return this.classes.filter((c) => String(c.academic_qualification_id || '') === qid)
        },
        smsBalance() {
            return Number(this.systems?.site?.sms_balance || 0)
        },
        smsBalanceText() {
            return this.smsBalance.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' Tk.'
        },
        canSend() {
            return this.smsBalance > 0
        },
        studentLists() {
            const list = []
            for (const el of this.students || []) {
                const roll = this.form.sending_type === 'applicants' ? el.admission_roll : el.college_roll
                const prefix = roll ? `(${roll}) - ` : ''
                list.push({
                    id: String(el.id),
                    name: `${prefix}${String(el.name || '').slice(0, 20)} - (${el.mobile || ''})`,
                })
            }
            return list
        },
    },
    watch: {
        'form.sending_type'(type) {
            this.students = []
            this.form.student_ids = []
            if (type === 'all') {
                this.form.any_numbers = ''
            }
            if (type === 'any') {
                this.form.academic_session_id = ''
                this.form.academic_qualification_id = ''
                this.form.department_id = ''
                this.form.academic_class_id = ''
            }
        },
    },
    async created() {
        await this.loadTemplates()
    },
    methods: {
        toast(message, type = 'success') {
            window.dispatchEvent(new CustomEvent('app-toast', { detail: { message, type } }))
        },
        go(path) {
            window.history.pushState({}, '', path)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        goIndex() {
            this.go('/admin/smsHistory')
        },
        onQualChange() {
            this.form.department_id = ''
            this.form.academic_class_id = ''
        },
        async loadTemplates() {
            try {
                const res = await window.axios.get('/admin/smsTemplate', { params: { allData: true } })
                this.templates = Array.isArray(res?.data) ? res.data : []
            } catch {
                this.templates = []
            }
        },
        async getStudents() {
            this.loadingStudents = true
            this.error = ''
            try {
                const status = this.form.sending_type === 'applicants' ? 'approved' : 'active'
                const res = await window.axios.get('/admin/smsHistory/students', {
                    params: {
                        sending_type: this.form.sending_type,
                        academic_session_id: this.form.academic_session_id,
                        academic_qualification_id: this.form.academic_qualification_id,
                        department_id: this.form.department_id,
                        academic_class_id: this.form.academic_class_id,
                        status,
                    },
                })
                this.students = Array.isArray(res?.data) ? res.data : []
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load students.'
            } finally {
                this.loadingStudents = false
            }
        },
        async submit() {
            this.sending = true
            this.error = ''
            this.success = ''
            try {
                if (!this.form.sms_template_id) {
                    this.error = 'SMS Template is required.'
                    this.toast(this.error, 'error')
                    return
                }

                if (this.form.sending_type === 'any') {
                    if (!this.form.any_numbers) {
                        this.error = 'Numbers is required.'
                        this.toast(this.error, 'error')
                        return
                    }
                } else {
                    if (!this.form.academic_session_id || !this.form.academic_qualification_id || !this.form.department_id || !this.form.academic_class_id) {
                        this.error = 'Please select session, academic level, department and class.'
                        this.toast(this.error, 'error')
                        return
                    }
                }

                const payload = {
                    sending_type: this.form.sending_type,
                    sms_template_id: Number(this.form.sms_template_id),
                    academic_session_id: this.form.academic_session_id ? Number(this.form.academic_session_id) : null,
                    academic_qualification_id: this.form.academic_qualification_id ? Number(this.form.academic_qualification_id) : null,
                    department_id: this.form.department_id ? Number(this.form.department_id) : null,
                    academic_class_id: this.form.academic_class_id ? Number(this.form.academic_class_id) : null,
                    any_numbers: this.form.any_numbers,
                    student_ids: Array.isArray(this.form.student_ids) ? this.form.student_ids.map((x) => Number(x)) : [],
                }

                if (!confirm('Are you sure want to send sms?')) {
                    return
                }

                const res = await window.axios.post('/admin/smsHistory', payload)
                if (res?.data?.error) {
                    this.error = res.data.error
                    this.toast(this.error, 'error')
                    return
                }

                const msg = res?.data?.message || 'Send Successfully!'
                this.success = msg
                this.toast(msg, 'success')
                this.goIndex()
            } catch (e) {
                this.error = e?.response?.data?.message || e?.response?.data?.exception || 'Failed to send SMS.'
                this.toast(this.error, 'error')
            } finally {
                this.sending = false
            }
        },
    },
}
</script>
