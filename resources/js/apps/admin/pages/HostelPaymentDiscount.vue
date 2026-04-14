<template>
    <div class="min-h-screen bg-slate-50">
        <div class="mx-auto">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-slate-900">Hostel Payment Discount</h1>
                <p class="text-slate-600">Manage student discounts for hostel payments</p>
            </div>

            <!-- Search Filters -->
            <div class="border border-slate-300 bg-white p-5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Hostel *</label>
                        <select v-model="filters.hostel_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                            <option value="">--Select Hostel--</option>
                            <option v-for="hostel in hostels" :key="hostel.id" :value="hostel.id">{{ hostel.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Student Type</label>
                        <select v-model="filters.discount_student" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                            <option value="">All Student</option>
                            <option value="1">Discount Student</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Search Field</label>
                        <select v-model="filters.field_name" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                            <option v-for="(field, key) in fields_name" :key="key" :value="key">{{ field }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Search</label>
                        <input v-model="filters.value" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="Type your text" />
                    </div>
                    <div class="flex items-end">
                        <button @click="searchStudents" :disabled="loading" class="h-9 w-full rounded-sm bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50">
                            <span v-if="loading">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Loading...
                            </span>
                            <span v-else>
                                <i class="fas fa-search mr-2"></i>
                                Search
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Students Table -->
            <div class="mt-6 border border-slate-300 bg-white p-5">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-slate-900">Students</h2>
                </div>

                <div class="overflow-x-auto">
                    <table v-if="students && students.length > 0" class="min-w-full border-collapse border border-slate-300">
                        <thead class="bg-emerald-100">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-700">
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">#</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Software ID</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Student</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Admission ID</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Session</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Dept. / Group</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Academic Level / Class</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">Discount</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <tr v-for="(student, key) in students" :key="student.id" :class="key % 2 === 0 ? 'bg-white' : 'bg-slate-50'">
                                <td class="border border-slate-300 px-1 py-1 text-center text-sm">{{ key + 1 }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-sm text-slate-800">{{ student.student_id }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-sm text-slate-800">
                                    <div>{{ student.name }}</div>
                                    <div class="text-xs text-slate-500">{{ student.mobile }}</div>
                                </td>
                                <td class="border border-slate-300 px-1 py-1 text-sm text-slate-800">{{ student.admission_id }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-sm text-slate-800">{{ student.academic_session?.name || '' }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-sm text-slate-800">{{ student.department?.name || '' }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-sm text-slate-800">
                                    <div>{{ student.qualification?.name || '' }}</div>
                                    <div class="text-xs text-slate-500">({{ student.academic_class?.name || '' }})</div>
                                </td>
                                <td class="border border-slate-300 px-1 py-1">
                                    <div class="flex justify-center">
                                        <input
                                            type="number"
                                            class="h-9 w-[120px] rounded-sm border border-slate-300 bg-white px-3 text-center text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                            v-model="student.hostel_discount_percent"
                                            placeholder="ex: 1000"
                                            @keyup.enter="discountStudent(student)"
                                        />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-else class="text-center py-8 text-slate-500">
                        No Students Found
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'HostelPaymentDiscount',
    props: ['systems'],
    data() {
        return {
            loading: false,
            searchTimer: null,
            students: [],
            filters: {
                field_name: 'mobile',
                value: '',
                hostel_id: '',
                discount_student: ''
            },
            fields_name: {
                0: 'Select One',
                student_id: 'Software ID',
                name: 'Name',
                mobile: 'Mobile',
                reg_no: 'Reg No.',
                admission_id: 'Admission ID',
            }
        }
    },
    computed: {
        hostels() {
            return this.systems?.global?.hostels || []
        }
    },
    watch: {
        'filters.hostel_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch()
        },
        'filters.discount_student'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch()
        },
        'filters.field_name'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch()
        },
        'filters.value'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleSearch()
        },
    },
    methods: {
        scheduleSearch() {
            if (this.searchTimer) clearTimeout(this.searchTimer)
            this.searchTimer = setTimeout(() => {
                this.searchStudents(true)
            }, 250)
        },
        async searchStudents(silent = false) {
            if (!this.filters.hostel_id) {
                if (!silent) this.$toast.error('Please select a hostel')
                this.students = []
                return
            }

            this.loading = true
            try {
                const response = await window.axios.post('/admin/hostelPayment-students', this.filters)
                this.students = response.data || []
            } catch (error) {
                if (!silent) this.$toast.error('Failed to load students')
            } finally {
                this.loading = false
            }
        },
        async discountStudent(student) {
            if (confirm('Are you sure want to discount this student?')) {
                this.loading = true
                try {
                    const data = {
                        id: student.id,
                        discount: student.hostel_discount_percent
                    }
                    const response = await window.axios.post('/admin/hostelPayment-discount', data)
                    this.$toast.success(response.data.message || 'Discount updated successfully')
                    await this.searchStudents()
                } catch (error) {
                    this.$toast.error('Failed to update discount')
                } finally {
                    this.loading = false
                }
            }
        }
    },
    mounted() {
        // Load initial data if needed
    }
}
</script>
