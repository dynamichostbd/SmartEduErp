<template>
    <div class="rounded-sm border border-slate-300 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50/50 px-4 py-3">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Result</h2>
        </div>
        <div class="p-6 sm:p-10">
            <div v-if="loadingSystems" class="text-center py-4">
                <div class="inline-block h-6 w-6 animate-spin rounded-full border-2 border-[#0d6b75] border-t-transparent"></div>
                <div class="mt-2 text-sm text-slate-500 font-medium">Loading systems...</div>
            </div>
            <div v-else>
                <div v-if="error" class="mb-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700 flex items-center gap-3">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    {{ error }}
                </div>

                <form class="mx-auto w-full max-w-2xl space-y-5" @submit.prevent="search">
                    <div class="grid grid-cols-1 gap-5">
                        <!-- Session -->
                        <div class="grid grid-cols-1 items-center gap-2 sm:grid-cols-3">
                            <label class="text-sm font-bold text-slate-700">Session <span class="text-red-500">*</span></label>
                            <div class="sm:col-span-2">
                                <select v-model="form.academic_session_id" class="h-11 w-full rounded-md border border-slate-300 bg-white px-3 text-sm font-medium outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 transition-all" required>
                                    <option value="">--Select Any--</option>
                                    <option v-for="s in sessions" :key="'rs-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Academic Level -->
                        <div class="grid grid-cols-1 items-center gap-2 sm:grid-cols-3">
                            <label class="text-sm font-bold text-slate-700">Academic Level <span class="text-red-500">*</span></label>
                            <div class="sm:col-span-2">
                                <select v-model="form.academic_qualification_id" class="h-11 w-full rounded-md border border-slate-300 bg-white px-3 text-sm font-medium outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 transition-all" required>
                                    <option value="">--Select Any--</option>
                                    <option v-for="q in qualifications" :key="'rq-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Department -->
                        <div class="grid grid-cols-1 items-center gap-2 sm:grid-cols-3">
                            <label class="text-sm font-bold text-slate-700">Department <span class="text-red-500">*</span></label>
                            <div class="sm:col-span-2">
                                <select v-model="form.department_id" class="h-11 w-full rounded-md border border-slate-300 bg-white px-3 text-sm font-medium outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 transition-all" required>
                                    <option value="">--Select Any--</option>
                                    <option v-for="d in filteredDepartments" :key="'rd-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Class -->
                        <div class="grid grid-cols-1 items-center gap-2 sm:grid-cols-3">
                            <label class="text-sm font-bold text-slate-700">Class <span class="text-red-500">*</span></label>
                            <div class="sm:col-span-2">
                                <select v-model="form.academic_class_id" class="h-11 w-full rounded-md border border-slate-300 bg-white px-3 text-sm font-medium outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 transition-all" required>
                                    <option value="">--Select Any--</option>
                                    <option v-for="c in filteredClasses" :key="'rc-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Exam -->
                        <div class="grid grid-cols-1 items-center gap-2 sm:grid-cols-3">
                            <label class="text-sm font-bold text-slate-700">Exam <span class="text-red-500">*</span></label>
                            <div class="sm:col-span-2">
                                <select v-model="form.exam_id" class="h-11 w-full rounded-md border border-slate-300 bg-white px-3 text-sm font-medium outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 transition-all" required>
                                    <option value="">--Select Any--</option>
                                    <option v-for="e in exams" :key="'re-' + e.id" :value="String(e.id)">{{ e.name }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="pt-8 text-center">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 min-w-[160px] rounded-md bg-[#0d6b75] px-8 py-3 text-sm font-bold text-white shadow-md hover:bg-[#0b5b63] transition-all active:scale-[0.98] disabled:opacity-70" :disabled="submitting">
                            <svg v-if="submitting" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><circle cx="12" cy="12" r="10" opacity="0.25"></circle><path d="M12 2a10 10 0 0 1 10 10" opacity="0.75"></path></svg>
                            {{ submitting ? 'Processing...' : 'Search' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Result Modal -->
        <div v-if="showModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 p-2 sm:p-4 backdrop-blur-sm" @click.self="closeModal">
            <div class="max-h-[96vh] w-full max-w-6xl flex flex-col overflow-hidden rounded-md bg-white shadow-2xl animate-in fade-in zoom-in duration-200">
                <!-- Modal Header -->
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-[#1e293b] px-4 py-3 text-white">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        <span class="text-sm font-bold uppercase tracking-wider">Result Sheet</span>
                    </div>
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <button type="button" class="rounded bg-white/10 px-3 py-1.5 text-xs font-bold hover:bg-white/20 transition-colors flex items-center gap-1.5" @click="printResult">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                            Print
                        </button>
                        <button v-if="canDownload" type="button" class="rounded bg-blue-500 px-3 py-1.5 text-xs font-bold hover:bg-blue-600 transition-colors flex items-center gap-1.5" @click="downloadMarksheet">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            Download
                        </button>
                        <button type="button" class="rounded bg-emerald-600 px-3 py-1.5 text-xs font-bold hover:bg-emerald-700 transition-colors flex items-center gap-1.5" @click="searchAgain">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            Search Again
                        </button>
                        <button type="button" class="rounded bg-slate-600 px-3 py-1.5 text-xs font-bold hover:bg-slate-700 transition-colors" @click="closeModal">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="flex-1 overflow-y-auto p-4 sm:p-6 bg-slate-50">
                    <div id="printArea" class="mx-auto max-w-4xl bg-white p-6 shadow-sm border border-slate-200">
                        <!-- Print Header -->
                        <div class="mb-6 flex flex-col sm:flex-row items-center gap-4 border-b pb-6 text-center sm:text-left">
                            <img v-if="app.site.logo" :src="app.site.logo" class="h-16 w-auto" alt="Logo" />
                            <div class="flex-1">
                                <h2 class="text-2xl font-black text-slate-900 leading-tight uppercase">{{ app.collegeName }}</h2>
                                <p class="text-sm font-bold text-slate-600">{{ app.site.address }}</p>
                            </div>
                        </div>

                        <!-- Student Info Grid (Term) -->
                        <template v-if="isTerm">
                            <div class="info-grid mb-6">
                                <div class="info-row">
                                    <span class="info-label">Software ID</span>
                                    <span class="info-value">{{ resultStudent.student_id || '' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Session</span>
                                    <span class="info-value">{{ (resultPayload?.result?.academic_session?.name) || '' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Name</span>
                                    <span class="info-value">{{ resultStudent.name || '' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Academic Level</span>
                                    <span class="info-value">{{ (resultPayload?.result?.qualification?.name) || '' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">College Roll</span>
                                    <span class="info-value">{{ resultStudent.college_roll || '' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Mobile</span>
                                    <span class="info-value">{{ resultStudent.mobile || '' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Dept / Group</span>
                                    <span class="info-value">{{ (resultPayload?.result?.department?.name) || '' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Exam</span>
                                    <span class="info-value">{{ (resultPayload?.result?.exam?.name) || '' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Class</span>
                                    <span class="info-value">{{ (resultPayload?.result?.academic_class?.name) || '' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Status</span>
                                    <span class="info-value font-black uppercase" :class="resultDetails.result_status === 'PASSED' ? 'text-emerald-600' : 'text-rose-600'">{{ resultDetails.result_status || '' }}</span>
                                </div>
                            </div>
                        </template>

                        <!-- Student Info Grid (CT) -->
                        <template v-else>
                            <div class="mb-6 max-w-2xl">
                                <table class="w-full border-collapse border border-slate-300 text-sm">
                                    <tbody>
                                        <tr>
                                            <th class="border border-slate-300 bg-slate-50 px-4 py-2 text-left w-48 font-bold text-slate-700">SOFTWARE ID</th>
                                            <td class="border border-slate-300 px-4 py-2 font-medium">{{ resultStudent.student_id || '' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="border border-slate-300 bg-slate-50 px-4 py-2 text-left font-bold text-slate-700">STUDENT NAME</th>
                                            <td class="border border-slate-300 px-4 py-2 font-medium">{{ resultStudent.name || '' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="border border-slate-300 bg-slate-50 px-4 py-2 text-left font-bold text-slate-700">COLLEGE ROLL</th>
                                            <td class="border border-slate-300 px-4 py-2 font-medium">{{ resultStudent.college_roll || '' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="border border-slate-300 bg-slate-50 px-4 py-2 text-left font-bold text-slate-700">STUDENT MOBILE</th>
                                            <td class="border border-slate-300 px-4 py-2 font-medium">{{ resultStudent.mobile || '' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="border border-slate-300 bg-slate-50 px-4 py-2 text-left font-bold text-slate-700">RESULT STATUS</th>
                                            <td class="border border-slate-300 px-4 py-2">
                                                <span class="font-black uppercase" :class="resultDetails.result_status === 'PASSED' ? 'text-emerald-600' : 'text-rose-600'">{{ resultDetails.result_status || '' }}</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </template>

                        <!-- Marks Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse border border-slate-300 text-sm">
                                <thead v-if="isTerm">
                                    <tr class="bg-slate-100 text-center text-[10px] font-black uppercase tracking-wider text-slate-700">
                                        <th class="border border-slate-300 px-1 py-2">SI.</th>
                                        <th class="border border-slate-300 px-2 py-2 text-left" colspan="2">Name of Subjects</th>
                                        <th v-if="resultPayload?.result?.has_ct_mark" class="border border-slate-300 px-1 py-2">CT</th>
                                        <th class="border border-slate-300 px-1 py-2">CQ</th>
                                        <th class="border border-slate-300 px-1 py-2">MCQ</th>
                                        <th class="border border-slate-300 px-1 py-2">Prac.</th>
                                        <th class="border border-slate-300 px-1 py-2">Obtained</th>
                                        <th class="border border-slate-300 px-1 py-2">Total Obtained</th>
                                        <th class="border border-slate-300 px-1 py-2">Letter Grade</th>
                                        <th class="border border-slate-300 px-1 py-2">Grade Point</th>
                                        <th class="border border-slate-300 px-1 py-2">GPA</th>
                                        <th class="border border-slate-300 px-1 py-2">GRADE</th>
                                    </tr>
                                </thead>
                                <thead v-else>
                                    <tr class="bg-slate-100 text-center text-[11px] font-black uppercase tracking-wider text-slate-700">
                                        <th class="border border-slate-300 px-1 py-2 w-16">SI. No.</th>
                                        <th class="border border-slate-300 px-3 py-2 text-left">Name of Subjects</th>
                                        <th class="border border-slate-300 px-1 py-2 w-28">Exam Mark</th>
                                        <th class="border border-slate-300 px-1 py-2 w-28">Mark</th>
                                        <th class="border border-slate-300 px-1 py-2 w-32">Status</th>
                                    </tr>
                                </thead>

                                <tbody v-if="resultMarks && resultMarks.length > 0">
                                    <template v-if="isTerm">
                                        <tr v-for="(m, idx) in resultMarks" :key="'rm-' + idx" class="text-center font-medium">
                                            <!-- SI No. rowspanned per subject group -->
                                            <td v-if="shouldShowMergedCell(idx)" :rowspan="getMergedCellRowspan(idx)" class="border border-slate-300 px-1 py-1.5 align-middle bg-slate-50/30">
                                                {{ getGroupIndex(idx) }}
                                            </td>

                                            <!-- Parent subject cell: rowspanned for first child in group -->
                                            <td v-if="m.parent_subject_name && shouldShowMergedCell(idx)" :rowspan="getMergedCellRowspan(idx)" class="border border-slate-300 px-2 py-1.5 text-left align-middle font-bold bg-slate-50/30" :class="m.is_fourth_subject == 1 ? 'text-rose-600' : 'text-slate-800'">
                                                {{ m.parent_subject_name }}
                                                <span v-if="m.is_fourth_subject == 1" class="text-[9px] uppercase tracking-tighter">(4th)</span>
                                            </td>

                                            <!-- Child paper name OR standalone subject name -->
                                            <td v-if="!m.parent_subject_name" colspan="2" class="border border-slate-300 px-2 py-1.5 text-left align-middle" :class="m.is_fourth_subject == 1 ? 'text-rose-600 font-bold' : 'text-slate-800 font-semibold'">
                                                {{ m.subject?.name_en || '' }}
                                                <span v-if="m.is_fourth_subject == 1" class="text-[9px] uppercase tracking-tighter">(4th)</span>
                                            </td>
                                            <td v-else class="border border-slate-300 px-2 py-1.5 text-left align-middle text-slate-600">
                                                {{ m.subject?.name_en || '' }}
                                            </td>

                                            <!-- CT -->
                                            <td v-if="resultPayload?.result?.has_ct_mark" class="border border-slate-300 px-1 py-1.5 text-slate-500">
                                                <span v-if="m.is_absent == 1 && m.ct_mark == null && m.ct_allocated" class="text-rose-500 font-bold">ABS</span>
                                                <span v-else>{{ m.ct_mark }}</span>
                                            </td>

                                            <!-- CQ -->
                                            <td class="border border-slate-300 px-1 py-1.5 text-slate-500">
                                                <span v-if="m.is_absent == 1 && m.cq_mark == null && m.cq_allocated" class="text-rose-500 font-bold">ABS</span>
                                                <span v-else>{{ m.cq_mark }}</span>
                                            </td>

                                            <!-- MCQ -->
                                            <td class="border border-slate-300 px-1 py-1.5 text-slate-500">
                                                <span v-if="m.is_absent == 1 && m.mcq_mark == null && m.mcq_allocated" class="text-rose-500 font-bold">ABS</span>
                                                <span v-else>{{ m.mcq_mark }}</span>
                                            </td>

                                            <!-- Practical -->
                                            <td class="border border-slate-300 px-1 py-1.5 text-slate-500">
                                                <span v-if="m.is_absent == 1 && m.practical_mark == null && m.practical_allocated" class="text-rose-500 font-bold">ABS</span>
                                                <span v-else>{{ m.practical_mark }}</span>
                                            </td>

                                            <!-- Obtained -->
                                            <td class="border border-slate-300 px-1 py-1.5 font-bold text-slate-700">
                                                {{ m.obtained_mark }}
                                            </td>

                                            <!-- Total Obtained (merged) -->
                                            <td v-if="shouldShowMergedCell(idx)" :rowspan="getMergedCellRowspan(idx)" class="border border-slate-300 px-1 py-1.5 align-middle font-black text-slate-900 bg-slate-50/30">
                                                {{ m.total_mark }}
                                            </td>

                                            <!-- Letter Grade (merged) -->
                                            <td v-if="shouldShowMergedCell(idx)" :rowspan="getMergedCellRowspan(idx)" class="border border-slate-300 px-1 py-1.5 align-middle font-black bg-slate-50/30" :class="m.letter_grade === 'F' ? 'text-rose-600' : 'text-emerald-600'">
                                                {{ m.letter_grade }}
                                            </td>

                                            <!-- Grade Point (merged) -->
                                            <td v-if="shouldShowMergedCell(idx)" :rowspan="getMergedCellRowspan(idx)" class="border border-slate-300 px-1 py-1.5 align-middle font-mono text-slate-600 bg-slate-50/30">
                                                {{ m.gpa }}
                                            </td>

                                            <!-- Overall GPA (Shown once) -->
                                            <td v-if="idx == 0" :rowspan="resultMarks.length" class="border border-slate-300 px-1 py-1.5 align-middle font-black text-lg text-[#0d6b75] bg-slate-100/50">
                                                {{ resultDetails.gpa || '' }}
                                            </td>

                                            <!-- Overall GRADE (Shown once) -->
                                            <td v-if="idx == 0" :rowspan="resultMarks.length" class="border border-slate-300 px-1 py-1.5 align-middle font-black text-lg bg-slate-100/50" :class="resultDetails.letter_grade === 'F' ? 'text-rose-600' : 'text-[#0d6b75]'">
                                                {{ resultDetails.letter_grade || '' }}
                                            </td>
                                        </tr>
                                    </template>
                                    <template v-else>
                                        <!-- Class Test View -->
                                        <tr v-for="(m, idx) in resultMarks" :key="'ctm-' + idx" class="text-center font-medium">
                                            <td class="border border-slate-300 px-1 py-1.5">{{ idx + 1 }}</td>
                                            <td class="border border-slate-300 px-3 py-1.5 text-left font-bold text-slate-800">{{ m.subject?.name_en || '' }}</td>
                                            <td class="border border-slate-300 px-1 py-1.5 text-slate-500">{{ m.exam_mark }}</td>
                                            <td class="border border-slate-300 px-1 py-1.5 font-bold text-slate-900">{{ m.mark }}</td>
                                            <td class="border border-slate-300 px-1 py-1.5">
                                                <span class="inline-flex items-center gap-1 rounded-full px-3 py-0.5 text-[10px] font-bold uppercase tracking-wider" :class="m.result_status === 'PASSED' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'">
                                                    <span class="h-1.5 w-1.5 rounded-full" :class="m.result_status === 'PASSED' ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                                    {{ m.result_status }}
                                                </span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, inject, onMounted, reactive, ref, watch } from 'vue'

const app = inject('app')

const submitting = ref(false)
const error = ref('')
const showModal = ref(false)
const examType = ref('')
const resultPayload = ref(null)

const form = reactive({
    academic_session_id: '',
    academic_qualification_id: '',
    department_id: '',
    academic_class_id: '',
    exam_id: '',
})

const systems = computed(() => app?.studentProfileSystems || {})
const loadingSystems = computed(() => app?.studentProfileSystemsLoading)

const sessions = computed(() => (Array.isArray(systems.value?.academic_sessions) ? systems.value.academic_sessions : []))
const qualifications = computed(() => (Array.isArray(systems.value?.academic_qualifications) ? systems.value.academic_qualifications : []))
const departments = computed(() => (Array.isArray(systems.value?.departments) ? systems.value.departments : []))
const classes = computed(() => (Array.isArray(systems.value?.academic_classes) ? systems.value.academic_classes : []))
const exams = computed(() => (Array.isArray(systems.value?.exams) ? systems.value.exams : []))

const departmentQualidactions = computed(() => (Array.isArray(systems.value?.department_qualidactions) ? systems.value.department_qualidactions : []))

const filteredDepartments = computed(() => {
    const qid = String(form.academic_qualification_id || '')
    if (!qid) return departments.value
    const allow = new Set(
        departmentQualidactions.value
            .filter((x) => String(x?.academic_qualification_id ?? '') === qid)
            .map((x) => String(x?.department_id ?? ''))
            .filter(Boolean),
    )
    if (!allow.size) return departments.value
    return departments.value.filter((d) => allow.has(String(d?.id ?? '')))
})

const filteredClasses = computed(() => {
    const qid = String(form.academic_qualification_id || '')
    if (!qid) return classes.value
    return classes.value.filter((c) => String(c?.academic_qualification_id ?? '') === qid)
})

const isTerm = computed(() => String(examType.value || '') !== 'ct')

const resultStudent = computed(() => resultPayload.value?.student || {})
const resultDetails = computed(() => resultPayload.value?.result_details || {})
const resultMarks = computed(() => (Array.isArray(resultPayload.value?.marks) ? resultPayload.value.marks : []))

const canDownload = computed(() => {
    if (!isTerm.value) return false
    return !!resultPayload.value?.id
})

function closeModal() {
    showModal.value = false
}

function searchAgain() {
    closeModal()
    resultPayload.value = null
    examType.value = ''
}

function printResult() {
    const el = document.getElementById('printArea')
    if (!el) return
    const prtHtml = el.innerHTML
    let stylesHtml = ''
    for (const node of [...document.querySelectorAll('link[rel="stylesheet"], style')]) {
        stylesHtml += node.outerHTML
    }
    const win = window.open('', '', 'left=0,top=0,width=900,height=900,toolbar=0,scrollbars=1,status=0')
    if (!win) return
    win.document.write(`<!DOCTYPE html><html><head><title>Result</title>${stylesHtml}</head><body>${prtHtml}</body></html>`)
    setTimeout(() => {
        win.document.close()
        win.focus()
        win.print()
    }, 200)
}

function downloadMarksheet() {
    const id = resultPayload.value?.id
    if (!id) return
    const url = `/api/public/student/result/marksheet/${encodeURIComponent(String(id))}/download`
    window.open(url, '_blank')
}

function shouldShowMergedCell(index) {
    const marks = resultMarks.value
    if (!marks || !marks[index]) return true
    if (index === 0) return true

    const current = marks[index]
    const previous = marks[index - 1]

    if (!current.subject || !current.subject.parent_id) return true
    if (!previous.subject || String(current.subject.parent_id) !== String(previous.subject.parent_id)) return true

    return false
}

function getMergedCellRowspan(index) {
    const marks = resultMarks.value
    if (!marks || !marks[index]) return 1

    const parentId = marks[index].subject?.parent_id
    if (!parentId) return 1

    let count = 1
    for (let i = index + 1; i < marks.length; i++) {
        if (String(marks[i].subject?.parent_id) === String(parentId)) {
            count++;
        } else {
            break;
        }
    }
    return count
}

function getGroupIndex(index) {
    let count = 0
    for (let i = 0; i <= index; i++) {
        if (shouldShowMergedCell(i)) count++
    }
    return count
}

async function search() {
    submitting.value = true
    error.value = ''
    try {
        const payload = {
            academic_session_id: Number(form.academic_session_id || 0),
            academic_qualification_id: Number(form.academic_qualification_id || 0),
            department_id: Number(form.department_id || 0),
            academic_class_id: Number(form.academic_class_id || 0),
            exam_id: Number(form.exam_id || 0),
        }
        const res = await window.axios.post('/api/public/student/result/search', payload)
        if (res?.data?.error) {
            error.value = res.data.error
            return
        }
        examType.value = res?.data?.exam_type || ''
        resultPayload.value = res?.data?.result || null
        if (!resultPayload.value) {
            error.value = 'Result not found'
            return
        }
        showModal.value = true
    } catch (e) {
        error.value = e?.response?.data?.message || 'Something went wrong'
    } finally {
        submitting.value = false
    }
}

function prefillFromStudent() {
    const me = app?.studentMe || {}
    if (!form.academic_session_id && me?.academic_session_id != null) form.academic_session_id = String(me.academic_session_id)
    if (!form.academic_qualification_id && me?.academic_qualification_id != null) form.academic_qualification_id = String(me.academic_qualification_id)
    if (!form.department_id && me?.department_id != null) form.department_id = String(me.department_id)
    if (!form.academic_class_id && me?.academic_class_id != null) form.academic_class_id = String(me.academic_class_id)
}

onMounted(() => {
    prefillFromStudent()
})

watch(
    () => app?.studentMe,
    () => {
        prefillFromStudent()
    },
)
</script>

<style scoped>
.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    border: 1px solid #cbd5e1;
    border-radius: 2px;
}

.info-row {
    display: flex;
    border-right: 1px solid #cbd5e1;
    border-bottom: 1px solid #cbd5e1;
}

.info-row:nth-child(even) {
    border-right: none;
}

.info-label {
    background: #f8fafc;
    font-weight: 700;
    font-size: 11px;
    padding: 4px 8px;
    min-width: 130px;
    border-right: 1px solid #cbd5e1;
    color: #475569;
    text-transform: uppercase;
    display: flex;
    align-items: center;
}

.info-value {
    font-size: 13px;
    padding: 4px 8px;
    color: #1e293b;
    font-weight: 600;
    display: flex;
    align-items: center;
    flex: 1;
}

@media (max-width: 640px) {
    .info-grid {
        grid-template-columns: 1fr;
    }
    .info-row, .info-row:nth-child(even) {
        border-right: none;
    }
}
</style>
