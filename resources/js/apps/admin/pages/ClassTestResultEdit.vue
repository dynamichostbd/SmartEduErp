<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Class Test Result Marks Entry</div>
                    <div class="mt-1 text-sm text-slate-600">Enter marks by subject</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        @click="goBack"
                    >
                        Back
                    </button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Session</div>
                    <input :value="result.academic_session_name || ''" disabled class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm" />
                </div>
                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Academic Level</div>
                    <input :value="result.academic_qualification_name || ''" disabled class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm" />
                </div>
                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Department</div>
                    <input :value="result.department_name || ''" disabled class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm" />
                </div>
                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Class</div>
                    <input :value="result.academic_class_name || ''" disabled class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm" />
                </div>

                <div class="lg:col-span-4">
                    <div class="text-xs font-semibold text-slate-600">Exam</div>
                    <input :value="result.exam_name || ''" disabled class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm" />
                </div>

                <div class="lg:col-span-4">
                    <div class="text-xs font-semibold text-slate-600">Exam Date</div>
                    <input :value="result.exam_date || ''" disabled class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm" />
                </div>

                <div class="lg:col-span-4">
                    <div class="text-xs font-semibold text-slate-600">Subject <span class="text-rose-600">*</span></div>
                    <select
                        v-model="subjectId"
                        class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                        :disabled="isPublished || loadingSubjects"
                        @change="selectSubject"
                    >
                        <option value="">Select</option>
                        <option v-for="s in subjects" :key="'sub-' + s.subject_id" :value="String(s.subject_id)">{{ s.subject_name }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="text-sm text-slate-600">
                    <span class="font-semibold text-slate-900">Students</span>
                    <span class="text-slate-500">({{ details.length }})</span>
                </div>
                <div v-if="subject" class="text-xs text-slate-500">
                    Exam Mark: <span class="font-semibold text-slate-900">{{ subject.ct_mark ?? '--' }}</span>
                    <span class="mx-2">|</span>
                    Pass Mark: <span class="font-semibold text-slate-900">{{ subject.ct_pass_mark ?? '--' }}</span>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Roll</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Software ID</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Student</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Mark</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="(d, idx) in details" :key="'d-' + idx">
                            <td class="px-3 py-2 text-slate-800">{{ d?.student?.college_roll || '' }}</td>
                            <td class="px-3 py-2 text-slate-800">{{ d?.student?.student_id || '' }}</td>
                            <td class="px-3 py-2 text-slate-800">{{ d?.student?.name || '' }}</td>
                            <td class="px-3 py-2">
                                <input
                                    v-model="details[idx].marks[0].mark"
                                    type="number"
                                    step="0.01"
                                    class="h-9 w-28 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                    :disabled="isPublished || !subjectId"
                                    :ref="(el) => setMarkInputRef(el, idx)"
                                    @keydown.enter.prevent="saveAndMove(idx, 1)"
                                    @keydown.tab.prevent="saveAndMove(idx, $event.shiftKey ? -1 : 1)"
                                    @blur="saveOnly(idx)"
                                />
                            </td>
                        </tr>
                        <tr v-if="!details.length">
                            <td colspan="4" class="px-3 py-10 text-center text-slate-500">
                                {{ subjectId ? 'No students found' : 'Select a subject to load students' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ClassTestResultEdit',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        resultId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            loading: false,
            error: '',
            message: '',
            result: {
                id: null,
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
                exam_id: '',
                exam_date: '',
                published_date: '',
                status: '',
                academic_session_name: '',
                academic_qualification_name: '',
                department_name: '',
                academic_class_name: '',
                exam_name: '',
            },
            subjects: [],
            loadingSubjects: false,
            subjectId: '',
            subject: null,
            details: [],
            savingIndex: null,
            markInputRefs: [],
            lastSavedByKey: {},
        }
    },
    computed: {
        isPublished() {
            return String(this.result?.status || '') === 'published'
        },
    },
    async mounted() {
        await this.loadResult()
        await this.loadSubjects()
    },
    methods: {
        setMarkInputRef(el, idx) {
            if (!el) return
            this.markInputRefs[idx] = el
        },
        goBack() {
            window.location.href = '/admin/classTestResult'
        },
        async loadResult() {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/classTestResult/${this.resultId}/details`)
                const r = res?.data || {}
                this.result = {
                    ...this.result,
                    ...r,
                    id: r?.id || this.resultId,
                }

                // Load names from list endpoint style if present
                if (r?.academic_session_name) this.result.academic_session_name = r.academic_session_name
                if (r?.academic_qualification_name) this.result.academic_qualification_name = r.academic_qualification_name
                if (r?.department_name) this.result.department_name = r.department_name
                if (r?.academic_class_name) this.result.academic_class_name = r.academic_class_name
                if (r?.exam_name) this.result.exam_name = r.exam_name
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load result.'
            } finally {
                this.loading = false
            }
        },
        async loadSubjects() {
            this.loadingSubjects = true
            this.error = ''
            try {
                const params = {
                    academic_qualification_id: this.result.academic_qualification_id,
                    department_id: this.result.department_id,
                    academic_class_id: this.result.academic_class_id,
                }
                const res = await window.axios.get('/admin/classwise-subjects', { params })
                this.subjects = Array.isArray(res?.data) ? res.data : []
            } catch (e) {
                this.subjects = []
                this.error = e?.response?.data?.message || 'Failed to load subjects.'
            } finally {
                this.loadingSubjects = false
            }
        },
        async selectSubject() {
            const s = (this.subjects || []).find((x) => String(x?.subject_id) === String(this.subjectId))
            this.subject = s || null
            await this.loadStudents()
        },
        async loadStudents() {
            this.details = []
            this.error = ''
            this.message = ''
            this.markInputRefs = []

            if (!this.subjectId) return

            try {
                const selectedStudent =
                    Number(this.subject?.main_subject || 0) || Number(this.subject?.fourth_subject || 0) ? 1 : 0

                const params = {
                    result_id: this.result.id,
                    academic_session_id: this.result.academic_session_id,
                    academic_class_id: this.result.academic_class_id,
                    department_id: this.result.department_id,
                    academic_qualification_id: this.result.academic_qualification_id,
                    subject_id: this.subjectId,
                    exam_id: this.result.exam_id,
                    selected_student: selectedStudent,
                }

                const res = await window.axios.get('/admin/students-for-class-test-marks-entry', { params })
                this.details = Array.isArray(res?.data) ? res.data : []

                this.$nextTick(() => {
                    const el = this.markInputRefs[0]
                    if (el && typeof el.focus === 'function') {
                        el.focus()
                        if (typeof el.select === 'function') el.select()
                    }
                })
            } catch (e) {
                this.details = []
                this.error = e?.response?.data?.message || 'Failed to load students.'
            }
        },
        validateMark(mark) {
            if (mark === null || mark === undefined || mark === '') return true
            const num = Number(mark)
            if (Number.isNaN(num)) return false
            const max = Number(this.subject?.ct_mark ?? this.subject?.exam_mark ?? null)
            if (Number.isFinite(max) && max >= 0) {
                return num <= max
            }
            return true
        },
        normalizeMark(mark) {
            if (mark === null || mark === undefined || mark === '') return ''
            const num = Number(mark)
            if (Number.isNaN(num)) return String(mark)
            return String(num)
        },
        detailKey(index) {
            const d = this.details[index]
            const sid = d?.student_id || d?.student?.id || ''
            return `${String(this.result?.id || '')}:${String(this.subjectId || '')}:${String(sid)}`
        },
        computeResultStatus(mark, passMark) {
            if (mark === null || mark === undefined || mark === '') return null
            const m = Number(mark)
            const p = Number(passMark)
            if (Number.isNaN(m) || Number.isNaN(p)) return null
            return m < p ? 'FAILED' : 'PASSED'
        },
        async saveOnly(index) {
            await this.submitMark(index)
        },
        async saveAndMove(index, step) {
            const ok = await this.submitMark(index)
            if (!ok) return

            const next = Math.max(0, Math.min(this.details.length - 1, index + step))
            this.$nextTick(() => {
                const el = this.markInputRefs[next]
                if (el && typeof el.focus === 'function') {
                    el.focus()
                    if (typeof el.select === 'function') el.select()
                }
            })
        },
        async submitMark(index) {
            if (!this.subjectId) return
            if (this.isPublished) return

            const d = this.details[index]
            if (!d) return

            const mark = d?.marks?.[0]?.mark
            if (!this.validateMark(mark)) {
                this.error = 'Invalid mark (exceed exam mark)'
                return
            }

            const key = this.detailKey(index)
            const normalized = this.normalizeMark(mark)
            if (this.lastSavedByKey[key] === normalized) {
                return true
            }

            if (this.savingIndex === index) {
                return false
            }

            this.savingIndex = index
            this.error = ''
            this.message = ''

            try {
                const payload = {
                    details: d,
                    subject_id: this.subjectId,
                    subject: this.subject,
                }
                const res = await window.axios.post(`/admin/classTestResult/${this.result.id}`, payload)
                const passMark = d?.marks?.[0]?.pass_mark
                const status = this.computeResultStatus(mark, passMark)
                if (this.details[index]?.marks?.[0]) {
                    this.details[index].marks[0].result_status = status
                }

                this.lastSavedByKey[key] = normalized
                this.message = res?.data?.message || ''
                if (this.message) {
                    this.$toast?.success?.(this.message)
                }
                
                return true
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to save mark.'
                this.$toast?.error?.(this.error)
                return false
            } finally {
                this.savingIndex = null
            }
        },
    },
}
</script>
