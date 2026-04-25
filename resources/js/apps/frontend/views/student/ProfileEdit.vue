<template>
    <div class="rounded-sm border border-slate-300 bg-white shadow-sm">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-200 p-4 sm:p-6">
            <h2 class="text-xl font-bold text-slate-800">Edit Profile</h2>
            <div class="flex gap-2">
                <button
                    type="button"
                    class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                    @click="injectGo('/student/profile')"
                >Cancel</button>
                <button
                    type="button"
                    class="rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60"
                    :disabled="studentProfileSaving"
                    @click="injectSubmitStudentProfileUpdate"
                >{{ studentProfileSaving ? 'Saving...' : 'Save Changes' }}</button>
            </div>
        </div>

        <div class="p-4 sm:p-6">
            <!-- Error -->
            <div v-if="studentProfileSaveError" class="mb-6 rounded-sm border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                {{ studentProfileSaveError }}
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

                <!-- ── Profile Picture Column ─────────────────── -->
                <div class="lg:col-span-3 flex flex-col items-center gap-4">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Profile Picture</div>
                    <div class="relative h-44 w-44 overflow-hidden rounded-xl border-4 border-slate-100 bg-slate-50 shadow-inner">
                        <img v-if="studentProfileImageFile" :src="profilePreview" class="h-full w-full object-cover" />
                        <img v-else-if="studentMe?.profile" :src="studentMe.profile" class="h-full w-full object-cover" />
                        <div v-else class="flex h-full w-full items-center justify-center text-slate-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                    </div>
                    <label class="cursor-pointer rounded-sm border border-emerald-500 px-4 py-1.5 text-sm font-semibold text-emerald-600 hover:bg-emerald-50">
                        Change Photo
                        <input type="file" class="hidden" accept="image/*" @change="onPickStudentProfileImage" />
                    </label>
                    <div class="text-center text-xs text-slate-400">Passport size, jpg/png, max 5MB</div>
                </div>

                <!-- ── Fields Area ────────────────────────────── -->
                <div class="lg:col-span-9 space-y-6">

                    <!-- Section: Personal Information -->
                    <div>
                        <div class="mb-3 border-b border-slate-100 pb-1 text-sm font-bold text-slate-700">Personal Information</div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Full Name <span class="text-red-500">*</span></label>
                                <input v-model="studentProfileEditForm.name" type="text" placeholder="Full Name" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30" />
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Mobile</label>
                                <input v-model="studentProfileEditForm.mobile" type="text" placeholder="01XXXXXXXXX" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30" />
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Email</label>
                                <input v-model="studentProfileEditForm.email" type="email" placeholder="Email" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30" />
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Gender</label>
                                <div class="flex flex-wrap gap-4 pt-1">
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                        <input type="radio" v-model="studentProfileEditForm.gender" value="Male" class="accent-emerald-600" /> Male
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                        <input type="radio" v-model="studentProfileEditForm.gender" value="Female" class="accent-emerald-600" /> Female
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                        <input type="radio" v-model="studentProfileEditForm.gender" value="Others" class="accent-emerald-600" /> Others
                                    </label>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Blood Group</label>
                                <select v-model="studentProfileEditForm.blood_group" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30">
                                    <option value="">-- Select --</option>
                                    <option v-for="bg in bloodGroups" :key="bg" :value="bg">{{ bg }}</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Date of Birth</label>
                                <input v-model="studentProfileEditForm.dob" type="date" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30" />
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Religion</label>
                                <select v-model="studentProfileEditForm.religion" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30">
                                    <option value="">-- Select --</option>
                                    <option v-for="r in religions" :key="r" :value="r">{{ r }}</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">NID / Birth Reg No</label>
                                <input v-model="studentProfileEditForm.nid" type="text" placeholder="NID / Birth Reg No" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30" />
                            </div>

                        </div>
                    </div>

                    <!-- Section: Academic Information -->
                    <div>
                        <div class="mb-3 border-b border-slate-100 pb-1 text-sm font-bold text-slate-700">Academic Information</div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Registration No</label>
                                <input v-model="studentProfileEditForm.reg_no" type="text" placeholder="Registration No" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30" />
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">SSC GPA</label>
                                <input v-model="studentProfileEditForm.ssc_gpa" type="text" placeholder="e.g. 4.50" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30" />
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Passing Year</label>
                                <input v-model="studentProfileEditForm.passing_year" type="text" placeholder="e.g. 2023" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30" />
                            </div>

                        </div>
                    </div>

                    <!-- Section: Address -->
                    <div>
                        <div class="mb-3 border-b border-slate-100 pb-1 text-sm font-bold text-slate-700">Address</div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Present Address <small class="normal-case font-normal">(Vill, Post, Upozila, Dist)</small></label>
                                <textarea v-model="studentProfileEditForm.address" rows="3" placeholder="Present Address" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30"></textarea>
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Permanent Address <small class="normal-case font-normal">(Vill, Post, Upozila, Dist)</small></label>
                                <textarea v-model="studentProfileEditForm.permanent_address" rows="3" placeholder="Permanent Address" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30"></textarea>
                            </div>

                        </div>
                    </div>

                    <!-- Section: Family Information -->
                    <div>
                        <div class="mb-3 border-b border-slate-100 pb-1 text-sm font-bold text-slate-700">Family Information</div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Father's Name</label>
                                <input v-model="studentProfileEditForm.fathers_name" type="text" placeholder="Father's Name" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30" />
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Mother's Name</label>
                                <input v-model="studentProfileEditForm.mothers_name" type="text" placeholder="Mother's Name" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30" />
                            </div>

                        </div>
                    </div>

                    <!-- Section: Guardian Information -->
                    <div>
                        <div class="mb-3 border-b border-slate-100 pb-1 text-sm font-bold text-slate-700">Guardian Information</div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Guardian Type</label>
                                <div class="flex flex-wrap gap-4 pt-1">
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                        <input type="radio" v-model="studentProfileEditForm.guardian_type" value="Father" @change="onGuardianTypeChange" class="accent-emerald-600" /> Father
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                        <input type="radio" v-model="studentProfileEditForm.guardian_type" value="Mother" @change="onGuardianTypeChange" class="accent-emerald-600" /> Mother
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                        <input type="radio" v-model="studentProfileEditForm.guardian_type" value="Other" @change="onGuardianTypeChange" class="accent-emerald-600" /> Other
                                    </label>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Guardian Name</label>
                                <input v-model="studentProfileEditForm.guardian_name" type="text" :readonly="guardianRelationReadonly" placeholder="Guardian Name" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30 read-only:bg-slate-50" />
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Guardian Mobile</label>
                                <input v-model="studentProfileEditForm.guardian_mobile" type="text" placeholder="Guardian Mobile" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30" />
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Guardian Relations</label>
                                <input v-model="studentProfileEditForm.guardian_relations" type="text" :readonly="guardianRelationReadonly" placeholder="Relations" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30 read-only:bg-slate-50" />
                            </div>

                        </div>
                    </div>

                    <!-- Section: Hostel & Other -->
                    <div>
                        <div class="mb-3 border-b border-slate-100 pb-1 text-sm font-bold text-slate-700">Hostel & Other Information</div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Hostel</label>
                                <select v-model="studentProfileEditForm.hostel_id" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30">
                                    <option value="">-- Select One --</option>
                                    <option v-for="h in hostels" :key="h.id" :value="String(h.id)">{{ h.name }}</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Hostel Room No <small class="normal-case font-normal text-slate-400">(if known)</small></label>
                                <input v-model="studentProfileEditForm.hostel_room_no" type="text" placeholder="Hostel Room No" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30" />
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Nationality</label>
                                <input v-model="studentProfileEditForm.nationality" type="text" placeholder="Nationality" class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30" />
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Extra Curricular Activity</label>
                                <input v-model="studentProfileEditForm.extra_curricular_activity" type="text" placeholder="e.g. Sports, Debate..." class="w-full rounded-sm border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30" />
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Quota</label>
                                <div class="flex flex-wrap gap-4 pt-1">
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                        <input type="radio" v-model="studentProfileEditForm.quota" value="Yes" class="accent-emerald-600" /> Yes
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                        <input type="radio" v-model="studentProfileEditForm.quota" value="No" class="accent-emerald-600" /> No
                                    </label>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Marital Status</label>
                                <div class="flex flex-wrap gap-4 pt-1">
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                        <input type="radio" v-model="studentProfileEditForm.marital_status" value="Married" class="accent-emerald-600" /> Married
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                        <input type="radio" v-model="studentProfileEditForm.marital_status" value="Unmarried" class="accent-emerald-600" /> Unmarried
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Save Button (bottom) -->
                    <div class="flex justify-end border-t border-slate-100 pt-4">
                        <button
                            type="button"
                            class="rounded-sm bg-emerald-600 px-8 py-2.5 text-sm font-bold text-white hover:bg-emerald-700 disabled:opacity-60"
                            :disabled="studentProfileSaving"
                            @click="injectSubmitStudentProfileUpdate"
                        >{{ studentProfileSaving ? 'Saving...' : 'Save Changes' }}</button>
                    </div>

                </div><!-- end fields area -->
            </div>
        </div>
    </div>
