<template>
    <div class="space-y-6">
        <!-- Main Header Card -->
        <div class="rounded-sm border border-slate-300 bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50/50 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#0d6b75] text-white shadow-sm">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 uppercase tracking-tight">Student Profile</h2>
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Personal & Academic Details</p>
                    </div>
                </div>
                <button
                    class="inline-flex items-center gap-2 rounded-md bg-[#0d6b75] px-5 py-2 text-xs font-bold text-white shadow-md transition-all hover:bg-[#0b5b63] active:scale-[0.98]"
                    @click="injectGo('/student/profile/edit')"
                >
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    EDIT PROFILE
                </button>
            </div>
            
            <div class="p-6">
                <div class="flex flex-col gap-8 lg:flex-row items-start">
                    <!-- Avatar Section -->
                    <div class="flex flex-col items-center gap-4 w-full lg:w-auto">
                        <div class="group relative h-48 w-48 overflow-hidden rounded-2xl border-4 border-white bg-slate-100 shadow-xl ring-1 ring-slate-200">
                            <img v-if="studentMe?.profile" :src="studentMe.profile" class="h-full w-full object-cover" alt="Profile" />
                            <div v-else class="flex h-full w-full items-center justify-center text-slate-300">
                                <svg class="h-20 w-20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-xl font-black text-slate-800 leading-tight">{{ studentMe?.name || '—' }}</div>
                            <div class="mt-1 inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-0.5 text-[11px] font-bold text-emerald-700 border border-emerald-100 uppercase tracking-wider">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                {{ studentMe?.status || 'Active' }}
                            </div>
                        </div>
                    </div>

                    <!-- Top Summary Grid -->
                    <div class="flex-1 w-full">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            <div v-for="field in topFields" :key="field.label" class="group border-b border-slate-100 pb-3 transition-colors hover:border-[#0d6b75]/30">
                                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 group-hover:text-[#0d6b75]">{{ field.label }}</div>
                                <div class="mt-1 text-sm font-bold text-slate-800">{{ field.value || '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Academic Information Card -->
            <div class="rounded-sm border border-slate-300 bg-white shadow-sm overflow-hidden flex flex-col">
                <div class="bg-slate-50 px-4 py-2.5 border-b border-slate-200 flex items-center gap-2">
                    <svg class="h-4 w-4 text-[#0d6b75]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10L12 5L2 10L12 15L22 10Z"></path><path d="M6 12V17C6 17 6 20 12 20C18 20 18 17 18 17V12"></path></svg>
                    <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider">Academic Information</h3>
                </div>
                <div class="flex-1">
                    <table class="w-full text-sm">
                        <tbody>
                            <tr v-for="(field, idx) in academicFields" :key="'ac-' + idx" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                                <th class="w-1/3 bg-slate-50/50 px-4 py-2.5 text-left text-[11px] font-bold text-slate-500 uppercase tracking-tight border-r border-slate-100">{{ field.label }}</th>
                                <td class="px-4 py-2.5 font-bold text-slate-800">{{ field.value || '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Student Information Card (Personal) -->
            <div class="rounded-sm border border-slate-300 bg-white shadow-sm overflow-hidden flex flex-col">
                <div class="bg-slate-50 px-4 py-2.5 border-b border-slate-200 flex items-center gap-2">
                    <svg class="h-4 w-4 text-[#0d6b75]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z"></path><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path></svg>
                    <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider">Personal Information</h3>
                </div>
                <div class="flex-1">
                    <table class="w-full text-sm">
                        <tbody>
                            <tr v-for="(field, idx) in personalFields" :key="'ps-' + idx" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                                <th class="w-1/3 bg-slate-50/50 px-4 py-2.5 text-left text-[11px] font-bold text-slate-500 uppercase tracking-tight border-r border-slate-100">{{ field.label }}</th>
                                <td class="px-4 py-2.5 font-bold text-slate-800">{{ field.value || '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Guardian Information Card -->
            <div class="rounded-sm border border-slate-300 bg-white shadow-sm overflow-hidden">
                <div class="bg-slate-50 px-4 py-2.5 border-b border-slate-200 flex items-center gap-2">
                    <svg class="h-4 w-4 text-[#0d6b75]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider">Guardian Information</h3>
                </div>
                <table class="w-full text-sm">
                    <tbody>
                        <tr v-for="(field, idx) in guardianFields" :key="'gd-' + idx" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                            <th class="w-1/3 bg-slate-50/50 px-4 py-2.5 text-left text-[11px] font-bold text-slate-500 uppercase tracking-tight border-r border-slate-100">{{ field.label }}</th>
                            <td class="px-4 py-2.5 font-bold text-slate-800">{{ field.value || '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Contact Credentials Card -->
            <div class="rounded-sm border border-slate-300 bg-white shadow-sm overflow-hidden">
                <div class="bg-slate-50 px-4 py-2.5 border-b border-slate-200 flex items-center gap-2">
                    <svg class="h-4 w-4 text-[#0d6b75]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider">Contact Credentials</h3>
                </div>
                <table class="w-full text-sm">
                    <tbody>
                        <tr v-for="(field, idx) in contactFields" :key="'ct-' + idx" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                            <th class="w-1/3 bg-slate-50/50 px-4 py-2.5 text-left text-[11px] font-bold text-slate-500 uppercase tracking-tight border-r border-slate-100">{{ field.label }}</th>
                            <td class="px-4 py-2.5 font-bold text-slate-800">{{ field.value || '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Other's Information Card -->
            <div class="rounded-sm border border-slate-300 bg-white shadow-sm overflow-hidden lg:col-span-2">
                <div class="bg-slate-50 px-4 py-2.5 border-b border-slate-200 flex items-center gap-2">
                    <svg class="h-4 w-4 text-[#0d6b75]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider">Other's Information</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2">
                    <div v-for="(field, idx) in otherFields" :key="'ot-' + idx" class="flex border-b border-slate-100 odd:border-r hover:bg-slate-50/50 transition-colors">
                        <th class="w-1/3 bg-slate-50/50 px-4 py-2.5 text-left text-[11px] font-bold text-slate-500 uppercase tracking-tight border-r border-slate-100">{{ field.label }}</th>
                        <td class="flex-1 px-4 py-2.5 font-bold text-slate-800 text-sm">{{ field.value || '—' }}</td>
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

const formatDate = (dateStr) => {
    if (!dateStr) return '—'
    try {
        const date = new Date(dateStr)
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'long', year: 'numeric' })
    } catch (e) {
        return dateStr
    }
}

const topFields = computed(() => {
    const s = studentMe.value || {}
    const sys = studentProfileSystems.value || {}
    return [
        { label: 'Software ID', value: s.student_id },
        { label: 'College Roll', value: s.college_roll },
        { label: 'Academic Session', value: getNameById(sys.academic_sessions, s.academic_session_id) },
        { label: 'Department/Group', value: getNameById(sys.departments, s.department_id) },
        { label: 'Academic Level', value: getNameById(sys.academic_qualifications, s.academic_qualification_id) },
        { label: 'Academic Class', value: getNameById(sys.academic_classes, s.academic_class_id) },
    ]
})

const personalFields = computed(() => {
    const s = studentMe.value || {}
    return [
        { label: 'Full Name', value: s.name },
        { label: 'Father\'s Name', value: s.fathers_name },
        { label: 'Mother\'s Name', value: s.mothers_name },
        { label: 'Gender', value: s.gender },
        { label: 'Date of Birth', value: formatDate(s.dob) },
        { label: 'Religion', value: s.religion },
        { label: 'Blood Group', value: s.blood_group },
        { label: 'NID / Birth Reg. No.', value: s.nid },
    ]
})

const academicFields = computed(() => {
    const s = studentMe.value || {}
    const sys = studentProfileSystems.value || {}
    return [
        { label: 'Software ID', value: s.student_id },
        { label: 'College Roll', value: s.college_roll },
        { label: 'SSC GPA', value: s.ssc_gpa },
        { label: 'Academic Session', value: getNameById(sys.academic_sessions, s.academic_session_id) },
        { label: 'Department/Group', value: getNameById(sys.departments, s.department_id) },
        { label: 'Academic Level', value: getNameById(sys.academic_qualifications, s.academic_qualification_id) },
        { label: 'Academic Class', value: getNameById(sys.academic_classes, s.academic_class_id) },
        { label: 'Student Type', value: s.student_type },
        { label: 'Admission ID', value: s.admission_id },
        { label: 'Registration No', value: s.reg_no },
        { label: 'Readmission Roll', value: s.readmission_college_roll },
        { label: 'Hostel Name', value: getNameById(sys.hostels, s.hostel_id) },
        { label: 'Hostel Room No.', value: s.hostel_room_no },
    ]
})

const guardianFields = computed(() => {
    const s = studentMe.value || {}
    return [
        { label: 'Guardian Type', value: s.guardian_type },
        { label: 'Guardian Name', value: s.guardian_name },
        { label: 'Guardian Mobile', value: s.guardian_mobile },
        { label: 'Guardian Relations', value: s.guardian_relations },
    ]
})

const contactFields = computed(() => {
    const s = studentMe.value || {}
    return [
        { label: 'Mobile', value: s.mobile },
        { label: 'Email', value: s.email },
        { label: 'Address', value: s.address },
        { label: 'Permanent Address', value: s.permanent_address },
    ]
})

const otherFields = computed(() => {
    const s = studentMe.value || {}
    return [
        { label: 'Passing Year', value: s.passing_year },
        { label: 'Nationality', value: s.nationality },
        { label: 'Extra Curricular', value: s.extra_curricular_activity },
        { label: 'Quota', value: s.quota },
        { label: 'Marital Status', value: s.marital_status },
    ]
})
</script>

<style scoped>
/* Optional: specific tweaks for table cells */
th {
    user-select: none;
}
</style>
