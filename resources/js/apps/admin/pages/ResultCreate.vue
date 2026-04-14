<template>
    <form class="flex flex-col gap-4" @submit.prevent="submit">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Result Entry</div>
                    <div class="mt-1 text-sm text-slate-600">Create new result</div>
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
                    <div class="text-sm font-semibold text-slate-900">Result Setup</div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
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

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Exam <span class="text-rose-600">*</span></div>
                            <select
                                v-model="form.exam_id"
                                required
                                class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            >
                                <option value="">Select</option>
                                <option v-for="e in examsTerm" :key="'ex-' + e.id" :value="String(e.id)">{{ e.name }}</option>
                            </select>
                            <div class="mt-1 text-xs text-slate-500">Only Term exams are shown</div>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Exam Subjects <span class="text-rose-600">*</span></div>
                            <input
                                v-model.number="form.total_exam_subjects"
                                type="number"
                                min="1"
                                required
                                class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Two Paper</div>
                            <select
                                v-model.number="form.child_subject_enabled"
                                class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            >
                                <option :value="0">Disable</option>
                                <option :value="1">Enable</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2 lg:col-span-3" v-if="form.child_subject_enabled === 0">
                            <div class="flex flex-wrap items-center gap-2">
                                <button
                                    type="button"
                                    class="h-9 rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800"
                                    @click="showSpecificSubject = !showSpecificSubject"
                                >
                                    {{ showSpecificSubject ? 'Disable Specific Subject' : 'Enable Specific Subject' }}
                                </button>
                                <div class="text-xs text-slate-500">Enable child-subject feature only for selected subjects</div>
                            </div>
                        </div>

                        <div class="sm:col-span-2 lg:col-span-3" v-if="form.child_subject_enabled === 0 && showSpecificSubject">
                            <div class="text-xs font-semibold text-slate-600">Enable Child For Subjects</div>
                            <select
                                v-model="form.child_subject_enabled_subject_ids"
                                multiple
                                class="mt-1 h-40 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            >
                                <option v-for="s in subjects" :key="'sub-' + s.subject_id" :value="Number(s.subject_id)">
                                    {{ s.subject_name }}
                                </option>
                            </select>
                            <div class="mt-1 text-xs text-slate-500">Hold Ctrl to select multiple</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-4 lg:col-span-4">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-900">Status</div>
                    <div class="mt-2 text-sm text-slate-600">Select the result scope and exam, then save to continue with result entry.</div>
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
                            :disabled="submitting"
                        >
                            {{ submitting ? 'Processing...' : 'Save & Next' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
export default {
    name: 'ResultCreate',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
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
                total_exam_subjects: 7,
                child_subject_enabled: 0,
                child_subject_enabled_subject_ids: [],
            },
            examsList: [],
            showSpecificSubject: false,
            subjects: [],
            loadingSubjects: false,
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
        departmentQualifications() {
            const list = this.systems?.global?.department_qualidactions || this.systems?.global?.department_qualifications
            return Array.isArray(list) ? list : []
        },
        filteredDepartments() {
            const qid = this.form?.academic_qualification_id
            if (!qid) return this.departments
            const allowed = new Set(
                this.departmentQualifications
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
        examsTerm() {
            const list = Array.isArray(this.examsList) && this.examsList.length ? this.examsList : this.systems?.global?.exams
            const items = Array.isArray(list) ? list : []
            return items.filter((e) => {
                const t = String(e?.exam_type || '')
                if (!t) return true
                return t === 'term'
            })
        },
        hasScope() {
            return !!this.form.academic_qualification_id && !!this.form.department_id && !!this.form.academic_class_id
        },
    },
    watch: {
        'form.academic_qualification_id'(n, p) {
            if (String(n || '') !== String(p || '')) {
                this.form.department_id = ''
                this.form.academic_class_id = ''
                this.form.child_subject_enabled_subject_ids = []
                this.subjects = []
            }
            this.fetchSubjects()
        },
        'form.department_id'() {
            this.fetchSubjects()
        },
        'form.academic_class_id'() {
            this.fetchSubjects()
        },
        'form.child_subject_enabled'(v) {
            if (Number(v) !== 0) {
                this.showSpecificSubject = false
                this.form.child_subject_enabled_subject_ids = []
            }
        },
        showSpecificSubject(v) {
            if (!v) this.form.child_subject_enabled_subject_ids = []
        },
    },
    async mounted() {
        await this.loadExamsTerm()
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
            window.location.href = '/admin/result'
        },
        async loadExamsTerm() {
            try {
                const res = await window.axios.get('/admin/get-exam', { params: { allData: true, exam_type: 'term' } })
                this.examsList = Array.isArray(res?.data) ? res.data : []
            } catch (_e) {
                this.examsList = []
            }
        },
        async fetchSubjects() {
            if (!this.hasScope) {
                this.subjects = []
                return
            }
            this.loadingSubjects = true
            try {
                const params = {
                    academic_qualification_id: this.form.academic_qualification_id,
                    department_id: this.form.department_id,
                    academic_class_id: this.form.academic_class_id,
                }
                const res = await window.axios.get('/admin/classwise-subjects', { params })
                this.subjects = Array.isArray(res?.data) ? res.data : []
            } catch (_e) {
                this.subjects = []
            } finally {
                this.loadingSubjects = false
            }
        },
        async submit() {
            if (this.submitting) return
            this.submitting = true
            this.error = ''
            this.message = ''

            try {
                const payload = {
                    academic_session_id: this.form.academic_session_id,
                    academic_qualification_id: this.form.academic_qualification_id,
                    department_id: this.form.department_id,
                    academic_class_id: this.form.academic_class_id,
                    exam_id: this.form.exam_id,
                    total_exam_subjects: this.form.total_exam_subjects,
                    child_subject_enabled: this.form.child_subject_enabled,
                    child_subject_enabled_subject_ids: Array.isArray(this.form.child_subject_enabled_subject_ids)
                        ? this.form.child_subject_enabled_subject_ids
                        : [],
                }

                const res = await window.axios.post('/admin/result', payload)
                const id = res?.data?.id
                this.message = res?.data?.message || 'Create Successfully!'
                this.$toast?.success?.(this.message)

                if (id) {
                    setTimeout(() => {
                        window.location.href = `/admin/result/${id}/edit`
                    }, 200)
                }
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to create result.'
                this.$toast?.error?.(this.error)
            } finally {
                this.submitting = false
            }
        },
    },
}
</script>