</template>

<script setup>
import { inject, ref, computed } from 'vue'

const studentMe = inject('studentMe')
const studentProfileEditForm = inject('studentProfileEditForm')
const studentProfileSystems = inject('studentProfileSystems', {})
const studentProfileSaving = inject('studentProfileSaving')
const studentProfileSaveError = inject('studentProfileSaveError')
const injectSubmitStudentProfileUpdate = inject('submitStudentProfileUpdate')
const injectOnPickStudentProfileImage = inject('onPickStudentProfileImage')
const injectGo = inject('go')

const studentProfileImageFile = ref(null)
const profilePreview = ref('')
const guardianRelationReadonly = ref(false)

const religions = computed(() => Array.isArray(studentProfileSystems.value?.religions) ? studentProfileSystems.value.religions : [])
const bloodGroups = computed(() => Array.isArray(studentProfileSystems.value?.blood_groups) ? studentProfileSystems.value.blood_groups : [])
const hostels = computed(() => Array.isArray(studentProfileSystems.value?.hostels) ? studentProfileSystems.value.hostels : [])

function onPickStudentProfileImage(e) {
    const f = e?.target?.files?.[0]
    if (f) {
        studentProfileImageFile.value = f
        profilePreview.value = URL.createObjectURL(f)
        injectOnPickStudentProfileImage(e)
    }
}

function onGuardianTypeChange() {
    const type = studentProfileEditForm.guardian_type
    if (type === 'Father') {
        guardianRelationReadonly.value = true
        studentProfileEditForm.guardian_name = studentProfileEditForm.fathers_name
        studentProfileEditForm.guardian_relations = 'Father'
    } else if (type === 'Mother') {
        guardianRelationReadonly.value = true
        studentProfileEditForm.guardian_name = studentProfileEditForm.mothers_name
        studentProfileEditForm.guardian_relations = 'Mother'
    } else {
        guardianRelationReadonly.value = false
        studentProfileEditForm.guardian_name = ''
        studentProfileEditForm.guardian_relations = ''
    }
}
</script>
