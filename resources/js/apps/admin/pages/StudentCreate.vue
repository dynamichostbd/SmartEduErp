<template>
    <form class="flex flex-col gap-4" @submit.prevent="save">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Student Create</div>
                    <div class="mt-1 text-sm text-slate-600">Create new student</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        :disabled="saving"
                        @click="goBack"
                    >
                        Back
                    </button>
                    <button
                        type="submit"
                        class="rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                        :disabled="saving"
                    >
                        {{ saving ? 'Saving...' : 'Submit' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="space-y-4 lg:col-span-8 xl:col-span-9">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-slate-900">Academic Information</div>
                            <div class="mt-1 text-xs text-slate-500">Session, level, department and class selection</div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Select Session <span class="text-red-600">*</span></div>
                            <select
                                v-model="form.academic_session_id"
                                class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                required
                            >
                                <option value="">Select Session</option>
                                <option v-for="s in sessionsSorted" :key="s.id" :value="String(s.id)">{{ s.name }}</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Academic Level <span class="text-red-600">*</span></div>
                            <select
                                v-model="form.academic_qualification_id"
                                class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                required
                            >
                                <option value="">Select Qualification</option>
                                <option v-for="q in qualifications" :key="q.id" :value="String(q.id)">{{ q.name }}</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Department/Group <span v-if="departmentRequired" class="text-red-600">*</span></div>
                            <select
                                v-model="form.department_id"
                                class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                :required="departmentRequired"
                            >
                                <option value="">Select Department</option>
                                <option v-for="d in filteredDepartments" :key="d.id" :value="String(d.id)">{{ d.name }}</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Class <span class="text-red-600">*</span></div>
                            <select
                                v-model="form.academic_class_id"
                                class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                required
                            >
                                <option value="">Select Class</option>
                                <option v-for="c in filteredClasses" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Student Type <span class="text-red-600">*</span></div>
                            <select
                                v-model="form.student_type"
                                class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                required
                            >
                                <option value="">Student Type</option>
                                <option v-for="t in studentTypes" :key="t" :value="t">{{ t }}</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Admission ID</div>
                            <input v-model="form.admission_id" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">College Roll</div>
                            <input v-model="form.college_roll" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Reg. No</div>
                            <input v-model="form.reg_no" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Readmission College Roll</div>
                            <input v-model="form.readmission_college_roll" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">SSC GPA</div>
                            <input v-model="form.ssc_gpa" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Passing Year</div>
                            <input v-model="form.passing_year" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Nationality</div>
                            <input v-model="form.nationality" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-slate-900">Personal Information</div>
                        <div class="mt-1 text-xs text-slate-500">Identity, personal details and demographics</div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Full Name <span class="text-red-600">*</span></div>
                            <input
                                v-model="form.name"
                                type="text"
                                class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                required
                            />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Gender</div>
                            <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-slate-700">
                                <label class="inline-flex items-center gap-2">
                                    <input v-model="form.gender" class="accent-emerald-600" type="radio" name="gender" value="Male" />
                                    Male
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input v-model="form.gender" class="accent-emerald-600" type="radio" name="gender" value="Female" />
                                    Female
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input v-model="form.gender" class="accent-emerald-600" type="radio" name="gender" value="Others" />
                                    Others
                                </label>
                            </div>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Blood Group</div>
                            <select v-model="form.blood_group" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">Select</option>
                                <option v-for="b in bloodGroups" :key="'bg-' + b" :value="b">{{ b }}</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">DOB</div>
                            <input v-model="form.dob" type="date" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Religion</div>
                            <select v-model="form.religion" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">Select</option>
                                <option v-for="r in religions" :key="'rel-' + r" :value="r">{{ r }}</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">NID/Birth Reg No</div>
                            <input v-model="form.nid" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Quota</div>
                            <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-slate-700">
                                <label class="inline-flex items-center gap-2">
                                    <input v-model="form.quota" class="accent-emerald-600" type="radio" name="quota" value="Yes" />
                                    Yes
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input v-model="form.quota" class="accent-emerald-600" type="radio" name="quota" value="No" />
                                    No
                                </label>
                            </div>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Marital Status</div>
                            <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-slate-700">
                                <label class="inline-flex items-center gap-2">
                                    <input v-model="form.marital_status" class="accent-emerald-600" type="radio" name="marital_status" value="Married" />
                                    Married
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input v-model="form.marital_status" class="accent-emerald-600" type="radio" name="marital_status" value="Unmarried" />
                                    Unmarried
                                </label>
                            </div>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Extra Curricular Activity</div>
                            <input v-model="form.extra_curricular_activity" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-slate-900">Family & Guardian</div>
                        <div class="mt-1 text-xs text-slate-500">Parents, guardian and address information</div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Father's Name</div>
                            <input v-model="form.fathers_name" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Mother's Name</div>
                            <input v-model="form.mothers_name" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Address (Vill: , Post: , Upozila: , Dist:)</div>
                            <input v-model="form.address" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Guardian Type</div>
                            <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-slate-700">
                                <label class="inline-flex items-center gap-2">
                                    <input v-model="form.guardian_type" class="accent-emerald-600" type="radio" name="guardian_type" value="Father" />
                                    Father
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input v-model="form.guardian_type" class="accent-emerald-600" type="radio" name="guardian_type" value="Mother" />
                                    Mother
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input v-model="form.guardian_type" class="accent-emerald-600" type="radio" name="guardian_type" value="Other" />
                                    Other
                                </label>
                            </div>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Guardian Name</div>
                            <input v-model="form.guardian_name" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Guardian Mobile</div>
                            <input v-model="form.guardian_mobile" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Guardian Relations</div>
                            <input v-model="form.guardian_relations" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Living Type</div>
                            <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-slate-700">
                                <label class="inline-flex items-center gap-2">
                                    <input v-model="form.living_type" class="accent-emerald-600" type="radio" name="living_type" value="Hostel" />
                                    Hostel
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input v-model="form.living_type" class="accent-emerald-600" type="radio" name="living_type" value="Others" />
                                    Others
                                </label>
                            </div>
                        </div>

                        <div v-if="String(form.living_type) === 'Hostel'">
                            <div class="text-xs font-semibold text-slate-600">Hostel</div>
                            <select v-model="form.hostel_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">Select Hostel</option>
                                <option v-for="h in hostels" :key="h.id" :value="String(h.id)">{{ h.name }}</option>
                            </select>
                        </div>
                        <div v-if="String(form.living_type) === 'Hostel'">
                            <div class="text-xs font-semibold text-slate-600">Hostel Room No</div>
                            <input v-model="form.hostel_room_no" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div v-if="String(form.living_type) === 'Hostel'">
                            <div class="text-xs font-semibold text-slate-600">Hostel Admission</div>
                            <input v-model="form.hostel_admission_date" type="date" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div v-if="String(form.living_type) === 'Hostel'">
                            <div class="text-xs font-semibold text-slate-600">Hostel Release</div>
                            <input v-model="form.hostel_release_date" type="date" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4 lg:col-span-4 xl:col-span-3">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-900">Profile</div>
                    <div class="mt-1 text-xs text-slate-500">Upload student profile photo</div>

                    <div class="mt-4 flex items-center gap-4">
                        <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                            <img v-if="profilePreview" :src="profilePreview" class="h-full w-full object-cover" />
                            <div v-else class="text-lg font-semibold text-slate-400">{{ String(form.name || 'S').trim().slice(0, 1).toUpperCase() }}</div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <input
                                ref="profileInput"
                                type="file"
                                class="block w-full text-sm file:mr-3 file:rounded-sm file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-slate-800"
                                accept="image/*"
                                @change="onPickProfile"
                            />
                            <div class="mt-2 text-[11px] text-slate-500">JPG/PNG, recommended square image.</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-900">Account & Contact</div>
                    <div class="mt-1 text-xs text-slate-500">Login password and contact details</div>

                    <div class="mt-4 space-y-4">
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Mobile <span class="text-red-600">*</span></div>
                            <input v-model="form.mobile" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Password <span class="text-red-600">*</span></div>
                            <input
                                v-model="form.password"
                                type="text"
                                class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                required
                                placeholder="Password"
                            />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Email</div>
                            <input v-model="form.email" type="email" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Status</div>
                            <select v-model="form.status" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="active">Active</option>
                                <option value="deactive">Deactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
export default {
    name: 'StudentCreate',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            saving: false,
            error: '',
            profileFile: null,
            profilePreview: '',
            form: {
                academic_class_id: '',
                academic_qualification_id: '',
                academic_session_id: '',
                department_id: '',
                student_type: '',
                ssc_gpa: '',
                reg_no: '',
                admission_id: '',
                hostel_id: '',
                college_roll: '',
                readmission_college_roll: '',
                name: '',
                gender: 'Male',
                dob: '',
                religion: '',
                nid: '',
                blood_group: '',
                fathers_name: '',
                mothers_name: '',
                email: '',
                mobile: '',
                address: '',
                guardian_type: '',
                guardian_name: '',
                guardian_mobile: '',
                guardian_relations: '',
                passing_year: '',
                nationality: '',
                extra_curricular_activity: '',
                quota: '',
                marital_status: '',
                living_type: 'Others',
                hostel_room_no: '',
                hostel_admission_date: '',
                hostel_release_date: '',
                password: '',
                status: 'active',
            },
        }
    },
    computed: {
        classes() {
            const list = this.systems?.global?.academic_classes
            if (!Array.isArray(list)) return []
            return list.filter((r) => r?.status === undefined || r?.status === null || String(r.status) === 'active' || String(r.status) === '1')
        },
        sessions() {
            const list = this.systems?.global?.academic_sessions
            if (!Array.isArray(list)) return []
            return list.filter((r) => r?.status === undefined || r?.status === null || String(r.status) === 'active' || String(r.status) === '1')
        },
        sessionsSorted() {
            const list = Array.isArray(this.sessions) ? [...this.sessions] : []
            return list.sort((a, b) => this.sessionSortKey(b) - this.sessionSortKey(a))
        },
        qualifications() {
            const list = this.systems?.global?.academic_qualifications
            if (!Array.isArray(list)) return []
            return list.filter((r) => r?.status === undefined || r?.status === null || String(r.status) === 'active' || String(r.status) === '1')
        },
        departments() {
            const list = this.systems?.global?.departments
            if (!Array.isArray(list)) return []
            return list.filter((r) => r?.status === undefined || r?.status === null || String(r.status) === 'active' || String(r.status) === '1')
        },
        departmentQualidactions() {
            const list = this.systems?.global?.department_qualidactions || this.systems?.global?.department_qualifications
            return Array.isArray(list) ? list : []
        },
        filteredDepartments() {
            const qualificationId = this.form?.academic_qualification_id
            if (!qualificationId) return []

            const allowedDeptIds = new Set(
                this.departmentQualidactions
                    .filter((r) => String(r.academic_qualification_id) === String(qualificationId))
                    .map((r) => String(r.department_id))
            )

            return this.departments.filter((d) => allowedDeptIds.has(String(d.id)))
        },
        filteredClasses() {
            const qualificationId = this.form?.academic_qualification_id
            if (!qualificationId) return []
            return this.classes.filter((c) => String(c.academic_qualification_id) === String(qualificationId))
        },
        hostels() {
            const list = this.systems?.global?.hostels
            if (!Array.isArray(list)) return []
            return list.filter((r) => r?.status === undefined || r?.status === null || String(r.status) === 'active' || String(r.status) === '1')
        },
        studentTypes() {
            const list = this.systems?.global?.student_types
            return Array.isArray(list) ? list : []
        },
        religions() {
            const list = this.systems?.global?.religions
            return Array.isArray(list) ? list : []
        },
        bloodGroups() {
            const list = this.systems?.global?.blood_groups
            return Array.isArray(list) ? list : []
        },
        selectedQualificationName() {
            const id = this.form?.academic_qualification_id
            const q = (this.qualifications || []).find((x) => String(x?.id) === String(id))
            return String(q?.name || '')
        },
        departmentRequired() {
            const name = this.selectedQualificationName
            if (!name) return true
            const n = name.trim().toLowerCase()
            if (n === 'hsc' || n === 'degree') return false
            return true
        },
    },
    watch: {
        'form.academic_qualification_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.form.department_id = ''
                this.form.academic_class_id = ''
            }
        },
        'form.guardian_type'(val) {
            if (val === 'Father') {
                this.form.guardian_name = this.form.fathers_name
                this.form.guardian_relations = val
            } else if (val === 'Mother') {
                this.form.guardian_name = this.form.mothers_name
                this.form.guardian_relations = val
            } else {
                this.form.guardian_name = ''
                this.form.guardian_relations = ''
            }
        },
        'form.fathers_name'(val) {
            if (this.form.guardian_type === 'Father') {
                this.form.guardian_name = val
                this.form.guardian_relations = 'Father'
            }
        },
        'form.mothers_name'(val) {
            if (this.form.guardian_type === 'Mother') {
                this.form.guardian_name = val
                this.form.guardian_relations = 'Mother'
            }
        },
    },
    mounted() {
        this.form.password = this.randomPassword()
    },
    methods: {
        randomPassword() {
            return String(Math.floor(100000 + Math.random() * 900000))
        },
        sessionSortKey(s) {
            const name = String(s?.name || '')
            const m = name.match(/(\d{4})\s*[-/]\s*(\d{4})/)
            if (m) {
                const start = Number(m[1] || 0)
                const end = Number(m[2] || 0)
                return start * 10000 + end
            }
            const n = Number(name)
            if (!Number.isNaN(n) && Number.isFinite(n)) return n
            const id = Number(s?.id || 0)
            return Number.isFinite(id) ? id : 0
        },
        goBack() {
            window.location.href = '/admin/student'
        },
        onPickProfile(e) {
            const f = e?.target?.files?.[0] || null
            this.profileFile = f
            if (!f) {
                this.profilePreview = ''
                return
            }
            try {
                this.profilePreview = URL.createObjectURL(f)
            } catch (err) {
                this.profilePreview = ''
            }
        },
        buildFormData() {
            const fd = new FormData()
            Object.keys(this.form).forEach((k) => {
                const v = this.form[k]
                if (v === undefined || v === null) return
                fd.append(k, v)
            })

            if (this.profileFile) {
                fd.append('profile', this.profileFile)
            }

            return fd
        },
        async save() {
            this.saving = true
            this.error = ''
            try {
                const res = await window.axios.post('/admin/student', this.buildFormData(), {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                })

                if (res?.data?.error) {
                    this.error = String(res.data.error)
                    return
                }

                const id = res?.data?.id
                if (id) {
                    window.location.href = `/admin/student/${id}`
                } else {
                    window.location.href = '/admin/student'
                }
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to create student.'
            } finally {
                this.saving = false
            }
        },
    },
}
</script>
