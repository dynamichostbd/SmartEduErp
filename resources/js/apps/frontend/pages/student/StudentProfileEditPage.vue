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
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-sm border border-slate-300 bg-slate-50 px-4 py-3">
                        <div class="text-sm font-extrabold text-slate-800">EDIT PROFILE</div>
                        <button type="button" class="inline-flex items-center gap-2 rounded-sm bg-emerald-600 px-3 py-1.5 text-xs font-extrabold text-white hover:bg-emerald-700" @click="go('/student/profile')">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 12H5" />
                                <path d="M12 19l-7-7 7-7" />
                            </svg>
                            BACK
                        </button>
                    </div>

                    <div v-if="studentProfileSaveError" class="mt-4 rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-800">{{ studentProfileSaveError }}</div>
                    <div v-if="studentProfileSaveSuccess" class="mt-4 rounded-sm border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">{{ studentProfileSaveSuccess }}</div>

                    <form class="mt-4" @submit.prevent="submitStudentProfileUpdate">
                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                            <div>
                                <label class="text-sm font-bold text-slate-700">Full Name</label>
                                <input v-model="studentProfileEditForm.name" type="text" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10" required />

                                <div class="mt-4">
                                    <label class="text-sm font-bold text-slate-700">Gender</label>
                                    <div class="mt-2 flex flex-wrap items-center gap-4 text-sm font-semibold text-slate-700">
                                        <label class="inline-flex items-center gap-2"><input v-model="studentProfileEditForm.gender" type="radio" value="Male" /> Male</label>
                                        <label class="inline-flex items-center gap-2"><input v-model="studentProfileEditForm.gender" type="radio" value="Female" /> Female</label>
                                        <label class="inline-flex items-center gap-2"><input v-model="studentProfileEditForm.gender" type="radio" value="Others" /> Others</label>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <label class="text-sm font-bold text-slate-700">Blood Group</label>
                                    <select v-model="studentProfileEditForm.blood_group" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10">
                                        <option value="">--Select One--</option>
                                        <option v-for="bg in (studentProfileSystems?.blood_groups || [])" :key="'bg-' + bg" :value="bg">{{ bg }}</option>
                                    </select>
                                </div>

                                <div class="mt-4">
                                    <label class="text-sm font-bold text-slate-700">DOB</label>
                                    <input v-model="studentProfileEditForm.dob" type="date" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10" />
                                </div>

                                <div class="mt-4">
                                    <label class="text-sm font-bold text-slate-700">Religion</label>
                                    <select v-model="studentProfileEditForm.religion" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10">
                                        <option value="">--Select One--</option>
                                        <option v-for="r in (studentProfileSystems?.religions || [])" :key="'r-' + r" :value="r">{{ r }}</option>
                                    </select>
                                </div>

                                <div class="mt-4">
                                    <label class="text-sm font-bold text-slate-700">NID/Birth Reg No</label>
                                    <input v-model="studentProfileEditForm.nid" type="text" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10" />
                                </div>

                                <div class="mt-4">
                                    <label class="text-sm font-bold text-slate-700">Father's Name</label>
                                    <input v-model="studentProfileEditForm.fathers_name" type="text" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10" />
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-bold text-slate-700">Registration No</label>
                                <input v-model="studentProfileEditForm.reg_no" type="text" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10" />

                                <div class="mt-4">
                                    <label class="text-sm font-bold text-slate-700">SSC GPA</label>
                                    <input v-model="studentProfileEditForm.ssc_gpa" type="text" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10" />
                                </div>

                                <div class="mt-4">
                                    <label class="text-sm font-bold text-slate-700">Hostel</label>
                                    <select v-model="studentProfileEditForm.hostel_id" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10">
                                        <option value="">--Select One--</option>
                                        <option v-for="h in (studentProfileSystems?.hostels || [])" :key="'h-' + h.id" :value="String(h.id)">{{ h.name }}</option>
                                    </select>
                                </div>

                                <div class="mt-4">
                                    <label class="text-sm font-bold text-slate-700">Hostel Room No</label>
                                    <input v-model="studentProfileEditForm.hostel_room_no" type="text" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10" />
                                </div>

                                <div class="mt-4">
                                    <label class="text-sm font-bold text-slate-700">Guardian Type</label>
                                    <div class="mt-2 flex flex-wrap items-center gap-4 text-sm font-semibold text-slate-700">
                                        <label class="inline-flex items-center gap-2"><input v-model="studentProfileEditForm.guardian_type" type="radio" value="Father" /> Father</label>
                                        <label class="inline-flex items-center gap-2"><input v-model="studentProfileEditForm.guardian_type" type="radio" value="Mother" /> Mother</label>
                                        <label class="inline-flex items-center gap-2"><input v-model="studentProfileEditForm.guardian_type" type="radio" value="Other" /> Other</label>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <label class="text-sm font-bold text-slate-700">Guardian Name</label>
                                    <input v-model="studentProfileEditForm.guardian_name" type="text" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-slate-50 px-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10" />
                                </div>

                                <div class="mt-4">
                                    <label class="text-sm font-bold text-slate-700">Guardian Mobile</label>
                                    <input v-model="studentProfileEditForm.guardian_mobile" type="text" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10" />
                                </div>

                                <div class="mt-4">
                                    <label class="text-sm font-bold text-slate-700">Guardian Relations</label>
                                    <input v-model="studentProfileEditForm.guardian_relations" type="text" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10" />
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-bold text-slate-700">Email</label>
                                <input v-model="studentProfileEditForm.email" type="email" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10" />

                                <div class="mt-4">
                                    <label class="text-sm font-bold text-slate-700">Present Address</label>
                                    <textarea v-model="studentProfileEditForm.address" rows="4" class="mt-1 w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10"></textarea>
                                </div>

                                <div class="mt-4">
                                    <label class="text-sm font-bold text-slate-700">Permanent Address</label>
                                    <textarea v-model="studentProfileEditForm.permanent_address" rows="4" class="mt-1 w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10"></textarea>
                                </div>

                                <div class="mt-4">
                                    <label class="text-sm font-bold text-slate-700">Picture</label>
                                    <input ref="studentProfileFileInput" type="file" accept="image/*" class="mt-1 block w-full text-sm" @change="onPickStudentProfileImage" />
                                </div>

                                <div class="mt-4">
                                    <label class="text-sm font-bold text-slate-700">Passing Year</label>
                                    <input v-model="studentProfileEditForm.passing_year" type="text" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10" />
                                </div>

                                <div class="mt-4">
                                    <label class="text-sm font-bold text-slate-700">Nationality</label>
                                    <input v-model="studentProfileEditForm.nationality" type="text" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10" />
                                </div>

                                <div class="mt-4">
                                    <label class="text-sm font-bold text-slate-700">Extra Curricular Activity</label>
                                    <select v-model="studentProfileEditForm.extra_curricular_activity" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10">
                                        <option value="">--Select One--</option>
                                        <option value="STUDY">STUDY</option>
                                        <option value="Cultural activities">Cultural activities</option>
                                        <option value="Drawing and graffiti">Drawing and graffiti</option>
                                        <option value="Debate and public speaking">Debate and public speaking</option>
                                        <option value="Sports and physical exercise">Sports and physical exercise</option>
                                        <option value="Social service activities">Social service activities</option>
                                    </select>
                                </div>

                                <div class="mt-4">
                                    <label class="text-sm font-bold text-slate-700">Quota</label>
                                    <div class="mt-2 flex flex-wrap items-center gap-4 text-sm font-semibold text-slate-700">
                                        <label class="inline-flex items-center gap-2"><input v-model="studentProfileEditForm.quota" type="radio" value="Yes" /> Yes</label>
                                        <label class="inline-flex items-center gap-2"><input v-model="studentProfileEditForm.quota" type="radio" value="No" /> No</label>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <label class="text-sm font-bold text-slate-700">Marital Status</label>
                                    <div class="mt-2 flex flex-wrap items-center gap-4 text-sm font-semibold text-slate-700">
                                        <label class="inline-flex items-center gap-2"><input v-model="studentProfileEditForm.marital_status" type="radio" value="Married" /> Married</label>
                                        <label class="inline-flex items-center gap-2"><input v-model="studentProfileEditForm.marital_status" type="radio" value="Unmarried" /> Unmarried</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 flex items-center justify-end gap-3">
                            <button type="button" class="rounded-sm border border-slate-300 bg-white px-6 py-2.5 text-sm font-extrabold text-slate-700 hover:bg-slate-50" @click="go('/student/profile')">Cancel</button>
                            <button type="submit" class="rounded-sm bg-emerald-600 px-8 py-2.5 text-sm font-extrabold text-white hover:bg-emerald-700" :disabled="studentProfileSaving">{{ studentProfileSaving ? 'Saving...' : 'Save Changes' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, inject, onMounted, ref } from 'vue'

const app = inject('app')

const studentProfileFileInput = ref(null)

const studentMe = computed(() => app?.studentMe)
const studentSidebarActive = computed(() => app?.studentSidebarActive)
const studentAuthLoading = computed(() => app?.studentAuthLoading)

const studentProfileSystems = computed(() => app?.studentProfileSystems)
const studentProfileEditForm = computed(() => app?.studentProfileEditForm)
const studentProfileSaving = computed(() => app?.studentProfileSaving)
const studentProfileSaveError = computed(() => app?.studentProfileSaveError)
const studentProfileSaveSuccess = computed(() => app?.studentProfileSaveSuccess)

function go(href) {
    return app?.go?.(href)
}

function doStudentLogout() {
    return app?.doStudentLogout?.()
}

function onPickStudentProfileImage(e) {
    return app?.onPickStudentProfileImage?.(e)
}

function submitStudentProfileUpdate() {
    return app?.submitStudentProfileUpdate?.()
}

onMounted(() => {
    const input = studentProfileFileInput.value
    if (input) {
        try {
            input.value = ''
        } catch {
        }
    }
})
</script>
