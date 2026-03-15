<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">{{ isEdit ? 'Edit Academic Level' : 'Create Academic Level' }}</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">Back</button>
                    <button type="button" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="saving" @click="submit">{{ saving ? '...' : isEdit ? 'Update' : 'Create' }}</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>
        <div v-if="success" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ success }}</div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="lg:col-span-9">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                        <div class="lg:col-span-12">
                            <div class="text-xs font-semibold text-slate-600">Qualification Name</div>
                            <input v-model="form.name" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div class="lg:col-span-12">
                            <div class="text-xs font-semibold text-slate-600">Description</div>
                            <textarea v-model="form.description" rows="4" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"></textarea>
                        </div>

                        <div class="lg:col-span-4">
                            <div class="text-xs font-semibold text-slate-600">Is Registration?</div>
                            <select v-model="form.registration" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="1">Yes</option>
                                <option :value="0">No</option>
                            </select>
                        </div>

                        <div class="lg:col-span-4">
                            <div class="text-xs font-semibold text-slate-600">Online Admission?</div>
                            <select v-model="form.online_admission" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="1">Yes</option>
                                <option :value="0">No</option>
                            </select>
                        </div>

                        <div class="lg:col-span-4">
                            <div class="text-xs font-semibold text-slate-600">Application Fee</div>
                            <select v-model="form.application_fee" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="1">Yes</option>
                                <option :value="0">No</option>
                            </select>
                        </div>

                        <div class="lg:col-span-4">
                            <div class="text-xs font-semibold text-slate-600">Check Application Fees</div>
                            <select v-model="form.application_fees" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="1">Yes</option>
                                <option :value="0">No</option>
                            </select>
                        </div>

                        <div class="lg:col-span-4">
                            <div class="text-xs font-semibold text-slate-600">Admission Roll Verify</div>
                            <select v-model="form.admission_roll_verify" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="1">Yes</option>
                                <option :value="0">No</option>
                            </select>
                        </div>

                        <div class="lg:col-span-4">
                            <div class="text-xs font-semibold text-slate-600">Subject Choose</div>
                            <select v-model="form.subject_choose" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="1">Yes</option>
                                <option :value="0">No</option>
                            </select>
                        </div>

                        <div class="lg:col-span-4">
                            <div class="text-xs font-semibold text-slate-600">Migration</div>
                            <select v-model="form.migration" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="1">Yes</option>
                                <option :value="0">No</option>
                            </select>
                        </div>

                        <div class="lg:col-span-12">
                            <div class="text-sm font-semibold text-slate-900">Admission Files</div>
                            <div class="mt-2 rounded-xl border border-slate-200 p-4">
                                <div v-if="!allFiles.length" class="text-sm text-slate-500">No files list configured.</div>
                                <div v-else class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                    <label v-for="(f, idx) in admissionFiles" :key="f.key || idx" class="flex items-start gap-3 rounded-lg border border-slate-200 bg-white p-3">
                                        <input v-model="f.checked" type="checkbox" class="mt-1 h-4 w-4" />
                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm font-semibold text-slate-900">{{ f.name_bn || f.name_en || f.key }}</div>
                                            <div class="mt-1 text-xs text-slate-500">{{ f.name_en || '' }}</div>
                                            <label class="mt-2 inline-flex items-center gap-2 text-xs font-semibold text-slate-700">
                                                <input v-model="f.optional" type="checkbox" class="h-4 w-4" :disabled="!f.checked" />
                                                Optional
                                            </label>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-12">
                            <div class="text-sm font-semibold text-slate-900">Commitment</div>
                            <textarea v-model="form.commitment" rows="6" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">Status</div>
                    <select v-model="form.status" class="mt-2 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="active">Active</option>
                        <option value="deactive">Deactive</option>
                        <option value="draft">Draft</option>
                    </select>

                    <button type="button" class="mt-4 h-10 w-full rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800" :disabled="saving" @click="submit">{{ saving ? '...' : isEdit ? 'Update' : 'Create' }}</button>
                    <button type="button" class="mt-2 h-10 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AcademicQualificationManage',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        qualificationId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            saving: false,
            error: '',
            success: '',
            admissionFiles: [],
            form: {
                name: '',
                description: '',
                registration: 0,
                online_admission: 0,
                application_fee: 1,
                application_fees: 1,
                admission_roll_verify: 0,
                subject_choose: 0,
                migration: 0,
                commitment: '',
                status: 'active',
            },
        }
    },
    computed: {
        isEdit() {
            return !!this.qualificationId
        },
        allFiles() {
            const f = this.systems?.global?.files
            return Array.isArray(f) ? f : []
        },
    },
    created() {
        this.resetAdmissionFiles()
        if (this.isEdit) this.load()
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
            this.go('/admin/academicQualification')
        },
        resetAdmissionFiles(existingSelected = []) {
            const selectedArr = Array.isArray(existingSelected) ? existingSelected : []
            const selectedByKey = {}
            for (const s of selectedArr) {
                if (!s || !s.key) continue
                selectedByKey[String(s.key)] = s
            }

            this.admissionFiles = (this.allFiles || []).map((x) => {
                const key = String(x?.key || '')
                const existing = selectedByKey[key]
                return {
                    ...x,
                    checked: existing ? true : false,
                    optional: existing ? !!existing.optional : false,
                }
            })
        },
        parseAdmissionFiles(raw) {
            if (!raw) return []
            if (Array.isArray(raw)) return raw
            try {
                const parsed = JSON.parse(String(raw))
                return Array.isArray(parsed) ? parsed : []
            } catch {
                return []
            }
        },
        async load() {
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/academicQualification/${this.qualificationId}`)
                const row = res?.data?.academic_qualification || res?.data?.data || res?.data || {}

                this.form.name = row?.name || ''
                this.form.description = row?.description || ''
                this.form.registration = Number(row?.registration || 0)
                this.form.online_admission = Number(row?.online_admission || 0)
                this.form.application_fee = Number(row?.application_fee ?? 1)
                this.form.application_fees = Number(row?.application_fees ?? 1)
                this.form.admission_roll_verify = Number(row?.admission_roll_verify || 0)
                this.form.subject_choose = Number(row?.subject_choose || 0)
                this.form.migration = Number(row?.migration || 0)
                this.form.commitment = row?.commitment || ''
                this.form.status = row?.status || 'active'

                const existingFiles = this.parseAdmissionFiles(row?.admission_files)
                this.resetAdmissionFiles(existingFiles)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load academic level.'
            }
        },
        async submit() {
            this.saving = true
            this.error = ''
            this.success = ''

            try {
                const selectedAdmissionFiles = (this.admissionFiles || [])
                    .filter((f) => f.checked)
                    .map((f) => ({
                        key: f.key,
                        name_en: f.name_en,
                        name_bn: f.name_bn,
                        optional: !!f.optional,
                    }))

                const payload = {
                    name: this.form.name,
                    description: this.form.description,
                    registration: this.form.registration ? 1 : 0,
                    online_admission: this.form.online_admission ? 1 : 0,
                    application_fee: this.form.application_fee ? 1 : 0,
                    application_fees: this.form.application_fees ? 1 : 0,
                    admission_roll_verify: this.form.admission_roll_verify ? 1 : 0,
                    subject_choose: this.form.subject_choose ? 1 : 0,
                    migration: this.form.migration ? 1 : 0,
                    commitment: this.form.commitment,
                    status: this.form.status,
                    selected_admission_files: selectedAdmissionFiles,
                }

                let res
                if (this.isEdit) {
                    res = await window.axios.put(`/admin/academicQualification/${this.qualificationId}`, payload)
                } else {
                    res = await window.axios.post('/admin/academicQualification', payload)
                }

                const msg = res?.data?.message || (this.isEdit ? 'Update Successfully!' : 'Create Successfully!')
                this.success = msg
                this.toast(msg, 'success')

                if (!this.isEdit) {
                    const id = res?.data?.id
                    if (id) {
                        this.go(`/admin/academicQualification/${id}/edit`)
                    } else {
                        this.goIndex()
                    }
                }
            } catch (e) {
                this.error = e?.response?.data?.message || e?.response?.data?.exception || 'Save failed.'
                this.toast(this.error, 'error')
            } finally {
                this.saving = false
            }
        },
    },
}
</script>
