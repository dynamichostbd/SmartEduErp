<template>
    <div class="mt-3 rounded-md border border-slate-200 bg-white p-4 sm:p-6">
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
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="rounded-md border border-slate-200 bg-white">
                            <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 text-sm font-extrabold text-slate-800">COMPULSORY SUBJECTS</div>
                            <div class="p-4">
                                <div v-if="loading" class="text-sm text-slate-600">Loading...</div>
                                <div v-else-if="error" class="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-800">{{ error }}</div>
                                <div v-else>
                                    <div v-if="mainSubjects.length" class="overflow-x-auto">
                                        <table class="min-w-full text-sm">
                                            <thead>
                                                <tr class="border-b bg-slate-100 text-left text-xs font-extrabold text-slate-700">
                                                    <th class="px-3 py-2 w-10"></th>
                                                    <th class="px-3 py-2">Subject Name</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(s, idx) in mainSubjects" :key="'ms-' + idx" class="border-b last:border-b-0">
                                                    <td class="px-3 py-2 text-center font-extrabold text-blue-700">✓</td>
                                                    <td class="px-3 py-2">{{ subjectName(s) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div v-else class="rounded-md border border-slate-200 bg-slate-50 p-4 text-center text-sm text-slate-700">No subjects</div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-md border border-slate-200 bg-white">
                            <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 text-sm font-extrabold text-slate-800">4TH / MAIN SUBJECT</div>
                            <div class="p-4">
                                <div v-if="loading" class="text-sm text-slate-600">Loading...</div>
                                <div v-else-if="error" class="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-800">{{ error }}</div>
                                <div v-else>
                                    <div v-if="assignedSubjects.length" class="overflow-x-auto">
                                        <table class="min-w-full text-sm">
                                            <tbody>
                                                <tr class="border-b bg-slate-50">
                                                    <td class="px-3 py-2 font-extrabold" colspan="2">4th Subject</td>
                                                </tr>
                                                <tr class="border-b">
                                                    <td class="px-3 py-2 w-10 text-center font-extrabold text-blue-700">✓</td>
                                                    <td class="px-3 py-2">
                                                        {{ fourthAssignedName }}
                                                    </td>
                                                </tr>

                                                <tr class="border-b bg-slate-50">
                                                    <td class="px-3 py-2 font-extrabold" colspan="2">Main Subjects</td>
                                                </tr>
                                                <tr v-for="(a, idx) in mainAssigned" :key="'ma-' + idx" class="border-b last:border-b-0">
                                                    <td class="px-3 py-2 w-10 text-center font-extrabold text-blue-700">✓</td>
                                                    <td class="px-3 py-2">{{ a.subject_name || '' }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div v-else>
                                        <div v-if="subjectAssign?.note" class="mb-3 border-b border-slate-200 pb-2 text-xs font-semibold text-red-600">{{ subjectAssign.note }}</div>

                                        <div class="mb-4">
                                            <div class="mb-2 text-sm font-semibold text-slate-700">Select 4th Subject</div>
                                            <select v-model="forthSubjectId" class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 text-sm outline-none">
                                                <option value="">--Select 4th Subject--</option>
                                                <option v-for="s in fourthSubjects" :key="'fs-' + s.subject_id" :value="String(s.subject_id)">{{ subjectName(s) }}</option>
                                            </select>
                                        </div>

                                        <div class="mb-2 text-sm font-semibold text-slate-700">Select Main Subject</div>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full text-sm">
                                                <thead>
                                                    <tr class="border-b bg-slate-100 text-left text-xs font-extrabold text-slate-700">
                                                        <th class="px-3 py-2 w-10"></th>
                                                        <th class="px-3 py-2">Subject Name</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-if="subjectLoader">
                                                        <td colspan="2" class="px-3 py-8 text-center text-sm text-slate-600">loading..</td>
                                                    </tr>
                                                    <tr v-for="(s, idx) in mainSubjectList" v-else :key="'ml-' + idx" class="border-b last:border-b-0">
                                                        <td class="px-3 py-2 text-center">
                                                            <input
                                                                type="checkbox"
                                                                v-model="s.checked"
                                                                :disabled="checkboxDisabled(s)"
                                                                @change="selectAdditionalSubject(s)"
                                                            />
                                                        </td>
                                                        <td class="px-3 py-2">{{ subjectName(s) }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="pt-5 text-center">
                                            <button
                                                type="button"
                                                class="rounded-md bg-emerald-600 px-10 py-2.5 text-sm font-extrabold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                                                :disabled="submitting"
                                                @click="submitAssign"
                                            >
                                                {{ submitting ? 'Processing...' : 'SUBMIT' }}
                                            </button>
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
import { computed, inject, onMounted, ref, watch } from 'vue'
import StudentSidebar from '../../components/student/StudentSidebar.vue'

const app = inject('app')

const loading = ref(false)
const error = ref('')
const subjectLoader = ref(false)
const submitting = ref(false)

const forthSubjectId = ref('')

const data = ref({ subjectAssign: null, assign_subjects: [] })

const subjectAssign = computed(() => data.value?.subjectAssign || null)
const details = computed(() => (Array.isArray(subjectAssign.value?.details) ? subjectAssign.value.details : []))
const assignedSubjects = computed(() => (Array.isArray(data.value?.assign_subjects) ? data.value.assign_subjects : []))

const mainSubjects = computed(() => details.value.filter((e) => !Number(e?.fourth_subject || 0) && !Number(e?.main_subject || 0)))
const fourthSubjects = computed(() => details.value.filter((e) => Number(e?.fourth_subject || 0)))

const exceptSubjects = ref([])
const mainSubjectList = ref([])
const disabled = ref(false)

const fourthAssignedName = computed(() => {
    const f = assignedSubjects.value.find((x) => Number(x?.main_subject || 0) === 0)
    return f?.subject_name || ''
})

const mainAssigned = computed(() => assignedSubjects.value.filter((x) => Number(x?.main_subject || 0) === 1))

function subjectName(row) {
    return row?.subject?.name_en || row?.subject_name || ''
}

function checkboxDisabled(s) {
    if (!s) return true
    const sid = Number(s.subject_id || 0)
    if (!sid) return true
    if (exceptSubjects.value.includes(sid)) return true
    if (!s.checked && disabled.value) return true
    return false
}

function dataRender() {
    const arr = [...mainSubjectList.value]
    mainSubjectList.value = []
    mainSubjectList.value = arr
}

function selectAdditionalSubject(row) {
    if (!row) return

    if (row.except_subject_id) {
        const ex = Number(row.except_subject_id)
        if (exceptSubjects.value.includes(ex)) {
            exceptSubjects.value = exceptSubjects.value.filter((x) => x !== ex)
        } else {
            exceptSubjects.value.push(ex)
        }
    }

    setTimeout(() => {
        const count = mainSubjectList.value.filter((e) => e.checked).length
        const need = Number(subjectAssign.value?.main_subject || 0)
        disabled.value = need > 0 ? count === need : false
        dataRender()
    }, 50)
}

function rebuildMainSubjectList() {
    if (!forthSubjectId.value || !subjectAssign.value) {
        mainSubjectList.value = []
        return
    }

    exceptSubjects.value = [Number(forthSubjectId.value)]
    disabled.value = false
    mainSubjectList.value = []

    const subjectLists = details.value
    const find = subjectLists.find((e) => String(e.subject_id) === String(forthSubjectId.value))
    if (!find) return

    subjectLoader.value = true

    const addSubjects = subjectLists.filter(
        (e) => Number(e.main_subject || 0) === 1 && String(e.subject_id) !== String(forthSubjectId.value) && String(e.subject_id) !== String(find.except_subject_id || '')
    )

    const subjects = addSubjects.map((e) => ({ ...e, checked: false }))

    setTimeout(() => {
        mainSubjectList.value = subjects
        subjectLoader.value = false
    }, 200)
}

async function load() {
    loading.value = true
    error.value = ''
    try {
        const res = await window.axios.get('/api/public/student/subjects')
        data.value = res?.data || { subjectAssign: null, assign_subjects: [] }
    } catch (e) {
        data.value = { subjectAssign: null, assign_subjects: [] }
        error.value = e?.response?.data?.message || 'Failed to load subjects.'
    } finally {
        loading.value = false
    }
}

async function submitAssign() {
    if (!forthSubjectId.value) {
        error.value = 'Please select 4th subject'
        return
    }

    const need = Number(subjectAssign.value?.main_subject || 0)
    const selected = mainSubjectList.value.filter((e) => e.checked)

    if (!selected.length) {
        error.value = 'Please select subject'
        return
    }

    if (need > 0 && selected.length !== need) {
        error.value = `Please select minimum ${need} subject`
        return
    }

    if (!confirm('Are you sure want to submit this subjects ?')) return

    const payload = [{ main_subject: 0, subject_id: Number(forthSubjectId.value) }].concat(
        selected.map((e) => ({ main_subject: 1, subject_id: Number(e.subject_id) })),
    )

    submitting.value = true
    error.value = ''
    try {
        const res = await window.axios.post('/api/public/student/subjects/assign', payload)
        await load()
        if (res?.data?.message) {
            error.value = ''
        }
    } catch (e) {
        error.value = e?.response?.data?.message || 'Something went wrong'
    } finally {
        submitting.value = false
    }
}

watch(
    () => forthSubjectId.value,
    () => {
        rebuildMainSubjectList()
    },
)

onMounted(async () => {
    await load()
})
</script>
