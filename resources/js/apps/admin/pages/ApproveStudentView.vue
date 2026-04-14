<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Approved Student Batch Details</div>
                    <div class="mt-1 text-sm text-slate-600">View batch information and student approval status</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <router-link
                        :to="{ name: 'approveStudent.index' }"
                        class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                    >
                        <i class="fas fa-arrow-left mr-2"></i>Back to List
                    </router-link>
                    <router-link
                        :to="{ name: 'approveStudent.edit', params: { id: batchId } }"
                        class="rounded-sm bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800"
                    >
                        <i class="fas fa-edit mr-2"></i>Edit Batch
                    </router-link>
                </div>
            </div>
        </div>

        <div v-if="loading" class=" border border-slate-300 bg-white p-10 text-center">
            <i class="fas fa-spinner fa-spin text-2xl text-slate-400"></i>
            <div class="mt-2 text-sm text-slate-600">Loading...</div>
        </div>

        <div v-else-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <template v-else-if="batchInfo">
            <!-- Batch Information -->
            <div class=" border border-slate-300 bg-white p-5">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-slate-900">Batch Information</h3>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
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
                        <div class="text-sm font-semibold text-slate-600">Source Exam</div>
                        <div class="mt-1 text-slate-900">{{ batchInfo.exam?.name || '—' }}</div>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-slate-600">Target Exam</div>
                        <div class="mt-1 text-slate-900">{{ batchInfo.exam_others?.name || '—' }}</div>
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
                    <div>
                        <div class="text-sm font-semibold text-slate-600">Created</div>
                        <div class="mt-1 text-slate-900">{{ formatDate(batchInfo.created_at) }}</div>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-slate-600">Last Updated</div>
                        <div class="mt-1 text-slate-900">{{ formatDate(batchInfo.updated_at) }}</div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class=" border border-slate-300 bg-white p-5">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-slate-900">Approval Statistics</h3>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div class="rounded-xl border border-slate-300 bg-slate-50 p-4">
                        <div class="text-2xl font-bold text-slate-900">{{ students.length }}</div>
                        <div class="text-sm text-slate-600">Total Students</div>
                    </div>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                        <div class="text-2xl font-bold text-emerald-700">{{ passedCount }}</div>
                        <div class="text-sm text-emerald-600">Passed</div>
                    </div>
                    <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                        <div class="text-2xl font-bold text-red-700">{{ failedCount }}</div>
                        <div class="text-sm text-red-600">Failed</div>
                    </div>
                    <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                        <div class="text-2xl font-bold text-blue-700">{{ passRate }}%</div>
                        <div class="text-sm text-blue-600">Pass Rate</div>
                    </div>
                </div>
            </div>

            <!-- Students Table -->
            <div class=" border border-slate-300 bg-white p-5">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-slate-900">Students List</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-emerald-100">
                            <tr>
                                <th class="px-3 py-2 text-center font-semibold text-slate-700" style="width: 5%">#</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Software ID</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Student Name</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">College Roll</th>
                                <th class="px-3 py-2 text-center font-semibold text-slate-700" style="width: 15%">Result Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            <tr v-for="(student, index) in students" :key="'std-' + student.id">
                                <td class="px-3 py-2 text-center text-slate-600">{{ index + 1 }}</td>
                                <td class="px-3 py-2">
                                    <div class="font-medium text-slate-900">{{ student.student_id }}</div>
                                </td>
                                <td class="px-3 py-2 text-slate-900">{{ student.name }}</td>
                                <td class="px-3 py-2 text-slate-900">{{ student.college_roll }}</td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center justify-center">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                            :class="resultStatusClass(student.result_status)"
                                        >
                                            <i :class="resultStatusIcon(student.result_status)" class="mr-1"></i>
                                            {{ student.result_status || '—' }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
export default {
    name: 'ApproveStudentView',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            loading: false,
            error: '',
            batchInfo: null,
            students: [],
            batchId: null,
        }
    },
    computed: {
        passedCount() {
            return this.students.filter(s => s.result_status === 'PASSED').length
        },
        failedCount() {
            return this.students.filter(s => s.result_status === 'FAILED').length
        },
        passRate() {
            if (this.students.length === 0) return 0
            return Math.round((this.passedCount / this.students.length) * 100)
        },
    },
    async mounted() {
        this.batchId = window.location.pathname.split('/').pop()
        await this.loadBatch()
    },
    methods: {
        async loadBatch() {
            this.loading = true
            this.error = ''

            try {
                const res = await window.axios.get(`/admin/approveStudent/${this.batchId}`)
                this.batchInfo = res?.data?.batch || null
                this.students = Array.isArray(res?.data?.students) ? res.data.students : []
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load batch data.'
            } finally {
                this.loading = false
            }
        },
        statusBadgeClass(status) {
            const s = String(status || '').toLowerCase()
            if (s === 'active') return 'border border-emerald-200 bg-emerald-50 text-emerald-700'
            if (s === 'inactive') return 'border border-red-200 bg-red-50 text-red-700'
            return 'border border-slate-300 bg-slate-50 text-slate-700'
        },
        resultStatusClass(status) {
            const s = String(status || '').toUpperCase()
            if (s === 'PASSED') return 'border border-emerald-200 bg-emerald-50 text-emerald-700'
            if (s === 'FAILED') return 'border border-red-200 bg-red-50 text-red-700'
            return 'border border-slate-300 bg-slate-50 text-slate-700'
        },
        resultStatusIcon(status) {
            const s = String(status || '').toUpperCase()
            if (s === 'PASSED') return 'fas fa-check-circle'
            if (s === 'FAILED') return 'fas fa-times-circle'
            return 'fas fa-question-circle'
        },
        formatDate(dateStr) {
            if (!dateStr) return '—'
            try {
                return new Date(dateStr).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                })
            } catch {
                return dateStr
            }
        },
    },
}
</script>
