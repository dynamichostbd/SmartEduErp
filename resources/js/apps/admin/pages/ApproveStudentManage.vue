<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">
                        {{ isEdit ? 'Edit Approved Student Batch' : 'Create Approved Student Batch' }}
                    </div>
                    <div class="mt-1 text-sm text-slate-600">
                        {{ isEdit ? 'Update student approval status' : 'Fetch students from exam results' }}
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <router-link
                        :to="{ name: 'approveStudent.index' }"
                        class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                    >
                        <i class="fas fa-arrow-left mr-2"></i>Back to List
                    </router-link>
                </div>
            </div>
        </div>

        <!-- Edit Mode: Batch Info Header -->
        <div v-if="isEdit && batchInfo" class=" border border-slate-300 bg-white p-5">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-slate-900">Batch Information</h3>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-6">
                <div>
                    <div class="text-sm font-semibold text-slate-600">Session</div>
                    <div class="mt-1 text-slate-900">{{ batchInfo.academic_session?.name || '—' }}</div>
                </div>
                <div>
                    <div class="text-sm font-semibold text-slate-600">Academic Level</div>
                    <div class="mt-1 text-slate-900">{{ batchInfo.qualification?.name || '—' }}</div>
                </div>
                <div>
                    <div class="text-sm font-semibold text-slate-600">Department</div>
                    <div class="mt-1 text-slate-900">{{ batchInfo.department?.name || '—' }}</div>
                </div>
                <div>
                    <div class="text-sm font-semibold text-slate-600">Class</div>
                    <div class="mt-1 text-slate-900">{{ batchInfo.academic_class?.name || '—' }}</div>
                </div>
                <div>
                    <div class="text-sm font-semibold text-slate-600">Exam</div>
                    <div class="mt-1 text-slate-900">{{ batchInfo.exam?.name || '—' }}</div>
                </div>
                <div>
                    <div class="text-sm font-semibold text-slate-600">Status</div>
                    <div class="mt-1">
                        <span
                            class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                            :class="statusBadgeClass(batchInfo.status)"
                        >
                            {{ batchInfo.status || '—' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Form (Create Mode) -->
        <div v-if="!isEdit && !students.length" class=" border border-slate-300 bg-white p-5">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-slate-900">Search Result (Prefill from Result)</h3>
            </div>
            
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-6">
                <div>
                    <div class="text-xs font-semibold text-slate-600">Session *</div>
                    <select v-model="form.academic_session_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                        <option value="">Select Session</option>
                        <option v-for="s in sessions" :key="'ses-' + s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                </div>

                <div>
                    <div class="text-xs font-semibold text-slate-600">Academic Level *</div>
                    <select v-model="form.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                        <option value="">Select Level</option>
                        <option v-for="q in qualifications" :key="'q-' + q.id" :value="q.id">{{ q.name }}</option>
                    </select>
                </div>

                <div>
                    <div class="text-xs font-semibold text-slate-600">Department/Group *</div>
                    <select v-model="form.department_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                        <option value="">Select Department</option>
                        <option v-for="d in filteredDepartments" :key="'d-' + d.id" :value="d.id">{{ d.name }}</option>
                    </select>
                </div>

                <div>
                    <div class="text-xs font-semibold text-slate-600">Class *</div>
                    <select v-model="form.academic_class_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                        <option value="">Select Class</option>
                        <option v-for="c in filteredClasses" :key="'c-' + c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>

                <div>
                    <div class="text-xs font-semibold text-slate-600">Fetch Exam (Result) *</div>
                    <select v-model="form.exam_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                        <option value="">Select Exam</option>
                        <option v-for="e in exams" :key="'e-' + e.id" :value="e.id">{{ e.name }}</option>
                    </select>
                </div>

                <div>
                    <div class="text-xs font-semibold text-slate-600">Save To Exam *</div>
                    <select v-model="form.exam_id_others" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                        <option value="">Select Target Exam</option>
                        <option v-for="e in exams" :key="'e2-' + e.id" :value="e.id">{{ e.name }}</option>
                    </select>
                </div>
            </div>

            <div class="mt-6 text-center">
                <button
                    type="button"
                    class="rounded-sm bg-emerald-600 px-6 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                    :disabled="searching || !isFormValid"
                    @click="fetchStudents"
                >
                    <i v-if="searching" class="fas fa-spinner fa-spin mr-2"></i>
                    <i v-else class="fas fa-search mr-2"></i>
                    {{ searching ? 'Searching...' : 'Fetch from Result' }}
                </button>
            </div>
        </div>

        <!-- Students Table -->
        <div v-if="students.length > 0" class=" border border-slate-300 bg-white p-5">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900">Students Result</h3>
                <div class="flex items-center gap-3">
                    <span v-if="isEdit && mergedNewCount > 0" class="rounded-sm border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                        <i class="fas fa-plus-circle mr-1"></i>
                        {{ mergedNewCount }} new student(s) merged
                    </span>
                    <button
                        type="button"
                        class="rounded-sm bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 disabled:opacity-50"
                        :disabled="saving"
                        @click="saveApprovedStudents"
                    >
                        <i v-if="saving" class="fas fa-spinner fa-spin mr-2"></i>
                        <i v-else class="fas fa-save mr-2"></i>
                        {{ isEdit ? 'Update Changes' : 'Save Changes' }}
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-emerald-100">
                        <tr>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700" style="width: 5%">#</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Software ID</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Student Name</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">College Roll</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700" style="width: 15%">Is Passed?</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="(student, index) in students" :key="'std-' + student.id" :class="{ 'bg-amber-50': student._new }">
                            <td class="px-3 py-2 text-center text-slate-600">{{ index + 1 }}</td>
                            <td class="px-3 py-2">
                                <div class="font-medium text-slate-900">{{ student.student_id }}</div>
                                <span v-if="student._new" class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-amber-100 text-amber-800 mt-1">
                                    NEW
                                </span>
                            </td>
                            <td class="px-3 py-2 text-slate-900">{{ student.name }}</td>
                            <td class="px-3 py-2 text-slate-900">{{ student.college_roll }}</td>
                            <td class="px-3 py-2">
                                <div class="flex items-center justify-center gap-4">
                                    <label class="flex items-center cursor-pointer">
                                        <input
                                            type="radio"
                                            :name="'status_' + index"
                                            :id="'pass_' + index"
                                            value="PASSED"
                                            v-model="student.result_status"
                                            class="text-emerald-600 focus:ring-emerald-500"
                                        />
                                        <span :for="'pass_' + index" class="ml-2 text-sm font-medium text-emerald-700">Yes</span>
                                    </label>
                                    <label class="flex items-center cursor-pointer">
                                        <input
                                            type="radio"
                                            :name="'status_' + index"
                                            :id="'fail_' + index"
                                            value="FAILED"
                                            v-model="student.result_status"
                                            class="text-red-600 focus:ring-red-500"
                                        />
                                        <span :for="'fail_' + index" class="ml-2 text-sm font-medium text-red-700">No</span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div v-if="message" class=" border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
            {{ message }}
        </div>
    </div>
</template>

<script>
export default {
    name: 'ApproveStudentManage',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            loading: false,
            searching: false,
            saving: false,
            error: '',
            message: '',
            isEdit: false,
            batchInfo: null,
            mergedNewCount: 0,
            students: [],
            form: {
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
                exam_id: '',
                exam_id_others: '',
            },
        }
    },
    computed: {
        sessions() {
            return Array.isArray(this.systems?.global?.academic_sessions) ? this.systems.global.academic_sessions : []
        },
        qualifications() {
            return Array.isArray(this.systems?.global?.academic_qualifications) ? this.systems.global.academic_qualifications : []
        },
        departments() {
            return Array.isArray(this.systems?.global?.departments) ? this.systems.global.departments : []
        },
        classes() {
            return Array.isArray(this.systems?.global?.academic_classes) ? this.systems.global.academic_classes : []
        },
        exams() {
            return Array.isArray(this.systems?.global?.exams) ? this.systems.global.exams : []
        },
        filteredDepartments() {
            if (!this.form.academic_qualification_id) return this.departments
            return this.departments.filter(d => String(d.academic_qualification_id) === String(this.form.academic_qualification_id))
        },
        filteredClasses() {
            if (!this.form.academic_qualification_id) return this.classes
            return this.classes.filter(c => String(c.academic_qualification_id) === String(this.form.academic_qualification_id))
        },
        isFormValid() {
            return this.form.academic_session_id &&
                   this.form.academic_qualification_id &&
                   this.form.department_id &&
                   this.form.academic_class_id &&
                   this.form.exam_id &&
                   this.form.exam_id_others
        },
    },
    async mounted() {
        const path = window.location.pathname
        if (path.includes('/edit')) {
            this.isEdit = true
            await this.loadBatch()
        }
    },
    methods: {
        async loadBatch() {
            this.loading = true
            this.error = ''

            try {
                const id = window.location.pathname.split('/').slice(-2)[0]
                const res = await window.axios.get(`/admin/approveStudent/${id}/edit`)
                this.batchInfo = res?.data?.batch || null
                this.students = Array.isArray(res?.data?.students) ? res.data.students : []
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load batch data.'
            } finally {
                this.loading = false
            }
        },
        async fetchStudents() {
            this.searching = true
            this.error = ''

            try {
                const res = await window.axios.post('/admin/approveStudent/fetch-students', this.form)
                this.students = Array.isArray(res?.data?.students) ? res.data.students : []
                
                if (this.students.length === 0) {
                    this.error = 'No students found for the selected criteria.'
                }
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to fetch students.'
                this.students = []
            } finally {
                this.searching = false
            }
        },
        async saveApprovedStudents() {
            this.saving = true
            this.error = ''

            try {
                const data = {
                    ...this.form,
                    students: this.students.map(s => ({
                        id: s.id,
                        result_status: s.result_status
                    }))
                }

                let res
                if (this.isEdit) {
                    const id = window.location.pathname.split('/').slice(-2)[0]
                    res = await window.axios.put(`/admin/approveStudent/${id}`, data)
                } else {
                    res = await window.axios.post('/admin/approveStudent', data)
                }

                this.message = res?.data?.message || 'Approved students saved successfully!'
                
                // Redirect to index after successful save
                setTimeout(() => {
                    window.location.href = '/admin/approveStudent'
                }, 1500)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to save approved students.'
            } finally {
                this.saving = false
            }
        },
        statusBadgeClass(status) {
            const s = String(status || '').toLowerCase()
            if (s === 'active') return 'border border-emerald-200 bg-emerald-50 text-emerald-700'
            if (s === 'inactive') return 'border border-red-200 bg-red-50 text-red-700'
            return 'border border-slate-300 bg-slate-50 text-slate-700'
        },
    },
}
</script>
