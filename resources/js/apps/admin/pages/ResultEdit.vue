<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Result Marks Entry</div>
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

                    <button
                        type="button"
                        class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800"
                        :disabled="syncing"
                        @click="syncResult"
                    >
                        {{ syncing ? 'Syncing...' : 'Sync Result' }}
                    </button>

                    <button
                        type="button"
                        class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700"
                        :disabled="syncingSubject"
                        @click="syncSubject"
                    >
                        {{ syncingSubject ? 'Syncing...' : 'Sync Subject' }}
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
                    <input :value="result.academic_session_name || ''" disabled class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm" />
                </div>
                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Academic Level</div>
                    <input :value="result.academic_qualification_name || ''" disabled class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm" />
                </div>
                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Department</div>
                    <input :value="result.department_name || ''" disabled class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm" />
                </div>
                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Class</div>
                    <input :value="result.academic_class_name || ''" disabled class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm" />
                </div>

                <div class="lg:col-span-4">
                    <div class="text-xs font-semibold text-slate-600">Exam</div>
                    <input :value="result.exam_name || ''" disabled class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm" />
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Exam Subjects</div>
                    <input
                        v-model.number="totalExamSubjects"
                        type="number"
                        min="1"
                        class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                        :disabled="isPublished"
                        @change="saveTotalExamSubjects"
                    />
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Convert Mark</div>
                    <select
                        v-model.number="convertMark"
                        class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                        :disabled="isPublished"
                    >
                        <option :value="0">No</option>
                        <option :value="1">Yes</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Subject <span class="text-rose-600">*</span></div>
                    <select
                        v-model="subjectId"
                        class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                        :disabled="isPublished || loadingSubjects"
                        @change="selectSubject"
                    >
                        <option value="">Select</option>
                        <option v-for="s in subjects" :key="'sub-' + s.subject_id" :value="String(s.subject_id)">{{ s.subject_name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-3" v-if="hasChildSubjects">
                    <div class="text-xs font-semibold text-slate-600">Paper</div>
                    <select
                        v-model="childSubjectId"
                        class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                        :disabled="isPublished || !subjectId"
                        @change="filterChildSubject"
                    >
                        <option value="">All Papers</option>
                        <option v-for="c in childSubjects" :key="'c-' + c.id" :value="String(c.id)">{{ c.name_en }}</option>
                    </select>
                    <div class="mt-1 text-xs text-slate-500">If child enabled, selecting a paper will show only that paper's mark fields</div>
                </div>

                <div class="lg:col-span-12 flex flex-wrap items-end gap-2">
                    <button
                        type="button"
                        class="h-10 rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800"
                        :disabled="loading || !subjectId"
                        @click="loadStudents"
                    >
                        {{ loading ? '...' : 'Load Students' }}
                    </button>

                    <button
                        type="button"
                        class="h-10 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700"
                        :disabled="savingAll || isPublished || !subjectId"
                        @click="saveAll"
                    >
                        {{ savingAll ? 'Saving...' : 'SUBMIT ALL MARKS / ENTRIES' }}
                    </button>
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
                    CT: <span class="font-semibold text-slate-900">{{ subject.ct_mark ?? '--' }}</span>
                    <span class="mx-2">|</span>
                    CQ: <span class="font-semibold text-slate-900">{{ subject.cq_mark ?? '--' }}</span>
                    <span class="mx-2">|</span>
                    MCQ: <span class="font-semibold text-slate-900">{{ subject.mcq_mark ?? '--' }}</span>
                    <span class="mx-2">|</span>
                    Practical: <span class="font-semibold text-slate-900">{{ subject.practical_mark ?? '--' }}</span>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Roll</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Software ID</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Student</th>

                            <th class="px-3 py-2 text-center font-semibold text-slate-700">CT</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700" v-if="showCq">CQ</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700" v-if="showMcq">MCQ</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700" v-if="showPractical">Practical</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Absent</th>

                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Obtained</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="(d, idx) in details" :key="'d-' + idx">
                            <td class="px-3 py-2 text-slate-800">{{ d?.student?.college_roll || '' }}</td>
                            <td class="px-3 py-2 text-slate-800">{{ d?.student?.student_id || '' }}</td>
                            <td class="px-3 py-2 text-slate-800">{{ d?.student?.name || '' }}</td>

                            <td class="px-3 py-2">
                                <input
                                    v-model="details[idx].marks[0].ct_mark"
                                    type="number"
                                    step="0.01"
                                    class="h-9 w-24 rounded-lg border border-slate-200 bg-white px-2 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                    :disabled="isPublished || !subjectId"
                                    :ref="(el) => setInputRef(el, idx, 'ct')"
                                    @keyup="recalcRow(idx)"
                                    @keydown.enter.prevent="saveAndMove(idx, 1)"
                                    @keydown.tab.prevent="saveAndMove(idx, $event.shiftKey ? -1 : 1)"
                                />
                            </td>

                            <td class="px-3 py-2" v-if="showCq">
                                <input
                                    v-model="details[idx].marks[0].cq_mark"
                                    type="number"
                                    step="0.01"
                                    class="h-9 w-24 rounded-lg border border-slate-200 bg-white px-2 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                    :disabled="isPublished || !subjectId"
                                    :ref="(el) => setInputRef(el, idx, 'cq')"
                                    @keyup="recalcRow(idx)"
                                    @keydown.enter.prevent="saveAndMove(idx, 1)"
                                    @keydown.tab.prevent="saveAndMove(idx, $event.shiftKey ? -1 : 1)"
                                />
                            </td>

                            <td class="px-3 py-2" v-if="showMcq">
                                <input
                                    v-model="details[idx].marks[0].mcq_mark"
                                    type="number"
                                    step="0.01"
                                    class="h-9 w-24 rounded-lg border border-slate-200 bg-white px-2 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                    :disabled="isPublished || !subjectId"
                                    :ref="(el) => setInputRef(el, idx, 'mcq')"
                                    @keyup="recalcRow(idx)"
                                    @keydown.enter.prevent="saveAndMove(idx, 1)"
                                    @keydown.tab.prevent="saveAndMove(idx, $event.shiftKey ? -1 : 1)"
                                />
                            </td>

                            <td class="px-3 py-2" v-if="showPractical">
                                <input
                                    v-model="details[idx].marks[0].practical_mark"
                                    type="number"
                                    step="0.01"
                                    class="h-9 w-24 rounded-lg border border-slate-200 bg-white px-2 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                    :disabled="isPublished || !subjectId"
                                    :ref="(el) => setInputRef(el, idx, 'practical')"
                                    @keyup="recalcRow(idx)"
                                    @keydown.enter.prevent="saveAndMove(idx, 1)"
                                    @keydown.tab.prevent="saveAndMove(idx, $event.shiftKey ? -1 : 1)"
                                />
                            </td>

                            <td class="px-3 py-2 text-center">
                                <input
                                    v-model="details[idx].marks[0].is_absent"
                                    type="checkbox"
                                    class="h-4 w-4"
                                    :disabled="isPublished || !subjectId"
                                    @change="recalcRow(idx)"
                                />
                            </td>

                            <td class="px-3 py-2 text-center text-slate-800">{{ details[idx].marks[0].obtained_mark ?? '' }}</td>
                            <td class="px-3 py-2 text-center text-slate-800">{{ details[idx].marks[0].total_mark ?? '' }}</td>
                        </tr>

                        <tr v-if="!details.length">
                            <td :colspan="tableColspan" class="px-3 py-10 text-center text-slate-500">
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
    name: 'ResultEdit',
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
            result: {
                id: null,
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
                exam_id: '',
                total_exam_subjects: 7,
                published_date: '',
                status: '',
                child_subject_enabled: 0,
                child_subject_enabled_subject_ids: [],
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
            savingAll: false,
            convertMark: 0,
            totalExamSubjects: 7,
            gradeRanges: [],
            syncing: false,
            syncingSubject: false,
            childEnabled: false,
            childSubjects: [],
            childSubjectId: '',
            inputRefs: {},
        }
    },
    computed: {
        isPublished() {
            return String(this.result?.status || '') === 'published'
        },
        showCq() {
            return Number(this.subject?.cq_mark || 0) > 0
        },
        showMcq() {
            return Number(this.subject?.mcq_mark || 0) > 0
        },
        showPractical() {
            return Number(this.subject?.practical_mark || 0) > 0
        },
        hasChildSubjects() {
            return !!this.childEnabled && Array.isArray(this.childSubjects) && this.childSubjects.length > 0
        },
        tableColspan() {
            let n = 0
            n += 3 // Roll, Software ID, Student
            n += 1 // CT
            n += this.showCq ? 1 : 0
            n += this.showMcq ? 1 : 0
            n += this.showPractical ? 1 : 0
            n += 1 // Absent
            n += 1 // Obtained
            n += 1 // Total
            return n
        },
    },
    async mounted() {
        this.gradeRanges = Array.isArray(this.systems?.global?.grade_management) ? this.systems.global.grade_management : []
        await this.loadResult()
        await this.loadSubjects()
        this.totalExamSubjects = Number(this.result?.total_exam_subjects || 7)
    },
    methods: {
        goBack() {
            window.location.href = '/admin/result'
        },
        setInputRef(el, idx, key) {
            if (!el) return
            this.inputRefs[`${idx}:${key}`] = el
        },
        focusRow(idx) {
            const el = this.inputRefs[`${idx}:ct`]
            if (el && typeof el.focus === 'function') el.focus()
        },
        async loadResult() {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/result/${this.resultId}/details`)
                const r = res?.data || {}
                this.result = { ...this.result, ...r, id: r?.id || this.resultId }
                this.totalExamSubjects = Number(r?.total_exam_subjects || 7)
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
        selectSubject() {
            const s = (this.subjects || []).find((x) => String(x?.subject_id) === String(this.subjectId))
            this.subject = s || null
            this.childSubjectId = ''
            this.details = []
            this.childEnabled = false
            this.childSubjects = []
        },
        filterChildSubject() {
            // handled server-side by fetching students again; keep here so UI matches legacy
            this.loadStudents()
        },
        async saveTotalExamSubjects() {
            // update happens inside students-for-marks-entry if you pass total_exam_subjects
            await this.loadStudents()
        },
        async loadStudents() {
            this.details = []
            this.error = ''
            this.inputRefs = {}

            if (!this.subjectId) return

            try {
                const selectedStudent = Number(this.subject?.main_subject || 0) || Number(this.subject?.fourth_subject || 0) ? 1 : 0

                const params = {
                    result_id: this.result.id,
                    academic_session_id: this.result.academic_session_id,
                    academic_class_id: this.result.academic_class_id,
                    department_id: this.result.department_id,
                    academic_qualification_id: this.result.academic_qualification_id,
                    subject_id: this.subjectId,
                    exam_id: this.result.exam_id,
                    selected_student: selectedStudent,
                    total_exam_subjects: this.totalExamSubjects,
                }

                const res = await window.axios.get('/admin/students-for-marks-entry', { params })

                const payload = res?.data || {}
                this.details = Array.isArray(payload?.details) ? payload.details : []
                this.childEnabled = Number(payload?.child_enabled || 0) === 1
                this.childSubjects = Array.isArray(payload?.child_subjects) ? payload.child_subjects : []

                if (this.hasChildSubjects && this.childSubjectId) {
                    // filter to only selected child paper
                    const id = Number(this.childSubjectId)
                    this.details = (this.details || []).map((d) => {
                        const marks = Array.isArray(d?.marks) ? d.marks : []
                        return {
                            ...d,
                            marks: marks.filter((m) => Number(m?.subject_id) === id),
                        }
                    })
                }

                this.details.forEach((_d, idx) => {
                    this.recalcRow(idx)
                })

                this.$nextTick(() => this.focusRow(0))
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load students.'
            }
        },
        recalcRow(idx) {
            const mark = this.details?.[idx]?.marks?.[0]
            if (!mark) return

            const ct = Number(mark.ct_mark || 0)
            const cq = Number(mark.cq_mark || 0)
            const mcq = Number(mark.mcq_mark || 0)
            const practical = Number(mark.practical_mark || 0)
            const isAbsent = !!mark.is_absent

            const obtainedRaw = cq + mcq + practical
            const obtained = this.convertMark ? (obtainedRaw * 80) / 100 : obtainedRaw
            const total = obtained + ct

            mark.obtained_mark = Number.isFinite(obtained) ? obtained.toFixed(2) : ''
            mark.total_mark = Number.isFinite(total) ? total.toFixed(2) : ''

            if (isAbsent) {
                mark.gpa = 0
                mark.letter_grade = 'ABS'
                return
            }

            const passes = {
                ct: Number(this.subject?.ct_pass_mark || 0),
                cq: Number(this.subject?.cq_pass_mark || 0),
                mcq: Number(this.subject?.mcq_pass_mark || 0),
                practical: Number(this.subject?.practical_pass_mark || 0),
            }

            const fail = (passes.ct > 0 && ct < passes.ct) || (passes.cq > 0 && cq < passes.cq) || (passes.mcq > 0 && mcq < passes.mcq) || (passes.practical > 0 && practical < passes.practical)

            if (fail) {
                mark.gpa = 0
                mark.letter_grade = 'F'
                return
            }

            const range = (this.gradeRanges || []).find((r) => Number(r?.from_mark) <= total && Number(r?.to_mark) >= total)
            if (range) {
                mark.gpa = Number(range.gpa || 0)
                mark.letter_grade = range.grade || 'F'
            } else {
                mark.gpa = 0
                mark.letter_grade = 'F'
            }
        },
        async saveOnly(idx) {
            if (this.savingIndex !== null) return
            const d = this.details?.[idx]
            if (!d) return

            this.savingIndex = idx
            this.error = ''

            try {
                const effectiveSubjectId = this.hasChildSubjects && this.childSubjectId ? Number(this.childSubjectId) : Number(this.subjectId)
                const payload = {
                    type: 'single',
                    is_convert: this.convertMark ? 1 : 0,
                    subject: {
                        subject_id: effectiveSubjectId,
                        ct_pass_mark: this.subject?.ct_pass_mark,
                        cq_pass_mark: this.subject?.cq_pass_mark,
                        mcq_pass_mark: this.subject?.mcq_pass_mark,
                        practical_pass_mark: this.subject?.practical_pass_mark,
                    },
                    details: d,
                }

                const res = await window.axios.post(`/admin/result/${this.result.id}`, payload)
                const msg = res?.data?.message || 'Update Successfully!'
                this.$toast?.success?.(msg)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to save.'
                this.$toast?.error?.(this.error)
            } finally {
                this.savingIndex = null
            }
        },
        async saveAll() {
            if (this.savingAll) return
            this.savingAll = true
            this.error = ''
            try {
                const effectiveSubjectId = this.hasChildSubjects && this.childSubjectId ? Number(this.childSubjectId) : Number(this.subjectId)
                const payload = {
                    type: 'all',
                    is_convert: this.convertMark ? 1 : 0,
                    subject: {
                        subject_id: effectiveSubjectId,
                        ct_pass_mark: this.subject?.ct_pass_mark,
                        cq_pass_mark: this.subject?.cq_pass_mark,
                        mcq_pass_mark: this.subject?.mcq_pass_mark,
                        practical_pass_mark: this.subject?.practical_pass_mark,
                    },
                    details: this.details,
                }
                const res = await window.axios.post(`/admin/result/${this.result.id}`, payload)
                const msg = res?.data?.message || 'Update Successfully!'
                this.$toast?.success?.(msg)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to save.'
                this.$toast?.error?.(this.error)
            } finally {
                this.savingAll = false
            }
        },
        async saveAndMove(idx, dir) {
            await this.saveOnly(idx)
            const next = idx + dir
            if (next < 0 || next >= (this.details || []).length) return
            this.$nextTick(() => this.focusRow(next))
        },
        async syncResult() {
            this.syncing = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/result-sync/${this.result.id}`)
                const msg = res?.data?.message || 'Result Sync Successfully!'
                this.$toast?.success?.(msg)
                await this.loadStudents()
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to sync.'
                this.$toast?.error?.(this.error)
            } finally {
                this.syncing = false
            }
        },
        async syncSubject() {
            this.syncingSubject = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/result-sync-subject/${this.result.id}`)
                const msg = res?.data?.message || 'Subject sync completed'
                this.$toast?.success?.(msg)
                await this.loadStudents()
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to sync subjects.'
                this.$toast?.error?.(this.error)
            } finally {
                this.syncingSubject = false
            }
        },
    },
}
</script>
