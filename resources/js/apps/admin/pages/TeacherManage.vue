<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">{{ isEdit ? 'Edit Teacher' : 'Create Teacher' }}</div>
                    <div class="mt-1 text-sm text-slate-600">{{ isEdit ? 'Update teacher/staff information' : 'Create new teacher/staff' }}</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">Back</button>
                    <button type="button" class="rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="saving" @click="submit">
                        {{ saving ? '...' : isEdit ? 'Update' : 'Create' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>
        <div v-if="success" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ success }}</div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="space-y-4 lg:col-span-8 xl:col-span-9">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-slate-900">Basic Information</div>
                        <div class="mt-1 text-xs text-slate-500">Account, department and contact details</div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Type</div>
                            <select v-model="form.type" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="Teacher">Teacher</option>
                                <option value="Staff">Staff</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Designation</div>
                            <select
                                v-model="teacher.designation_id"
                                class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                :disabled="form.type !== 'Teacher'"
                            >
                                <option value="">--Select Any--</option>
                                <option v-for="d in designations" :key="'des-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Name</div>
                            <input v-model="form.name" type="text" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Email</div>
                            <input v-model="form.email" type="email" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Mobile</div>
                            <input v-model="form.mobile" type="text" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Emergency Contacts</div>
                            <input v-model="form.emergency_contacts" type="text" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Password</div>
                            <input
                                v-model="form.password"
                                type="password"
                                class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                :placeholder="isEdit ? '(keep blank to keep same)' : ''"
                            />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Department/Group</div>
                            <select v-model="form.department_id" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">--Select Any--</option>
                                <option v-for="d in departments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Status</div>
                            <select v-model="form.status" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="active">Active</option>
                                <option value="deactive">Deactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-4">
                        <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <input v-model.number="form.is_two_factor_auth" type="checkbox" class="h-4 w-4" :true-value="1" :false-value="0" />
                            <span>Two Factor Auth</span>
                        </label>
                        <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <input v-model.number="form.block" type="checkbox" class="h-4 w-4" :true-value="1" :false-value="0" />
                            <span>Block</span>
                        </label>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-slate-900">Teacher Details</div>
                        <div class="mt-1 text-xs text-slate-500">Personal and professional details (for Teacher type)</div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Index Number</div>
                            <input v-model="teacher.index_number" type="text" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" :disabled="form.type !== 'Teacher'" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">BCS Batch</div>
                            <input v-model="teacher.bcs_batch" type="text" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" :disabled="form.type !== 'Teacher'" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Blood Group</div>
                            <select v-model="teacher.blood_group" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" :disabled="form.type !== 'Teacher'">
                                <option value="">--Select--</option>
                                <option v-for="b in bloodGroups" :key="'bg-' + b" :value="b">{{ b }}</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Date of Birth</div>
                            <input v-model="teacher.date_of_birth" type="date" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" :disabled="form.type !== 'Teacher'" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Joining Date (Lecturer)</div>
                            <input v-model="teacher.joining_date_lecturer" type="date" class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" :disabled="form.type !== 'Teacher'" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Joining Date (Present Designation)</div>
                            <input
                                v-model="teacher.joining_date_present_designation"
                                type="date"
                                class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                :disabled="form.type !== 'Teacher'"
                            />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Joining Date (Work Station)</div>
                            <input
                                v-model="teacher.joining_date_present_work_station"
                                type="date"
                                class="mt-1 h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                :disabled="form.type !== 'Teacher'"
                            />
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Present Address</div>
                            <textarea
                                v-model="teacher.present_address"
                                rows="2"
                                class="mt-1 w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                :disabled="form.type !== 'Teacher'"
                            ></textarea>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Permanent Address</div>
                            <textarea
                                v-model="teacher.permanent_address"
                                rows="2"
                                class="mt-1 w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                :disabled="form.type !== 'Teacher'"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4 lg:col-span-4 xl:col-span-3">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-900">Profile</div>
                    <div class="mt-1 text-xs text-slate-500">Upload profile photo</div>

                    <div class="mt-4 flex items-center gap-4">
                        <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                            <img :src="previewProfile || form.profile || fallback" class="h-full w-full object-cover" alt="profile" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <input
                                type="file"
                                accept="image/*"
                                class="block w-full text-sm file:mr-3 file:rounded-sm file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-slate-800"
                                @change="onFile"
                            />
                            <div class="mt-2 text-[11px] text-slate-500">JPG/PNG, recommended square image.</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-900">Signature</div>
                    <div class="mt-1 text-xs text-slate-500">Upload signature image</div>

                    <div class="mt-4 space-y-3">
                        <img
                            v-if="previewSignature || teacher.signature"
                            :src="previewSignature || teacher.signature || ''"
                            class="h-16 w-full rounded-sm border border-slate-200 bg-white object-contain"
                            alt="signature"
                        />
                        <input
                            type="file"
                            accept="image/*"
                            class="block w-full text-sm file:mr-3 file:rounded-sm file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-slate-800"
                            :disabled="form.type !== 'Teacher'"
                            @change="onSignature"
                        />
                    </div>
                </div>
            </div>
        </div>

        <div v-if="form.type === 'Teacher'" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-semibold text-slate-900">Subject Assign</div>
            <div class="mt-3 overflow-x-auto">
                <table class="min-w-full border-collapse border border-slate-200">
                    <thead class="bg-emerald-100">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-700">
                            <th class="border border-slate-200 px-3 py-2">Academic Level</th>
                            <th class="border border-slate-200 px-3 py-2">Department</th>
                            <th class="border border-slate-200 px-3 py-2">Class</th>
                            <th class="border border-slate-200 px-3 py-2">Subject</th>
                            <th class="border border-slate-200 px-3 py-2">Child Subject</th>
                            <th class="border border-slate-200 px-3 py-2">Status</th>
                            <th class="border border-slate-200 px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(it, idx) in subjectAssigns" :key="'sa-' + idx" class="text-sm text-slate-800">
                            <td class="border border-slate-200 px-3 py-2">
                                <select
                                    v-model="it.academic_qualification_id"
                                    class="h-9 w-48 rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                    @change="onAssignQualificationChange(it)"
                                >
                                    <option value="">--Select--</option>
                                    <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                                </select>
                            </td>
                            <td class="border border-slate-200 px-3 py-2">
                                <select v-model="it.department_id" class="h-9 w-48 rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                    <option value="">--Select--</option>
                                    <option v-for="d in departmentsForQualification(it.academic_qualification_id)" :key="'ad-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                                </select>
                            </td>
                            <td class="border border-slate-200 px-3 py-2">
                                <select v-model="it.academic_class_id" class="h-9 w-40 rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                    <option value="">--Select--</option>
                                    <option v-for="c in classesForQualification(it.academic_qualification_id)" :key="'ac-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                                </select>
                            </td>
                            <td class="border border-slate-200 px-3 py-2">
                                <select
                                    v-model="it.subject_id"
                                    class="h-9 w-56 rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                    @change="it.child_subject_id = ''"
                                >
                                    <option value="">--Select--</option>
                                    <option v-for="s in subjects" :key="'s-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                                </select>
                            </td>
                            <td class="border border-slate-200 px-3 py-2">
                                <select
                                    v-model="it.child_subject_id"
                                    class="h-9 w-56 rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                    :disabled="childSubjectOptions(it.subject_id).length === 0"
                                >
                                    <option value="">--Select--</option>
                                    <option v-for="s in childSubjectOptions(it.subject_id)" :key="'cs-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                                </select>
                            </td>
                            <td class="border border-slate-200 px-3 py-2">
                                <select v-model="it.status" class="h-9 w-32 rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                    <option value="active">Active</option>
                                    <option value="deactive">Deactive</option>
                                </select>
                            </td>
                            <td class="border border-slate-200 px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <button type="button" class="h-9 w-9 rounded-sm border border-rose-200 bg-white text-rose-700" :disabled="subjectAssigns.length <= 1" @click="removeAssign(idx)">-</button>
                                    <button type="button" class="h-9 w-9 rounded-sm border border-emerald-200 bg-white text-emerald-700" v-if="idx === subjectAssigns.length - 1" @click="addAssign">+</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'TeacherManage',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        teacherId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            loading: false,
            saving: false,
            error: '',
            success: '',
            file: null,
            signatureFile: null,
            previewProfile: '',
            previewSignature: '',
            fallback: window.laravel?.userimage || '',
            subjects: [],
            childSubjects: [],
            designationsApi: [],
            subjectAssigns: [
                {
                    academic_qualification_id: '',
                    department_id: '',
                    academic_class_id: '',
                    subject_id: '',
                    child_subject_id: '',
                    status: 'active',
                },
            ],
            form: {
                name: '',
                email: '',
                mobile: '',
                emergency_contacts: '',
                password: '',
                type: 'Teacher',
                department_id: '',
                status: 'active',
                is_two_factor_auth: 0,
                block: 0,
                profile: '',
            },
            teacher: {
                designation_id: '',
                index_number: '',
                bcs_batch: '',
                joining_date_lecturer: '',
                joining_date_present_designation: '',
                joining_date_present_work_station: '',
                blood_group: '',
                date_of_birth: '',
                present_address: '',
                permanent_address: '',
                signature: '',
            },
        }
    },
    computed: {
        isEdit() {
            return !!this.teacherId
        },
        departments() {
            const ds = this.systems?.global?.departments
            return Array.isArray(ds) ? ds : []
        },
        qualifications() {
            const list = this.systems?.global?.academic_qualifications
            return Array.isArray(list) ? list : []
        },
        classes() {
            const list = this.systems?.global?.academic_classes
            return Array.isArray(list) ? list : []
        },
        departmentQualidactions() {
            const list = this.systems?.global?.department_qualidactions
            return Array.isArray(list) ? list : []
        },
        designations() {
            return Array.isArray(this.designationsApi) ? this.designationsApi : []
        },
        bloodGroups() {
            return Array.isArray(this.systems?.global?.blood_groups) ? this.systems.global.blood_groups : []
        },
    },
    async created() {
        await Promise.all([this.loadSubjects(), this.loadChildSubjects(), this.loadDesignations()])
        if (this.isEdit) {
            await this.load()
        }
    },
    methods: {
        toast(message, type = 'success') {
            window.dispatchEvent(new CustomEvent('app-toast', { detail: { message, type } }))
        },
        go(path) {
            window.history.pushState({}, '', path)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        goIndex() {
            this.go('/admin/teacher')
        },
        onFile(e) {
            const f = e?.target?.files?.[0] || null
            this.file = f
            this.previewProfile = f ? URL.createObjectURL(f) : ''
        },
        onSignature(e) {
            const f = e?.target?.files?.[0] || null
            this.signatureFile = f
            this.previewSignature = f ? URL.createObjectURL(f) : ''
        },
        departmentsForQualification(qid) {
            const id = String(qid || '')
            if (!id) return []
            const allowed = new Set(
                this.departmentQualidactions
                    .filter((m) => String(m.academic_qualification_id) === id)
                    .map((m) => String(m.department_id))
            )
            return this.departments.filter((d) => allowed.has(String(d.id)))
        },
        classesForQualification(qid) {
            const id = String(qid || '')
            if (!id) return []
            return this.classes.filter((c) => String(c.academic_qualification_id) === id)
        },
        childSubjectOptions(parentId) {
            const pid = String(parentId || '')
            if (!pid) return []
            return (this.childSubjects || []).filter((s) => String(s.parent_id || '') === pid)
        },
        onAssignQualificationChange(it) {
            it.department_id = ''
            it.academic_class_id = ''
        },
        addAssign() {
            this.subjectAssigns.push({
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
                subject_id: '',
                child_subject_id: '',
                status: 'active',
            })
        },
        removeAssign(idx) {
            if (this.subjectAssigns.length <= 1) return
            this.subjectAssigns.splice(idx, 1)
        },
        async loadSubjects() {
            try {
                const res = await window.axios.get('/admin/all-subjects')
                this.subjects = Array.isArray(res?.data) ? res.data : []
            } catch {
                this.subjects = []
            }
        },
        async loadChildSubjects() {
            try {
                const res = await window.axios.get('/admin/all-child-subjects')
                this.childSubjects = Array.isArray(res?.data) ? res.data : []
            } catch {
                this.childSubjects = []
            }
        },
        async loadDesignations() {
            try {
                const res = await window.axios.get('/admin/get-designation', { params: { allData: true } })
                this.designationsApi = Array.isArray(res?.data) ? res.data : []
            } catch {
                this.designationsApi = []
            }
        },
        async load() {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/teacher/${this.teacherId}`)
                const t = res?.data?.teacher || {}
                const tp = res?.data?.teacher_profile || {}
                const assigns = Array.isArray(res?.data?.subject_assigns) ? res.data.subject_assigns : []
                this.form.name = t?.name || ''
                this.form.email = t?.email || ''
                this.form.mobile = t?.mobile || ''
                this.form.emergency_contacts = t?.emergency_contacts || ''
                this.form.department_id = t?.department_id != null ? String(t.department_id) : ''
                this.form.status = t?.status || 'active'
                this.form.profile = t?.profile || ''
                this.form.type = t?.type || 'Teacher'
                this.form.is_two_factor_auth = Number(t?.is_two_factor_auth || 0)
                this.form.block = Number(t?.block || 0)
                this.form.password = ''

                this.teacher.designation_id = tp?.designation_id != null ? String(tp.designation_id) : ''
                this.teacher.index_number = tp?.index_number || ''
                this.teacher.bcs_batch = tp?.bcs_batch || ''
                this.teacher.joining_date_lecturer = tp?.joining_date_lecturer || ''
                this.teacher.joining_date_present_designation = tp?.joining_date_present_designation || ''
                this.teacher.joining_date_present_work_station = tp?.joining_date_present_work_station || ''
                this.teacher.blood_group = tp?.blood_group || ''
                this.teacher.date_of_birth = tp?.date_of_birth || ''
                this.teacher.present_address = tp?.present_address || ''
                this.teacher.permanent_address = tp?.permanent_address || ''
                this.teacher.signature = tp?.signature || ''
                this.previewSignature = ''
                this.signatureFile = null

                if (assigns.length > 0) {
                    this.subjectAssigns = assigns.map((a) => ({
                        academic_qualification_id: a?.academic_qualification_id != null ? String(a.academic_qualification_id) : '',
                        department_id: a?.department_id != null ? String(a.department_id) : '',
                        academic_class_id: a?.academic_class_id != null ? String(a.academic_class_id) : '',
                        subject_id: a?.subject_id != null ? String(a.subject_id) : '',
                        child_subject_id: '',
                        status: a?.status || 'active',
                    }))
                }
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load teacher.'
            } finally {
                this.loading = false
            }
        },
        validate() {
            if (!this.form.name) return 'Name is required'
            if (!this.form.email) return 'Email is required'
            if (!this.isEdit && !this.form.password) return 'Password is required'
            if (!this.isEdit && String(this.form.password || '').length < 6) return 'Password must be at least 6 characters'
            if (this.isEdit && this.form.password && String(this.form.password).length < 6) return 'Password must be at least 6 characters'
            return ''
        },
        async submit() {
            this.error = ''
            this.success = ''

            const msg = this.validate()
            if (msg) {
                this.error = msg
                return
            }

            this.saving = true
            try {
                const fd = new FormData()
                Object.keys(this.form).forEach((k) => {
                    if (k === 'profile') return
                    const v = this.form[k]
                    if (v === null || v === undefined) return
                    if (k === 'password' && !v) return
                    fd.append(k, String(v))
                })
                if (this.file) fd.append('profile', this.file)

                fd.append('teacher', JSON.stringify(this.teacher))
                const assignsPayload = (this.subjectAssigns || []).map((a) => ({
                    academic_qualification_id: a.academic_qualification_id ? Number(a.academic_qualification_id) : null,
                    department_id: a.department_id ? Number(a.department_id) : null,
                    academic_class_id: a.academic_class_id ? Number(a.academic_class_id) : null,
                    subject_id: a.child_subject_id ? Number(a.child_subject_id) : a.subject_id ? Number(a.subject_id) : null,
                    status: a.status || 'active',
                }))
                fd.append('subject_assigns', JSON.stringify(assignsPayload))
                if (this.signatureFile) fd.append('signature', this.signatureFile)

                let res
                if (this.isEdit) {
                    res = await window.axios.post(`/admin/teacher/${this.teacherId}?_method=PUT`, fd, {
                        headers: { 'Content-Type': 'multipart/form-data' },
                    })
                } else {
                    res = await window.axios.post('/admin/teacher', fd, {
                        headers: { 'Content-Type': 'multipart/form-data' },
                    })
                }

                const m = res?.data?.message || (this.isEdit ? 'Update Successfully!' : 'Create Successfully!')
                this.success = m
                this.toast(m, 'success')

                if (!this.isEdit) {
                    this.goIndex()
                } else {
                    await this.load()
                }
            } catch (e) {
                this.error = e?.response?.data?.message || e?.response?.data?.exception || 'Submit failed.'
            } finally {
                this.saving = false
            }
        },
    },
}
</script>
