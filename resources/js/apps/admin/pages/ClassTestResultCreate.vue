<template>
    <form class="flex flex-col gap-4" @submit.prevent="submit">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Class Test Result Entry</div>
                    <div class="mt-1 text-sm text-slate-600">Create new class test result entry</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        :disabled="submitting"
                        @click="goBack"
                    >
                        Back
                    </button>
                    <button
                        type="submit"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                        :disabled="submitting"
                    >
                        {{ submitting ? 'Processing...' : 'Save & Next' }}
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
                    <select v-model="form.academic_session_id" required class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="s in sessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Academic Level <span class="text-rose-600">*</span></div>
                    <select v-model="form.academic_qualification_id" required class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Department <span class="text-rose-600">*</span></div>
                    <select v-model="form.department_id" required class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="d in filteredDepartments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Class <span class="text-rose-600">*</span></div>
                    <select v-model="form.academic_class_id" required class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="c in filteredClasses" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-4">
                    <div class="text-xs font-semibold text-slate-600">Exam <span class="text-rose-600">*</span></div>
                    <select v-model="form.exam_id" required class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="e in examsCt" :key="'ex-' + e.id" :value="String(e.id)">{{ e.name }}</option>
                    </select>
                    <div class="mt-1 text-xs text-slate-500">Only CT exams are shown</div>
                </div>

                <div class="lg:col-span-4">
                    <div class="text-xs font-semibold text-slate-600">Exam Date <span class="text-rose-600">*</span></div>
                    <input v-model="form.exam_date" type="date" required class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>
            </div>
        </div>
    </form>
</template>

<script>
export default {
    name: 'ClassTestResultCreate',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        const today = new Date().toISOString().slice(0, 10)
        return {
            submitting: false,
            error: '',
            message: '',
            form: {
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
                exam_id: '',
                exam_date: today,
            },
            examsList: [],
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
            const qid = this.form?.academic_qualification_id
            if (!qid) return this.departments
            const allowed = new Set(
                this.departmentQualidactions
                    .filter((r) => String(r.academic_qualification_id) === String(qid))
                    .map((r) => String(r.department_id)),
            )
            return this.departments.filter((d) => allowed.has(String(d.id)))
        },
        filteredClasses() {
            const qid = this.form?.academic_qualification_id
            if (!qid) return this.classes
            return this.classes.filter((c) => String(c.academic_qualification_id) === String(qid))
        },
        examsCt() {
            const list = Array.isArray(this.examsList) && this.examsList.length ? this.examsList : this.systems?.global?.exams
            const items = Array.isArray(list) ? list : []
            return items.filter((e) => {
                const t = String(e?.exam_type || '')
                if (!t) return true
                return t === 'ct'
            })
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
        await this.loadExamsCt()
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
            window.location.href = '/admin/classTestResult'
        },
        async loadExamsCt() {
            try {
                const res = await window.axios.get('/admin/get-exam', { params: { allData: true, exam_type: 'ct' } })
                this.examsList = Array.isArray(res?.data) ? res.data : []
            } catch (_e) {
                this.examsList = []
            }
        },
        async submit() {
            this.submitting = true
            this.error = ''
            this.message = ''

            const payload = {
                academic_session_id: this.form.academic_session_id,
                academic_qualification_id: this.form.academic_qualification_id,
                department_id: this.form.department_id,
                academic_class_id: this.form.academic_class_id,
                exam_id: this.form.exam_id,
                exam_date: this.form.exam_date,
            }

            try {
                const res = await window.axios.post('/admin/classTestResult', payload)
                const id = res?.data?.id
                this.message = res?.data?.message || 'Create Successfully!'
                this.$toast?.success?.(this.message)

                if (id) {
                    setTimeout(() => {
                        window.location.href = `/admin/classTestResult/${id}/edit`
                    }, 200)
                }
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to create class test result.'
                this.$toast?.error?.(this.error)
            } finally {
                this.submitting = false
            }
        },
    },
}
</script>
