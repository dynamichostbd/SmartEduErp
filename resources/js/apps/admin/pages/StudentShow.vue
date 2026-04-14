<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Student Details</div>
                    <div class="mt-1 text-sm text-slate-600">{{ student?.name || '' }}</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        @click="goBack"
                    >
                        Back
                    </button>
                    <button
                        type="button"
                        class="rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                        :disabled="loading"
                        @click="goEdit"
                    >
                        Edit
                    </button>
                    <button
                        type="button"
                        class="rounded-sm border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50"
                        :disabled="loading"
                        @click="deactivate"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div v-if="loading" class=" border border-slate-300 bg-white p-5 text-sm text-slate-600">Loading...</div>

        <div v-else class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class=" border border-slate-300 bg-white p-5 lg:col-span-1">
                <div class="flex items-center gap-4">
                    <div class="h-20 w-20 overflow-hidden rounded-xl bg-slate-100">
                        <img v-if="student?.profile" class="h-full w-full object-cover" :src="student.profile" />
                        <div v-else class="flex h-full w-full items-center justify-center text-sm font-semibold text-slate-500">
                            {{ (student?.student_id || '').slice(0, 2) || 'NA' }}
                        </div>
                    </div>
                    <div class="min-w-0">
                        <div class="truncate text-sm font-semibold text-slate-900">{{ student?.student_id || '' }}</div>
                        <div class="truncate text-sm text-slate-700">{{ student?.mobile || '' }}</div>
                        <div class="mt-1 inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" :class="student?.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700'">
                            {{ student?.status || 'n/a' }}
                        </div>
                    </div>
                </div>

                <div class="mt-5 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-slate-500">Session</div>
                        <div class="text-right font-medium text-slate-900">{{ student?.academic_session_name || '' }}</div>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-slate-500">Qualification</div>
                        <div class="text-right font-medium text-slate-900">{{ student?.academic_qualification_name || '' }}</div>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-slate-500">Class</div>
                        <div class="text-right font-medium text-slate-900">{{ student?.academic_class_name || '' }}</div>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-slate-500">Department</div>
                        <div class="text-right font-medium text-slate-900">{{ student?.department_name || '' }}</div>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-slate-500">Hostel</div>
                        <div class="text-right font-medium text-slate-900">{{ student?.hostel_name || '' }}</div>
                    </div>
                </div>
            </div>

            <div class=" border border-slate-300 bg-white p-5 lg:col-span-2">
                <div class="text-sm font-semibold text-slate-900">Basic Information</div>

                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-3">
                        <div class="text-xs font-semibold text-slate-500">Admission ID</div>
                        <div class="mt-1 text-sm font-medium text-slate-900">{{ student?.admission_id || '' }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-3">
                        <div class="text-xs font-semibold text-slate-500">College Roll</div>
                        <div class="mt-1 text-sm font-medium text-slate-900">{{ student?.college_roll || '' }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-3">
                        <div class="text-xs font-semibold text-slate-500">Reg No</div>
                        <div class="mt-1 text-sm font-medium text-slate-900">{{ student?.reg_no || '' }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-3">
                        <div class="text-xs font-semibold text-slate-500">Student Type</div>
                        <div class="mt-1 text-sm font-medium text-slate-900">{{ student?.student_type || '' }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-3">
                        <div class="text-xs font-semibold text-slate-500">Gender</div>
                        <div class="mt-1 text-sm font-medium text-slate-900">{{ student?.gender || '' }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-3">
                        <div class="text-xs font-semibold text-slate-500">Email</div>
                        <div class="mt-1 text-sm font-medium text-slate-900">{{ student?.email || '' }}</div>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-4">
                    <div class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-3">
                        <div class="text-xs font-semibold text-slate-500">Address</div>
                        <div class="mt-1 text-sm font-medium text-slate-900">{{ student?.address || '' }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-3">
                        <div class="text-xs font-semibold text-slate-500">Permanent Address</div>
                        <div class="mt-1 text-sm font-medium text-slate-900">{{ student?.permanent_address || '' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'StudentShow',
    props: {
        studentId: {
            type: [String, Number],
            required: true,
        },
    },
    data() {
        return {
            loading: false,
            error: '',
            student: null,
        }
    },
    mounted() {
        this.fetchDetails()
    },
    methods: {
        goBack() {
            window.location.href = '/admin/student'
        },
        goEdit() {
            window.location.href = `/admin/student/${this.studentId}/edit`
        },
        async fetchDetails() {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/student/${this.studentId}/details`)
                this.student = res?.data?.student || null
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load student details.'
            } finally {
                this.loading = false
            }
        },
        async deactivate() {
            if (!confirm('Are you sure want to delete?')) return
            this.loading = true
            this.error = ''
            try {
                await window.axios.post('/admin/student/bulk-deactivate', { ids: [this.studentId] })
                window.location.href = '/admin/student'
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to delete student.'
            } finally {
                this.loading = false
            }
        },
    },
}
</script>
