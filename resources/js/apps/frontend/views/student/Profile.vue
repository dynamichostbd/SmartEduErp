<template>
    <div class="rounded-sm border border-slate-300 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-200 p-6">
            <h2 class="text-xl font-bold text-slate-800">My Profile</h2>
            <button
                class="inline-flex items-center gap-2 rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-emerald-700"
                @click="injectGo('/student/profile/edit')"
            >
                Edit Profile
            </button>
        </div>
        <div class="p-6">
            <div class="flex flex-col gap-8 md:flex-row">
                <div class="flex flex-col items-center gap-4">
                    <div class="h-40 w-40 overflow-hidden rounded-xl border-4 border-slate-100 bg-slate-50 shadow-inner">
                        <img v-if="studentMe?.profile" :src="studentMe.profile" class="h-full w-full object-cover" />
                        <div v-else class="flex h-full w-full items-center justify-center text-slate-300 text-4xl">
                            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        </div>
                    </div>
                </div>
                <div class="flex-1 space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div v-for="field in profileFields" :key="field.label">
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ field.label }}</div>
                            <div class="mt-1 text-slate-900 font-medium">{{ field.value || '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { inject, computed } from 'vue'

const studentMe = inject('studentMe')
const studentProfileSystems = inject('studentProfileSystems', {})
const injectGo = inject('go')

const getNameById = (list, id) => {
    const needle = String(id || '')
    if (!needle) return ''
    const arr = Array.isArray(list) ? list : []
    const row = arr.find((x) => String(x?.id ?? '') === needle)
    return row?.name || ''
}

const profileFields = computed(() => {
    const s = studentMe.value || {}
    const sys = studentProfileSystems.value || {}
    return [
        { label: 'Name', value: s.name },
        { label: 'Mobile', value: s.mobile },
        { label: 'Email', value: s.email },
        { label: 'Reg No.', value: s.reg_no },
        { label: 'Admission ID', value: s.admission_id },
        { label: 'Roll', value: s.college_roll },
        { label: 'Qualification', value: getNameById(sys.academic_qualifications, s.academic_qualification_id) },
        { label: 'Session', value: getNameById(sys.academic_sessions, s.academic_session_id) },
        { label: 'Class', value: getNameById(sys.academic_classes, s.academic_class_id) },
        { label: 'Department', value: getNameById(sys.departments, s.department_id) },
        { label: 'Gender', value: s.gender },
        { label: 'Blood Group', value: s.blood_group },
        { label: 'Date of Birth', value: s.dob },
        { label: 'Religion', value: s.religion },
        { label: 'Father\'s Name', value: s.fathers_name },
        { label: 'Mother\'s Name', value: s.mothers_name },
        { label: 'Present Address', value: s.address },
        { label: 'Permanent Address', value: s.permanent_address },
    ]
})
</script>
