<template>
    <form class="flex flex-col gap-4" @submit.prevent="submit">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Online Admission Roll Verify</div>
                    <div class="mt-1 text-sm text-slate-600">Upload roll list from Excel/CSV</div>
                </div>
            </div>
        </div>

        <div v-if="message" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
            {{ message }}
        </div>

        <div v-if="error" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="flex flex-col gap-4 lg:col-span-8">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-900">Import Details</div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Select Session <span class="text-red-600">*</span></div>
                            <select
                                v-model="form.academic_session_id"
                                class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                required
                            >
                                <option value="">Select Session</option>
                                <option v-for="s in sessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Academic Level <span class="text-red-600">*</span></div>
                            <select
                                v-model="form.academic_qualification_id"
                                class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                required
                            >
                                <option value="">Select Level</option>
                                <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Department <span class="text-red-600">*</span></div>
                            <select
                                v-model="form.department_id"
                                class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                required
                            >
                                <option value="">Select Department</option>
                                <option v-for="d in departments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Class <span class="text-red-600">*</span></div>
                            <select
                                v-model="form.academic_class_id"
                                class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                required
                            >
                                <option value="">Select Class</option>
                                <option v-for="c in classes" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2 lg:col-span-3">
                            <div class="text-xs font-semibold text-slate-600">Excel File Upload <span class="text-red-600">*</span></div>
                            <input
                                ref="fileInput"
                                type="file"
                                accept=".csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                                class="mt-1 block h-9 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm"
                                @change="onPickFile"
                                required
                            />
                            <div class="mt-1 text-xs text-slate-500">1st column = admission roll, 2nd column = name (optional)</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-4 lg:col-span-4">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-900">Status</div>
                    <div class="mt-2 text-sm text-slate-600">Select session, level, department and class, then upload an Excel/CSV file.</div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-900">Actions</div>

                    <div class="mt-4 flex flex-col gap-2">
                        <button
                            type="button"
                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                            :disabled="submitting"
                            @click="goBack"
                        >
                            Back
                        </button>
                        <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="submitting">
                            {{ submitting ? 'Processing...' : 'Submit' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
export default {
    name: 'OnlineAdmissionRollVerifyCreate',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            submitting: false,
            error: '',
            message: '',
            file: null,
            form: {
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
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
            window.location.href = '/admin/onlineAdmissionRollVerify'
        },
        onPickFile(e) {
            const f = e?.target?.files?.[0] || null
            this.file = f
        },
        buildFormData() {
            const fd = new FormData()
            fd.append('academic_session_id', this.form.academic_session_id)
            fd.append('academic_qualification_id', this.form.academic_qualification_id)
            fd.append('department_id', this.form.department_id)
            fd.append('academic_class_id', this.form.academic_class_id)
            if (this.file) fd.append('excel_file', this.file)
            return fd
        },
        async submit() {
            this.submitting = true
            this.error = ''
            this.message = ''
            try {
                const res = await window.axios.post('/admin/onlineAdmissionRollVerify/import', this.buildFormData(), {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                })
                this.message = res?.data?.message || 'Import Successfully'
                setTimeout(() => {
                    window.location.href = '/admin/onlineAdmissionRollVerify'
                }, 300)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to import.'
            } finally {
                this.submitting = false
            }
        },
    },
}
</script>
