<template>
    <div class="mt-3 rounded-sm border border-slate-300 bg-white p-4 sm:p-6">
        <div class="mx-auto w-full max-w-6xl">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-3">
                    <div class="overflow-hidden rounded-sm border border-slate-300 bg-[#3f4b7a] text-white">
                        <div class="border-b border-white/15 px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 overflow-hidden rounded-sm bg-white/10">
                                    <img v-if="studentMe?.profile" :src="studentMe.profile" class="h-full w-full object-cover" />
                                    <div v-else class="flex h-full w-full items-center justify-center text-xs font-extrabold">{{ (studentMe?.student_id || 'NA').slice(0, 2) }}</div>
                                </div>
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-extrabold">{{ (studentMe?.name || '').toUpperCase() }}</div>
                                    <div class="mt-1 text-xs font-semibold opacity-90">
                                        <span v-if="studentMe?.student_id">{{ studentMe.student_id }}</span>
                                        <span v-else>—</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="py-2">
                            <a href="/dashboard" class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition" :class="studentSidebarActive === 'dashboard' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'">
                                <span v-if="studentSidebarActive === 'dashboard'" class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 11l9-8 9 8" />
                                    <path d="M5 10v10h5v-6h4v6h5V10" />
                                </svg>
                                <span>Dashboard</span>
                            </a>

                            <a href="/student/profile" class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition" :class="studentSidebarActive === 'profile' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'">
                                <span v-if="studentSidebarActive === 'profile'" class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21a8 8 0 10-16 0" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                                <span>My Profile</span>
                            </a>

                            <a href="/student/pay-now" class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition" :class="studentSidebarActive === 'pay' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'">
                                <span v-if="studentSidebarActive === 'pay'" class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="5" width="20" height="14" rx="2" />
                                    <path d="M2 10h20" />
                                </svg>
                                <span>Pay Now</span>
                            </a>

                            <div class="my-2 border-t border-white/10"></div>

                            <a href="/student/payment-history" class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition" :class="studentSidebarActive === 'history' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'">
                                <span v-if="studentSidebarActive === 'history'" class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 12a9 9 0 101-4" />
                                    <path d="M3 4v4h4" />
                                    <path d="M12 7v5l3 3" />
                                </svg>
                                <span>Payment History</span>
                            </a>
                            <a href="/student/fees" class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition" :class="studentSidebarActive === 'fees' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'">
                                <span v-if="studentSidebarActive === 'fees'" class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M8 6h13" />
                                    <path d="M8 12h13" />
                                    <path d="M8 18h13" />
                                    <path d="M3 6h.01" />
                                    <path d="M3 12h.01" />
                                    <path d="M3 18h.01" />
                                </svg>
                                <span>Fees List</span>
                            </a>
                            <a href="/student/admit-card" class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition" :class="studentSidebarActive === 'admit' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'">
                                <span v-if="studentSidebarActive === 'admit'" class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 3H6a2 2 0 00-2 2v14a2 2 0 002 2h12a2 2 0 002-2V9z" />
                                    <path d="M14 3v6h6" />
                                    <path d="M8 13h8" />
                                    <path d="M8 17h6" />
                                </svg>
                                <span>Admit Card</span>
                            </a>
                            <a href="/student/result" class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition" :class="studentSidebarActive === 'result' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'">
                                <span v-if="studentSidebarActive === 'result'" class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 3v18h18" />
                                    <path d="M7 14l4-4 3 3 5-6" />
                                </svg>
                                <span>Result</span>
                            </a>
                            <a href="/student/subjects" class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition" :class="studentSidebarActive === 'subject' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'">
                                <span v-if="studentSidebarActive === 'subject'" class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 19.5A2.5 2.5 0 016.5 17H20" />
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z" />
                                </svg>
                                <span>Subject Choice</span>
                            </a>
                            <a href="/student/change-password" class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition" :class="studentSidebarActive === 'password' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'">
                                <span v-if="studentSidebarActive === 'password'" class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" />
                                    <path d="M7 11V7a5 5 0 0110 0v4" />
                                </svg>
                                <span>Change Password</span>
                            </a>
                        </div>
                        <div class="border-t border-white/15 px-4 py-3">
                            <button type="button" class="w-full rounded-sm bg-white/10 px-4 py-2.5 text-sm font-extrabold transition hover:bg-white/20" @click="doStudentLogout" :disabled="studentAuthLoading">Logout</button>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-9">
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <div class="rounded-sm border border-slate-300 bg-white">
                            <div class="flex items-center justify-between gap-3 border-b border-slate-300 bg-slate-50 px-4 py-3">
                                <div class="text-sm font-extrabold text-slate-800">STUDENT INFORMATION</div>
                                <button type="button" class="inline-flex items-center gap-2 rounded-sm bg-emerald-600 px-3 py-1.5 text-xs font-extrabold text-white hover:bg-emerald-700" @click="go('/student/profile/edit')">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 20h9" />
                                        <path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4 12.5-12.5z" />
                                    </svg>
                                    EDIT PROFILE
                                </button>
                            </div>
                            <div class="p-4">
                                <div class="mb-4 flex items-center gap-3">
                                    <div class="h-24 w-24 overflow-hidden rounded-sm border border-slate-300 bg-slate-50">
                                        <img v-if="studentMe?.profile" :src="studentMe.profile" class="h-full w-full object-cover" />
                                        <div v-else class="flex h-full w-full items-center justify-center text-sm font-extrabold text-slate-500">NA</div>
                                    </div>
                                </div>

                                <div class="overflow-hidden rounded-sm border border-slate-300">
                                    <table class="min-w-full text-sm">
                                        <tbody>
                                            <tr class="border-b last:border-b-0 even:bg-slate-50">
                                                <td class="w-44 px-3 py-2 font-extrabold">Full Name</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentMe?.name || '' }}</td>
                                            </tr>
                                            <tr class="border-b last:border-b-0 even:bg-slate-50">
                                                <td class="px-3 py-2 font-extrabold">Father's Name</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentMe?.fathers_name || '' }}</td>
                                            </tr>
                                            <tr class="border-b last:border-b-0 even:bg-slate-50">
                                                <td class="px-3 py-2 font-extrabold">Mother's Name</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentMe?.mothers_name || '' }}</td>
                                            </tr>
                                            <tr class="border-b last:border-b-0 even:bg-slate-50">
                                                <td class="px-3 py-2 font-extrabold">Gender</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentMe?.gender || '' }}</td>
                                            </tr>
                                            <tr class="border-b last:border-b-0 even:bg-slate-50">
                                                <td class="px-3 py-2 font-extrabold">Date of Birth</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentMe?.dob || '' }}</td>
                                            </tr>
                                            <tr class="border-b last:border-b-0 even:bg-slate-50">
                                                <td class="px-3 py-2 font-extrabold">Religion</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentMe?.religion || '' }}</td>
                                            </tr>
                                            <tr class="border-b last:border-b-0 even:bg-slate-50">
                                                <td class="px-3 py-2 font-extrabold">Blood Group</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentMe?.blood_group || '' }}</td>
                                            </tr>
                                            <tr class="even:bg-slate-50">
                                                <td class="px-3 py-2 font-extrabold">NID / Birth Reg. No.</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentMe?.nid || '' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-sm border border-slate-300 bg-white">
                            <div class="border-b border-slate-300 bg-slate-50 px-4 py-3 text-sm font-extrabold text-slate-800">ACADEMIC INFORMATION</div>
                            <div class="p-4">
                                <div v-if="studentProfileSystemsLoading" class="text-sm text-slate-600">Loading...</div>
                                <div v-else class="overflow-hidden rounded-sm border border-slate-300">
                                    <table class="min-w-full text-sm">
                                        <tbody>
                                            <tr class="border-b last:border-b-0 even:bg-slate-50">
                                                <td class="w-48 px-3 py-2 font-extrabold">Software ID</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentMe?.student_id || '' }}</td>
                                            </tr>
                                            <tr class="border-b last:border-b-0 even:bg-slate-50">
                                                <td class="px-3 py-2 font-extrabold">College Roll</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentMe?.college_roll || '' }}</td>
                                            </tr>
                                            <tr class="border-b last:border-b-0 even:bg-slate-50">
                                                <td class="px-3 py-2 font-extrabold">SSC GPA</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentMe?.ssc_gpa || '' }}</td>
                                            </tr>
                                            <tr class="border-b last:border-b-0 even:bg-slate-50">
                                                <td class="px-3 py-2 font-extrabold">Academic Session</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentProfileNameById(studentProfileSystems?.academic_sessions, studentMe?.academic_session_id) }}</td>
                                            </tr>
                                            <tr class="border-b last:border-b-0 even:bg-slate-50">
                                                <td class="px-3 py-2 font-extrabold">Department/Group</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentProfileNameById(studentProfileSystems?.departments, studentMe?.department_id) }}</td>
                                            </tr>
                                            <tr class="border-b last:border-b-0 even:bg-slate-50">
                                                <td class="px-3 py-2 font-extrabold">Academic Level</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentProfileNameById(studentProfileSystems?.academic_qualifications, studentMe?.academic_qualification_id) }}</td>
                                            </tr>
                                            <tr class="border-b last:border-b-0 even:bg-slate-50">
                                                <td class="px-3 py-2 font-extrabold">Academic Class</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentProfileNameById(studentProfileSystems?.academic_classes, studentMe?.academic_class_id) }}</td>
                                            </tr>
                                            <tr class="border-b last:border-b-0 even:bg-slate-50">
                                                <td class="px-3 py-2 font-extrabold">Student Type</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentMe?.student_type || '' }}</td>
                                            </tr>
                                            <tr class="border-b last:border-b-0 even:bg-slate-50">
                                                <td class="px-3 py-2 font-extrabold">Admission ID</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentMe?.admission_id || '' }}</td>
                                            </tr>
                                            <tr class="border-b last:border-b-0 even:bg-slate-50">
                                                <td class="px-3 py-2 font-extrabold">Registration No</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentMe?.reg_no || '' }}</td>
                                            </tr>
                                            <tr class="border-b last:border-b-0 even:bg-slate-50">
                                                <td class="px-3 py-2 font-extrabold">Readmission College Roll</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentMe?.readmission_college_roll || '' }}</td>
                                            </tr>
                                            <tr class="border-b last:border-b-0 even:bg-slate-50">
                                                <td class="px-3 py-2 font-extrabold">Hostel Name</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentProfileNameById(studentProfileSystems?.hostels, studentMe?.hostel_id) }}</td>
                                            </tr>
                                            <tr class="even:bg-slate-50">
                                                <td class="px-3 py-2 font-extrabold">Hostel Room No.</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentMe?.hostel_room_no || '' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <div class="rounded-sm border border-slate-300 bg-white">
                            <div class="border-b border-slate-300 bg-slate-50 px-4 py-3 text-sm font-extrabold text-slate-800">GUARDIAN INFORMATION</div>
                            <div class="p-4">
                                <div class="overflow-hidden rounded-sm border border-slate-300">
                                    <table class="min-w-full text-sm">
                                        <tbody>
                                            <tr class="border-b last:border-b-0 even:bg-slate-50">
                                                <td class="w-44 px-3 py-2 font-extrabold">Guardian Type</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentMe?.guardian_type || '' }}</td>
                                            </tr>
                                            <tr class="border-b last:border-b-0 even:bg-slate-50">
                                                <td class="px-3 py-2 font-extrabold">Guardian Name</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentMe?.guardian_name || '' }}</td>
                                            </tr>
                                            <tr class="border-b last:border-b-0 even:bg-slate-50">
                                                <td class="px-3 py-2 font-extrabold">Guardian Mobile</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentMe?.guardian_mobile || '' }}</td>
                                            </tr>
                                            <tr class="even:bg-slate-50">
                                                <td class="px-3 py-2 font-extrabold">Relations</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentMe?.guardian_relations || '' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-sm border border-slate-300 bg-white">
                            <div class="border-b border-slate-300 bg-slate-50 px-4 py-3 text-sm font-extrabold text-slate-800">CONTACT CREDENTIALS</div>
                            <div class="p-4">
                                <div class="overflow-hidden rounded-sm border border-slate-300">
                                    <table class="min-w-full text-sm">
                                        <tbody>
                                            <tr class="border-b last:border-b-0 even:bg-slate-50">
                                                <td class="w-44 px-3 py-2 font-extrabold">Mobile</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentMe?.mobile || '' }}</td>
                                            </tr>
                                            <tr class="border-b last:border-b-0 even:bg-slate-50">
                                                <td class="px-3 py-2 font-extrabold">Email</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentMe?.email || '' }}</td>
                                            </tr>
                                            <tr class="border-b last:border-b-0 even:bg-slate-50">
                                                <td class="px-3 py-2 font-extrabold">Present Address</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentMe?.address || '' }}</td>
                                            </tr>
                                            <tr class="even:bg-slate-50">
                                                <td class="px-3 py-2 font-extrabold">Permanent Address</td>
                                                <td class="px-3 py-2 font-semibold">{{ studentMe?.permanent_address || '' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-sm border border-slate-300 bg-white">
                        <div class="border-b border-slate-300 bg-slate-50 px-4 py-3 text-sm font-extrabold text-slate-800">OTHER'S INFORMATION</div>
                        <div class="p-4">
                            <div class="overflow-hidden rounded-sm border border-slate-300">
                                <table class="min-w-full text-sm">
                                    <tbody>
                                        <tr class="border-b last:border-b-0 even:bg-slate-50">
                                            <td class="w-60 px-3 py-2 font-extrabold">Passing Year</td>
                                            <td class="px-3 py-2 font-semibold">{{ studentMe?.passing_year || '' }}</td>
                                        </tr>
                                        <tr class="border-b last:border-b-0 even:bg-slate-50">
                                            <td class="px-3 py-2 font-extrabold">Nationality</td>
                                            <td class="px-3 py-2 font-semibold">{{ studentMe?.nationality || '' }}</td>
                                        </tr>
                                        <tr class="border-b last:border-b-0 even:bg-slate-50">
                                            <td class="px-3 py-2 font-extrabold">Extra Curricular Activity</td>
                                            <td class="px-3 py-2 font-semibold">{{ studentMe?.extra_curricular_activity || '' }}</td>
                                        </tr>
                                        <tr class="border-b last:border-b-0 even:bg-slate-50">
                                            <td class="px-3 py-2 font-extrabold">Quota</td>
                                            <td class="px-3 py-2 font-semibold">{{ studentMe?.quota || '' }}</td>
                                        </tr>
                                        <tr class="even:bg-slate-50">
                                            <td class="px-3 py-2 font-extrabold">Marital Status</td>
                                            <td class="px-3 py-2 font-semibold">{{ studentMe?.marital_status || '' }}</td>
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
</template>

<script setup>
import { computed, inject } from 'vue'

const app = inject('app')

const studentMe = computed(() => app?.studentMe)
const studentSidebarActive = computed(() => app?.studentSidebarActive)
const studentAuthLoading = computed(() => app?.studentAuthLoading)
const studentProfileSystemsLoading = computed(() => app?.studentProfileSystemsLoading)
const studentProfileSystems = computed(() => app?.studentProfileSystems)

function go(href) {
    return app?.go?.(href)
}

function studentSidebarGo(key, href) {
    return app?.studentSidebarGo?.(key, href)
}

function doStudentLogout() {
    return app?.doStudentLogout?.()
}

function studentProfileNameById(list, id) {
    return app?.studentProfileNameById?.(list, id)
}
</script>
