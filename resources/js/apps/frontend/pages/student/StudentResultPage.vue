<template>
    <div class="mt-3 rounded-sm border border-slate-300 bg-white p-4 sm:p-6">
        <div class="mx-auto w-full max-w-6xl">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-3">
                    <StudentSidebar
                        :studentMe="app.studentMe"
                        :active="app.studentSidebarActive"
                        :onNavigate="app.studentSidebarGo"
                        :onLogout="app.doStudentLogout"
                        :logoutDisabled="app.studentAuthLoading"
                    />
                </div>

                <div class="lg:col-span-9">
                    <div class="rounded-sm border border-slate-300 bg-white">
                        <div class="border-b border-slate-300 bg-slate-50 px-4 py-3 text-sm font-extrabold text-slate-800">RESULT</div>
                        <div class="p-4">
                            <div v-if="loadingSystems" class="text-sm text-slate-600">Loading...</div>
                            <div v-else>
                                <div v-if="error" class="mb-4 rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-800">{{ error }}</div>

                                <form class="mx-auto w-full max-w-2xl" @submit.prevent="search">
                                    <div class="grid grid-cols-1 gap-4">
                                        <div class="grid grid-cols-1 items-center gap-2 sm:grid-cols-3">
                                            <div class="text-sm font-semibold text-slate-700">Session <span class="text-red-600">*</span></div>
                                            <div class="sm:col-span-2">
                                                <select v-model="form.academic_session_id" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" required>
                                                    <option value="">--Select Any--</option>
                                                    <option v-for="s in sessions" :key="'rs-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 items-center gap-2 sm:grid-cols-3">
                                            <div class="text-sm font-semibold text-slate-700">Academic Level <span class="text-red-600">*</span></div>
                                            <div class="sm:col-span-2">
                                                <select v-model="form.academic_qualification_id" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" required>
                                                    <option value="">--Select Any--</option>
                                                    <option v-for="q in qualifications" :key="'rq-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 items-center gap-2 sm:grid-cols-3">
                                            <div class="text-sm font-semibold text-slate-700">Department <span class="text-red-600">*</span></div>
                                            <div class="sm:col-span-2">
                                                <select v-model="form.department_id" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" required>
                                                    <option value="">--Select Any--</option>
                                                    <option v-for="d in filteredDepartments" :key="'rd-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 items-center gap-2 sm:grid-cols-3">
                                            <div class="text-sm font-semibold text-slate-700">Class <span class="text-red-600">*</span></div>
                                            <div class="sm:col-span-2">
                                                <select v-model="form.academic_class_id" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" required>
                                                    <option value="">--Select Any--</option>
                                                    <option v-for="c in filteredClasses" :key="'rc-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 items-center gap-2 sm:grid-cols-3">
                                            <div class="text-sm font-semibold text-slate-700">Exam <span class="text-red-600">*</span></div>
                                            <div class="sm:col-span-2">
                                                <select v-model="form.exam_id" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" required>
                                                    <option value="">--Select Any--</option>
                                                    <option v-for="e in exams" :key="'re-' + e.id" :value="String(e.id)">{{ e.name }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-8 text-center">
                                        <button type="submit" class="rounded-sm bg-[#0b1d4d] px-10 py-2.5 text-sm font-extrabold text-white hover:bg-[#09163c]" :disabled="submitting">
                                            {{ submitting ? 'Processing...' : 'Search' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="closeModal">
                        <div class="max-h-[92vh] w-full max-w-5xl overflow-hidden rounded-sm bg-white shadow-xl">
                            <div class="flex items-center justify-between gap-3 border-b border-slate-300 bg-slate-50 px-4 py-3">
                                <div class="text-sm font-extrabold text-slate-800">RESULT SHEET</div>
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-3 py-1.5 text-xs font-extrabold text-slate-700 hover:bg-slate-50" @click="printResult">
                                        Print
                                    </button>
                                    <button
                                        v-if="canDownload"
                                        type="button"
                                        class="rounded-sm bg-[#2b6cb0] px-3 py-1.5 text-xs font-extrabold text-white hover:bg-[#245a94]"
                                        @click="downloadMarksheet"
                                    >
                                        Download
                                    </button>
                                    <button type="button" class="rounded-sm bg-emerald-600 px-3 py-1.5 text-xs font-extrabold text-white hover:bg-emerald-700" @click="searchAgain">
                                        Search Again
                                    </button>
                                    <button type="button" class="rounded-sm bg-slate-700 px-3 py-1.5 text-xs font-extrabold text-white hover:bg-slate-800" @click="closeModal">
                                        Close
                                    </button>
                                </div>
                            </div>

                            <div class="max-h-[82vh] overflow-y-auto p-4">
                                <div id="printArea">
                                    <div class="grid grid-cols-1 gap-4">
                                        <div class="rounded-sm border border-slate-300 bg-white p-4">
                                            <div class="text-sm font-extrabold text-slate-900">Student</div>
                                            <div class="mt-2 grid grid-cols-1 gap-2 text-sm text-slate-700 sm:grid-cols-2">
                                                <div><span class="font-semibold">Name:</span> {{ resultStudent.name || '' }}</div>
                                                <div><span class="font-semibold">Student ID:</span> {{ resultStudent.student_id || '' }}</div>
                                                <div><span class="font-semibold">Mobile:</span> {{ resultStudent.mobile || '' }}</div>
                                                <div><span class="font-semibold">College Roll:</span> {{ resultStudent.college_roll || '' }}</div>
                                            </div>
                                        </div>

                                        <div v-if="isTerm" class="rounded-sm border border-slate-300 bg-white p-4">
                                            <div class="text-sm font-extrabold text-slate-900">Summary</div>
                                            <div class="mt-2 grid grid-cols-1 gap-2 text-sm text-slate-700 sm:grid-cols-3">
                                                <div><span class="font-semibold">GPA:</span> {{ resultDetails.gpa ?? '' }}</div>
                                                <div><span class="font-semibold">Grade:</span> {{ resultDetails.letter_grade ?? '' }}</div>
                                                <div><span class="font-semibold">Status:</span> {{ resultDetails.result_status ?? '' }}</div>
                                            </div>
                                        </div>

                                        <div class="rounded-sm border border-slate-300 bg-white">
                                            <div class="border-b border-slate-300 bg-slate-50 px-4 py-2 text-xs font-extrabold text-slate-800">Marks</div>
                                            <div class="overflow-x-auto p-4">
                                                <table class="min-w-full text-sm">
                                                    <thead>
                                                        <tr class="border-b bg-slate-100 text-left text-xs font-extrabold text-slate-700">
                                                            <th class="px-3 py-2">#</th>
                                                            <th class="px-3 py-2">Subject</th>
                                                            <th class="px-3 py-2 text-right">Total</th>
                                                            <template v-if="isTerm">
                                                                <th class="px-3 py-2 text-right">Obtained</th>
                                                                <th class="px-3 py-2 text-right">GPA</th>
                                                                <th class="px-3 py-2 text-right">Grade</th>
                                                            </template>
                                                            <template v-else>
                                                                <th class="px-3 py-2 text-right">Mark</th>
                                                                <th class="px-3 py-2">Status</th>
                                                            </template>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="(m, idx) in resultMarks" :key="'rm-' + idx" class="border-b last:border-b-0">
                                                            <td class="px-3 py-2">{{ idx + 1 }}</td>
                                                            <td class="px-3 py-2 font-semibold">{{ m.subject?.name_en || '' }}</td>
                                                            <td class="px-3 py-2 text-right">{{ isTerm ? (m.total_mark ?? '') : (m.exam_mark ?? '') }}</td>
                                                            <template v-if="isTerm">
                                                                <td class="px-3 py-2 text-right">{{ m.obtained_mark ?? '' }}</td>
                                                                <td class="px-3 py-2 text-right">{{ m.gpa ?? '' }}</td>
                                                                <td class="px-3 py-2 text-right">{{ m.letter_grade ?? '' }}</td>
                                                            </template>
                                                            <template v-else>
                                                                <td class="px-3 py-2 text-right">{{ m.mark ?? '' }}</td>
                                                                <td class="px-3 py-2">{{ m.result_status ?? '' }}</td>
                                                            </template>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, inject, onMounted, reactive, ref, watch } from 'vue'
import StudentSidebar from '../../components/student/StudentSidebar.vue'

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
