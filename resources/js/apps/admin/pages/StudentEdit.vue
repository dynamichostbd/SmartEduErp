<template>
    <form class="flex flex-col gap-4" @submit.prevent="save">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Student Edit</div>
                    <div class="mt-1 text-sm text-slate-600">{{ form.name || '' }}</div>
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
                        :disabled="loading || saving"
                    >
                        {{ saving ? 'Saving...' : 'Submit' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div v-if="loading" class=" border border-slate-300 bg-white p-5 text-sm text-slate-600">Loading...</div>

        <div v-else class=" border border-slate-300 bg-white p-5">
            <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
                <div class="space-y-4">
                    <div>
                        <div class="text-xs font-semibold text-slate-600">Profile</div>
                        <div class="mt-2 flex items-center gap-3">
                            <div class="h-16 w-16 overflow-hidden rounded-xl bg-slate-100">
                                <img v-if="profilePreview" :src="profilePreview" class="h-full w-full object-cover" />
                                <img v-else-if="form.profile" :src="form.profile" class="h-full w-full object-cover" />
                            </div>
                            <input ref="profileInput" type="file" class="block w-full text-sm" accept="image/*" @change="onPickProfile" />
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">Full Name <span class="text-red-600">*</span></div>
                        <input v-model="form.name" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required />
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">Gender</div>
                        <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-slate-700">
                            <label class="inline-flex items-center gap-2">
                                <input v-model="form.gender" type="radio" name="gender" value="Male" />
                                Male
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input v-model="form.gender" type="radio" name="gender" value="Female" />
                                Female
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input v-model="form.gender" type="radio" name="gender" value="Others" />
                                Others
                            </label>
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">Blood Group</div>
                        <select v-model="form.blood_group" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none">
                            <option value="">Select</option>
                            <option v-for="b in bloodGroups" :key="'bg-' + b" :value="b">{{ b }}</option>
                        </select>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">DOB</div>
                        <input v-model="form.dob" type="date" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" />
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">Religion</div>
                        <select v-model="form.religion" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none">
                            <option value="">Select</option>
                            <option v-for="r in religions" :key="'rel-' + r" :value="r">{{ r }}</option>
                        </select>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">NID/Birth Reg No</div>
                        <input v-model="form.nid" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" />
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <div class="text-xs font-semibold text-slate-600">Admission ID</div>
                        <input v-model="form.admission_id" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">College Roll</div>
                        <input v-model="form.college_roll" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">SSC GPA</div>
                        <input v-model="form.ssc_gpa" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" />
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">Select Session <span class="text-red-600">*</span></div>
                        <select v-model="form.academic_session_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required>
                            <option value="">Select Session</option>
                            <option v-for="s in sessionsSorted" :key="s.id" :value="String(s.id)">{{ s.name }}</option>
                        </select>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">Academic Level <span class="text-red-600">*</span></div>
                        <select v-model="form.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required>
                            <option value="">Select Qualification</option>
                            <option v-for="q in qualifications" :key="q.id" :value="String(q.id)">{{ q.name }}</option>
                        </select>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">Department/Group <span class="text-red-600">*</span></div>
                        <select v-model="form.department_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required>
                            <option value="">Select Department</option>
                            <option v-for="d in filteredDepartments" :key="d.id" :value="String(d.id)">{{ d.name }}</option>
                        </select>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">Class <span class="text-red-600">*</span></div>
                        <select v-model="form.academic_class_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required>
                            <option value="">Select Class</option>
                            <option v-for="c in filteredClasses" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
                        </select>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">Student Type <span class="text-red-600">*</span></div>
                        <select v-model="form.student_type" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required>
                            <option value="">Student Type</option>
                            <option v-for="t in studentTypes" :key="t" :value="t">{{ t }}</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <div class="text-xs font-semibold text-slate-600">Mobile <span class="text-red-600">*</span></div>
                        <input v-model="form.mobile" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required />
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">Password</div>
                        <input v-model="form.password" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" placeholder="Password" />
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">Email</div>
                        <input v-model="form.email" type="email" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" />
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">Address (Vill: , Post: , Upozila: , Dist:)</div>
                        <input v-model="form.address" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">Guardian Type</div>
                        <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-slate-700">
                            <label class="inline-flex items-center gap-2">
                                <input v-model="form.guardian_type" type="radio" name="guardian_type" value="Father" />
                                Father
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input v-model="form.guardian_type" type="radio" name="guardian_type" value="Mother" />
                                Mother
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input v-model="form.guardian_type" type="radio" name="guardian_type" value="Other" />
                                Other
                            </label>
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">Guardian Name</div>
                        <input v-model="form.guardian_name" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" />
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">Guardian Mobile</div>
                        <input v-model="form.guardian_mobile" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" />
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">Guardian Relations</div>
                        <input v-model="form.guardian_relations" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" />
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">Living Type</div>
                        <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-slate-700">
                            <label class="inline-flex items-center gap-2">
                                <input v-model="form.living_type" type="radio" name="living_type" value="Hostel" />
                                Hostel
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input v-model="form.living_type" type="radio" name="living_type" value="Others" />
                                Others
                            </label>
                        </div>
                    </div>

                    <div v-if="String(form.living_type) === 'Hostel'" class="space-y-4">
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Hostel</div>
                            <select v-model="form.hostel_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">Select Hostel</option>
                                <option v-for="h in hostels" :key="h.id" :value="String(h.id)">{{ h.name }}</option>
                            </select>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Hostel Room No</div>
                            <input v-model="form.hostel_room_no" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Hostel Admission</div>
                            <input v-model="form.hostel_admission_date" type="date" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Hostel Release</div>
                            <input v-model="form.hostel_release_date" type="date" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
export default {
    name: 'StudentEdit',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        studentId: {
            type: [String, Number],
            required: true,
        },
    },
    data() {
        return {
            loading: false,
            saving: false,
            error: '',
            hydrating: false,
            profileFile: null,
            profilePreview: '',
            form: {
                online_admission_id: '',
                academic_class_id: '',
                academic_qualification_id: '',
                academic_session_id: '',
                department_id: '',
                student_type: '',
                student_id: '',
                ssc_gpa: '',
                reg_no: '',
                admission_id: '',
                hostel_id: '',
                college_roll: '',
                readmission_college_roll: '',
                name: '',
                gender: '',
                dob: '',
                religion: '',
                nid: '',
                blood_group: '',
                fathers_name: '',
                mothers_name: '',
                email: '',
                mobile: '',
                address: '',
                permanent_address: '',
                profile: '',
                guardian_type: '',
                guardian_name: '',
                guardian_mobile: '',
                guardian_relations: '',
                passing_year: '',
                nationality: '',
                extra_curricular_activity: '',
                quota: '',
                marital_status: '',
                living_type: '',
                hostel_room_no: '',
                hostel_discount_percent: '',
                hostel_admission_date: '',
                hostel_release_date: '',
                cluster_subjects: null,
                subject_id: '',
                subject_cluster_id: '',
                college_roll_auto_generate: '',
                password: '',
                status: '',
            },
            clusterSubjectsText: '[]',
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
    },
    watch: {
        'form.academic_qualification_id'(next, prev) {
            if (this.hydrating) return
            if (String(next || '') !== String(prev || '')) {
                this.form.department_id = ''
                this.form.academic_class_id = ''
            }
        },
    },
    mounted() {
        this.fetchDetails()
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
            window.location.href = `/admin/student/${this.studentId}`
        },
        async fetchDetails() {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/student/${this.studentId}/details`)
                const student = res?.data?.student || null
                if (!student) return

                this.hydrating = true
                Object.keys(this.form).forEach((k) => {
                    if (student[k] !== undefined && student[k] !== null) {
                        this.form[k] = student[k]
                    }
                })

                ;['academic_session_id', 'academic_qualification_id', 'department_id', 'academic_class_id', 'hostel_id'].forEach((k) => {
                    if (this.form[k] !== undefined && this.form[k] !== null && this.form[k] !== '') {
                        this.form[k] = String(this.form[k])
                    }
                })

                if (student?.cluster_subjects !== undefined) {
                    try {
                        const txt = JSON.stringify(student.cluster_subjects ?? [], null, 2)
                        this.clusterSubjectsText = txt
                    } catch (e) {
                        this.clusterSubjectsText = '[]'
                    }
                }

                this.$nextTick(() => {
                    this.hydrating = false
                })
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load student details.'
            } finally {
                this.loading = false
            }
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
            fd.append('_method', 'PUT')

            Object.keys(this.form).forEach((k) => {
                const v = this.form[k]
                if (v === undefined || v === null) return
                fd.append(k, v)
            })

            try {
                const parsed = JSON.parse(this.clusterSubjectsText || '[]')
                fd.set('cluster_subjects', JSON.stringify(parsed))
            } catch (e) {
                fd.set('cluster_subjects', '')
            }

            if (this.profileFile) {
                fd.append('profile', this.profileFile)
            }

            return fd
        },
        async save() {
            this.saving = true
            this.error = ''
            try {
                await window.axios.post(`/admin/student/${this.studentId}`, this.buildFormData(), {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                })
                window.location.href = `/admin/student/${this.studentId}`
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to save student.'
            } finally {
                this.saving = false
            }
        },
    },
}
</script>
