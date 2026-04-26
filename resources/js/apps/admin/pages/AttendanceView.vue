<template>
    <div class="flex flex-col gap-4">
        <div v-if="loading" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm text-sm text-slate-600">
            Loading...
        </div>
        <div v-else-if="error" class="rounded-xl border border-red-200 bg-red-50 p-5 shadow-sm text-sm text-red-800">
            {{ error }}
        </div>
        <div v-else-if="attendance" class="flex flex-col gap-4">
            <div class="flex items-center justify-end">
                <button type="button"
                    class="flex items-center gap-2 rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm"
                    @click="printReport">
                    <i class="bx bx-printer"></i> PRINT
                </button>
            </div>

            <div id="printArea" class="flex flex-col gap-4">
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div
                        class="border-b border-slate-100 bg-slate-50 px-4 py-3 text-sm font-bold uppercase text-slate-800">
                        Attendance Info
                    </div>
                    <div class="p-4">
                        <table class="w-full text-sm border-collapse border border-slate-200">
                            <tbody>
                                <tr>
                                    <th
                                        class="border border-slate-200 px-3 py-2 text-left bg-slate-50 font-bold text-slate-700 w-[15%]">
                                        Date</th>
                                    <td class="border border-slate-200 px-3 py-2 text-slate-800 w-[35%]">{{
                                        attendance.date }}</td>
                                    <th class="border border-slate-200 px-3 py-2 bg-slate-50 w-[15%]"></th>
                                    <td class="border border-slate-200 px-3 py-2 w-[35%]"></td>
                                </tr>
                                <tr>
                                    <th
                                        class="border border-slate-200 px-3 py-2 text-left bg-slate-50 font-bold text-slate-700">
                                        Session</th>
                                    <td class="border border-slate-200 px-3 py-2 text-slate-800">{{ sessionName }}</td>
                                    <th
                                        class="border border-slate-200 px-3 py-2 text-left bg-slate-50 font-bold text-slate-700">
                                        Academic Level</th>
                                    <td class="border border-slate-200 px-3 py-2 text-slate-800">{{ qualificationName }}
                                    </td>
                                </tr>
                                <tr>
                                    <th
                                        class="border border-slate-200 px-3 py-2 text-left bg-slate-50 font-bold text-slate-700">
                                        Department/Group</th>
                                    <td class="border border-slate-200 px-3 py-2 text-slate-800">{{ departmentName }}
                                    </td>
                                    <th
                                        class="border border-slate-200 px-3 py-2 text-left bg-slate-50 font-bold text-slate-700">
                                        Class</th>
                                    <td class="border border-slate-200 px-3 py-2 text-slate-800">{{ className }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div
                        class="flex flex-col gap-2 border-b border-slate-100 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-sm font-bold uppercase text-slate-800">Students Attendance</div>
                        <div class="text-sm text-slate-800">
                            <span class="font-bold">Total:</span> <span class="font-bold text-blue-600">{{ totals.total
                                }}</span>
                            <span class="mx-2 text-slate-300">||</span>
                            <span class="font-bold">Present:</span> <span class="font-bold text-emerald-600">{{
                                totals.present }}</span>
                            <span class="mx-2 text-slate-300">||</span>
                            <span class="font-bold">Absent:</span> <span class="font-bold text-rose-600">{{
                                totals.absent }}</span>
                        </div>
                    </div>

                    <div class="p-4">
                        <div class="overflow-y-auto max-h-[400px]">
                            <table v-if="attendance.details && attendance.details.length"
                                class="min-w-full text-sm border-collapse border border-slate-200">
                                <thead class="bg-slate-50 sticky top-0 z-10">
                                    <tr>
                                        <th
                                            class="border border-slate-200 px-3 py-2 text-center font-bold text-slate-800">
                                            #</th>
                                        <th
                                            class="border border-slate-200 px-3 py-2 text-left font-bold text-slate-800">
                                            Software ID</th>
                                        <th
                                            class="border border-slate-200 px-3 py-2 text-left font-bold text-slate-800">
                                            Student Name</th>
                                        <th
                                            class="border border-slate-200 px-3 py-2 text-left font-bold text-slate-800">
                                            Mobile</th>
                                        <th
                                            class="border border-slate-200 px-3 py-2 text-left font-bold text-slate-800">
                                            College Roll</th>
                                        <th
                                            class="border border-slate-200 px-3 py-2 text-center font-bold text-slate-800">
                                            Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    <tr v-for="(detail, idx) in attendance.details" :key="'detail-' + idx"
                                        :class="(idx % 2 !== 0) ? 'bg-slate-50' : 'bg-white'">
                                        <td class="border border-slate-200 px-3 py-2 text-center text-slate-700">{{ idx
                                            + 1 }}</td>
                                        <td class="border border-slate-200 px-3 py-2 text-slate-700">{{
                                            detail.student_id || '--' }}</td>
                                        <td class="border border-slate-200 px-3 py-2 text-slate-700">{{ detail.student ?
                                            detail.student.name : '--' }}</td>
                                        <td class="border border-slate-200 px-3 py-2 text-slate-700">{{ detail.student ?
                                            detail.student.mobile : '--' }}</td>
                                        <td class="border border-slate-200 px-3 py-2 text-slate-700">{{ detail.student ?
                                            detail.student.college_roll : '--' }}</td>
                                        <td class="border border-slate-200 px-3 py-2 text-center">
                                            <span v-if="detail.status === 'P'"
                                                class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-bold text-emerald-700 tracking-wide">
                                                <i class="bx bxs-circle"></i> PRESENT
                                            </span>
                                            <span v-else
                                                class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-3 py-1 text-[11px] font-bold text-rose-700 tracking-wide">
                                                <i class="bx bxs-circle"></i> ABSENT
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-else class="text-center my-6 text-slate-600 font-semibold">No attendance details
                                found.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-start mt-2">
                <button type="button"
                    class="rounded-sm border border-slate-300 bg-white px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 shadow-sm"
                    @click="goBack">Back</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AttendanceView',
    props: {
        systems: { type: Object, default: () => ({}) },
        attendanceId: { type: [Number, String], required: true },
    },
    data() {
        return {
            loading: true,
            error: '',
            attendance: null,
        }
    },
    computed: {
        sessionName() {
            if (!this.attendance) return '--'
            if (this.attendance.academic_session?.name) return this.attendance.academic_session.name
            if (this.attendance.academic_session_name) return this.attendance.academic_session_name
            const s = (this.systems?.global?.academic_sessions || []).find(x => String(x.id) === String(this.attendance.academic_session_id))
            return s?.name || '--'
        },
        qualificationName() {
            if (!this.attendance) return '--'
            if (this.attendance.qualification?.name) return this.attendance.qualification.name
            if (this.attendance.academic_qualification_name) return this.attendance.academic_qualification_name
            const q = (this.systems?.global?.academic_qualifications || []).find(x => String(x.id) === String(this.attendance.academic_qualification_id))
            return q?.name || '--'
        },
        departmentName() {
            if (!this.attendance) return '--'
            if (this.attendance.department?.name) return this.attendance.department.name
            if (this.attendance.department_name) return this.attendance.department_name
            const d = (this.systems?.global?.departments || []).find(x => String(x.id) === String(this.attendance.department_id))
            return d?.name || '--'
        },
        className() {
            if (!this.attendance) return '--'
            if (this.attendance.academic_class?.name) return this.attendance.academic_class.name
            if (this.attendance.academic_class_name) return this.attendance.academic_class_name
            const c = (this.systems?.global?.academic_classes || []).find(x => String(x.id) === String(this.attendance.academic_class_id))
            return c?.name || '--'
        },
        totals() {
            if (!this.attendance || !this.attendance.details) return { total: 0, present: 0, absent: 0 }
            const total = this.attendance.details.length
            const present = this.attendance.details.filter(d => String(d.status || 'P') === 'P').length
            return { total, present, absent: total - present }
        }
    },
    async mounted() {
        await this.loadData()
    },
    methods: {
        async loadData() {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/attendance/${this.attendanceId}/details`)
                if (res?.data?.data) {
                    this.attendance = res.data.data
                    // Old backend might return data directly in res.data, let's gracefully handle that:
                } else if (res?.data?.attendance) {
                    this.attendance = res.data.attendance
                } else {
                    this.attendance = res.data
                }

                // Fetch full details if they are separated like in the edit (though old old view fetched them together via implicit backend endpoint). Let's see if details are in there.
                if (!this.attendance.details && res?.data?.details) {
                    this.attendance.details = res.data.details
                }
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load attendance record.'
            } finally {
                this.loading = false
            }
        },
        goBack() {
            window.location.href = '/admin/attendance'
        },
        printReport() {
            const printContent = document.getElementById('printArea').innerHTML
            const originalContents = document.body.innerHTML
            document.body.innerHTML = printContent
            window.print()
            document.body.innerHTML = originalContents
            window.location.reload()
        }
    }
}
</script>
