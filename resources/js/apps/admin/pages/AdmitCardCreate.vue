<template>
    <form class="flex flex-col gap-4" @submit.prevent="submit">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">{{ isEdit ? 'Edit Admit Card' : 'Admit Card' }}</div>
                    <div class="mt-1 text-sm text-slate-600">Create or update admit card</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="submitting" @click="goBack">
                        Back
                    </button>
                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="submitting">
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
                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Session <span class="text-rose-600">*</span></div>
                    <select v-model="form.academic_session_id" required class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="s in sessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Academic Level <span class="text-rose-600">*</span></div>
                    <select v-model="form.academic_qualification_id" required class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Department/Group</div>
                    <select v-model="form.department_id" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="d in filteredDepartments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Class <span class="text-rose-600">*</span></div>
                    <select v-model="form.academic_class_id" required class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="c in filteredClasses" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-4">
                    <div class="text-xs font-semibold text-slate-600">Exam <span class="text-rose-600">*</span></div>
                    <select v-model="form.exam_id" required class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="e in exams" :key="'ex-' + e.id" :value="String(e.id)">{{ e.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-4">
                    <div class="text-xs font-semibold text-slate-600">Admit Card Name <span class="text-rose-600">*</span></div>
                    <input v-model="form.name" type="text" required class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Issue Date <span class="text-rose-600">*</span></div>
                    <input v-model="form.issue_date" type="date" required class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Expired Date <span class="text-rose-600">*</span></div>
                    <input v-model="form.expired_date" type="date" required class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>
            </div>
        </div>
    </form>
</template>

<script>
export default {
    name: 'AdmitCardCreate',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        admitCardId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        const today = new Date().toISOString().slice(0, 10)
        return {
            submitting: false,
            error: '',
            message: '',
            form: {
                id: null,
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
                exam_id: '',
                name: '',
                issue_date: today,
                expired_date: today,
            },
        }
    },
    computed: {
        isEdit() {
            const id = this.admitCardId
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
        exams() {
            const list = this.systems?.global?.exams
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
            await this.load()
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
            window.location.href = '/admin/admitCard'
        },
        async load() {
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/admitCard/${this.admitCardId}/details`)
                const ac = res?.data?.admit_card || null
                if (!ac) return

                this.form.id = ac.id
                this.form.academic_session_id = ac.academic_session_id ? String(ac.academic_session_id) : ''
                this.form.academic_qualification_id = ac.academic_qualification_id ? String(ac.academic_qualification_id) : ''
                this.form.department_id = ac.department_id ? String(ac.department_id) : ''
                this.form.academic_class_id = ac.academic_class_id ? String(ac.academic_class_id) : ''
                this.form.exam_id = ac.exam_id ? String(ac.exam_id) : ''
                this.form.name = ac.name || ''
                this.form.issue_date = ac.issue_date || this.form.issue_date
                this.form.expired_date = ac.expired_date || this.form.expired_date
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load admit card.'
            }
        },
        async submit() {
            this.submitting = true
            this.error = ''
            this.message = ''

            const payload = {
                academic_session_id: this.form.academic_session_id,
                academic_qualification_id: this.form.academic_qualification_id,
                department_id: this.form.department_id || null,
                academic_class_id: this.form.academic_class_id,
                exam_id: this.form.exam_id,
                name: this.form.name,
                issue_date: this.form.issue_date,
                expired_date: this.form.expired_date,
            }

            try {
                if (this.isEdit) {
                    const res = await window.axios.put(`/admin/admitCard/${this.admitCardId}`, payload)
                    this.message = res?.data?.message || res?.data?.error || 'Update Successfully!'
                } else {
                    const res = await window.axios.post('/admin/admitCard', payload)
                    this.message = res?.data?.message || res?.data?.error || 'Create Successfully!'
                }

                setTimeout(() => {
                    window.location.href = '/admin/admitCard'
                }, 300)
            } catch (e) {
                const msg = e?.response?.data?.message || e?.response?.data?.error
                this.error = msg || 'Failed to save admit card.'
            } finally {
                this.submitting = false
            }
        },
    },
}
</script>
