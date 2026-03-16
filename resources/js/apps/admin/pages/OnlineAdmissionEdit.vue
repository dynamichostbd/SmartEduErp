<template>
    <form class="flex flex-col gap-4" @submit.prevent="submit">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Online Admission Edit</div>
                    <div class="mt-1 text-sm text-slate-600">Update applicant information</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        :disabled="submitting"
                        @click="goBack"
                    >
                        Back
                    </button>
                    <button
                        type="submit"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                        :disabled="submitting || loading"
                    >
                        {{ submitting ? 'Saving...' : 'Save' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="message" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
            {{ message }}
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div v-if="loading" class="rounded-2xl border border-slate-200 bg-white p-5 text-sm text-slate-600">Loading...</div>

        <div v-else class="grid grid-cols-1 gap-4 xl:grid-cols-12">
            <div class="xl:col-span-8 flex flex-col gap-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">Student Information</div>
                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Name</div>
                            <input v-model="form.name" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" required />
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Gender</div>
                            <select v-model="form.gender" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" required>
                                <option value="">Select</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">DOB</div>
                            <input v-model="form.dob" type="date" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Religion</div>
                            <input v-model="form.religion" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">NID</div>
                            <input v-model="form.nid" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Blood Group</div>
                            <input v-model="form.blood_group" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Father's Name</div>
                            <input v-model="form.fathers_name" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Mother's Name</div>
                            <input v-model="form.mothers_name" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Mobile</div>
                            <input v-model="form.mobile" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" required />
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Email</div>
                            <input v-model="form.email" type="email" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" />
                        </div>
                        <div class="md:col-span-2">
                            <div class="text-xs font-semibold text-slate-600">Address</div>
                            <input v-model="form.address" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" />
                        </div>
                        <div class="md:col-span-2">
                            <div class="text-xs font-semibold text-slate-600">Permanent Address</div>
                            <textarea v-model="form.permanent_address" rows="2" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"></textarea>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">Academic Information</div>
                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Admission Roll</div>
                            <input v-model="form.admission_roll" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" required />
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Registration No</div>
                            <input v-model="form.registration_no" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">SSC GPA</div>
                            <input v-model="form.ssc_gpa" type="number" step="0.01" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Student Type</div>
                            <select v-model="form.student_type" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm">
                                <option value="Regular">Regular</option>
                            </select>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Session</div>
                            <select v-model="form.academic_session_id" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" required>
                                <option value="">Select</option>
                                <option v-for="s in sessionsSorted" :key="'ses-' + s.id" :value="Number(s.id)">{{ s.name }}</option>
                            </select>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Academic Level</div>
                            <select v-model="form.academic_qualification_id" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" required>
                                <option value="">Select</option>
                                <option v-for="q in qualifications" :key="'q-' + q.id" :value="Number(q.id)">{{ q.name }}</option>
                            </select>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Department</div>
                            <select v-model="form.department_id" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm">
                                <option value="">Select</option>
                                <option v-for="d in departments" :key="'d-' + d.id" :value="Number(d.id)">{{ d.name }}</option>
                            </select>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Class</div>
                            <select v-model="form.academic_class_id" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" required>
                                <option value="">Select</option>
                                <option v-for="c in classes" :key="'c-' + c.id" :value="Number(c.id)">{{ c.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">Guardian Information</div>
                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Guardian Type</div>
                            <input v-model="form.guardian_type" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Guardian Name</div>
                            <input v-model="form.guardian_name" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Guardian Mobile</div>
                            <input v-model="form.guardian_mobile" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Guardian Relations</div>
                            <input v-model="form.guardian_relations" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" />
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">Others</div>
                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Passing Year</div>
                            <input v-model="form.passing_year" type="number" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Nationality</div>
                            <input v-model="form.nationality" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Quota</div>
                            <input v-model="form.quota" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Marital Status</div>
                            <input v-model="form.marital_status" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" />
                        </div>
                        <div class="md:col-span-2">
                            <div class="text-xs font-semibold text-slate-600">Extra Curricular Activity</div>
                            <input v-model="form.extra_curricular_activity" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-4 flex flex-col gap-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">Profile</div>
                    <div class="mt-4">
                        <div v-if="form.profile" class="mb-3 overflow-hidden rounded-xl border border-slate-200">
                            <img :src="form.profile" alt="Profile" class="h-52 w-full object-cover" />
                        </div>
                        <input
                            type="file"
                            accept="image/*"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"
                            @change="onPickProfile"
                        />
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">Status</div>
                    <div class="mt-3">
                        <select v-model="form.status" class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
export default {
    name: 'OnlineAdmissionEdit',
    props: {
        applicantId: {
            type: [String, Number],
            required: true,
        },
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            loading: false,
            submitting: false,
            error: '',
            message: '',
            profileFile: null,
            form: {
                academic_session_id: '',
                department_id: '',
                academic_qualification_id: '',
                academic_class_id: '',
                admission_roll: '',
                registration_no: '',
                ssc_gpa: '',
                student_type: 'Regular',
                name: '',
                gender: '',
                dob: '',
                religion: '',
                nid: '',
                blood_group: '',
                fathers_name: '',
                mothers_name: '',
                mobile: '',
                email: '',
                address: '',
                permanent_address: '',
                guardian_type: '',
                guardian_name: '',
                guardian_mobile: '',
                guardian_relations: '',
                passing_year: '',
                nationality: '',
                extra_curricular_activity: '',
                quota: '',
                marital_status: '',
                profile: '',
                status: 'pending',
            },
        }
    },
    computed: {
        sessions() {
            const list = this.systems?.global?.academic_sessions
            return Array.isArray(list) ? list : []
        },
        sessionsSorted() {
            const list = Array.isArray(this.sessions) ? [...this.sessions] : []
            return list.sort((a, b) => this.sessionSortKey(b) - this.sessionSortKey(a))
        },
        qualifications() {
            const list = this.systems?.global?.academic_qualifications
            return Array.isArray(list) ? list : []
        },
        departments() {
            const list = this.systems?.global?.departments
            return Array.isArray(list) ? list : []
        },
        classes() {
            const list = this.systems?.global?.academic_classes
            return Array.isArray(list) ? list : []
        },
    },
    mounted() {
        this.load()
    },
    methods: {
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
            window.location.href = '/admin/onlineAdmission'
        },
        onPickProfile(e) {
            const f = e?.target?.files?.[0] || null
            this.profileFile = f
            if (f) {
                this.form.profile = URL.createObjectURL(f)
            }
        },
        async load() {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/onlineAdmission/${this.applicantId}/details`)
                const d = res?.data || {}

                this.form = {
                    ...this.form,
                    academic_session_id: d.academic_session_id || '',
                    department_id: d.department_id || '',
                    academic_qualification_id: d.academic_qualification_id || '',
                    academic_class_id: d.academic_class_id || '',
                    admission_roll: d.admission_roll || '',
                    registration_no: d.registration_no || '',
                    ssc_gpa: d.ssc_gpa || '',
                    student_type: d.student_type || 'Regular',
                    name: d.name || '',
                    gender: d.gender || '',
                    dob: d.dob || '',
                    religion: d.religion || '',
                    nid: d.nid || '',
                    blood_group: d.blood_group || '',
                    fathers_name: d.fathers_name || '',
                    mothers_name: d.mothers_name || '',
                    mobile: d.mobile || '',
                    email: d.email || '',
                    address: d.address || '',
                    permanent_address: d.permanent_address || '',
                    guardian_type: d.guardian_type || '',
                    guardian_name: d.guardian_name || '',
                    guardian_mobile: d.guardian_mobile || '',
                    guardian_relations: d.guardian_relations || '',
                    passing_year: d.passing_year || '',
                    nationality: d.nationality || '',
                    extra_curricular_activity: d.extra_curricular_activity || '',
                    quota: d.quota || '',
                    marital_status: d.marital_status || '',
                    profile: d.profile || '',
                    status: d.status || 'pending',
                }
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load.'
            } finally {
                this.loading = false
            }
        },
        buildFormData() {
            const fd = new FormData()

            for (const [k, v] of Object.entries(this.form || {})) {
                if (k === 'profile') continue
                fd.append(k, v === null || v === undefined ? '' : String(v))
            }

            if (this.profileFile) {
                fd.append('profile', this.profileFile)
            }

            return fd
        },
        async submit() {
            this.submitting = true
            this.error = ''
            this.message = ''
            try {
                const res = await window.axios.post(`/admin/onlineAdmission/${this.applicantId}`, this.buildFormData(), {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                })
                this.message = res?.data?.message || 'Update Successfully!'
                setTimeout(() => this.goBack(), 350)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to update.'
            } finally {
                this.submitting = false
            }
        },
    },
}
</script>
