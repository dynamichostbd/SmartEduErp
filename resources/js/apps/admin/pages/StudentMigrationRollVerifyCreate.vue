<template>
    <form class="flex flex-col gap-4" @submit.prevent="submit">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Migration Roll Verify</div>
                    <div class="mt-1 text-sm text-slate-600">Upload roll list from Excel/CSV</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        :disabled="submitting"
                        @click="goBack"
                    >
                        Back
                    </button>
                    <button
                        type="submit"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                        :disabled="submitting"
                    >
                        {{ submitting ? 'Processing...' : 'Submit' }}
                    </button>
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
            <div class="space-y-4 lg:col-span-8 xl:col-span-9">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-900">Upload File</div>
                    <div class="mt-1 text-xs text-slate-500">First column must be Admission Roll</div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Select Session <span class="text-red-600">*</span></div>
                            <select v-model="form.academic_session_id" class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required>
                                <option value="">Select Session</option>
                                <option v-for="s in sessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Migrate Department <span class="text-red-600">*</span></div>
                            <select v-model="form.migrate_department_id" class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required>
                                <option value="">Select Department</option>
                                <option v-for="d in departments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2 lg:col-span-3">
                            <div class="text-xs font-semibold text-slate-600">Excel File Upload <span class="text-red-600">*</span></div>
                            <input
                                ref="fileInput"
                                type="file"
                                accept=".csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                                class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                @change="onPickFile"
                                required
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4 lg:col-span-4 xl:col-span-3">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-900">Actions</div>

                    <button type="submit" class="mt-4 h-9 w-full rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800" :disabled="submitting">
                        {{ submitting ? 'Processing...' : 'Submit' }}
                    </button>

                    <button type="button" class="mt-2 h-9 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="submitting" @click="goBack">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
export default {
    name: 'StudentMigrationRollVerifyCreate',
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
                migrate_department_id: '',
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
        departments() {
            const list = this.systems?.global?.departments
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
            window.location.href = '/admin/studentMigrationRollVerify'
        },
        onPickFile(e) {
            const f = e?.target?.files?.[0] || null
            this.file = f
        },
        buildFormData() {
            const fd = new FormData()
            fd.append('academic_session_id', this.form.academic_session_id)
            fd.append('migrate_department_id', this.form.migrate_department_id)
            if (this.file) fd.append('excel_file', this.file)
            return fd
        },
        async submit() {
            this.submitting = true
            this.error = ''
            this.message = ''
            try {
                const res = await window.axios.post('/admin/studentMigrationRollVerify/import', this.buildFormData(), {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                })
                this.message = res?.data?.message || 'Create Successfully!'
                setTimeout(() => {
                    window.location.href = '/admin/studentMigrationRollVerify'
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
