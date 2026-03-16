<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Admission Fee Setup</div>
                    <div class="mt-1 text-sm text-slate-600">{{ isEdit ? 'Edit' : 'Create' }}</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goBack">Back</button>
                    <button type="button" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="submitting" @click="submit">{{ submitting ? '...' : 'Save' }}</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Purpose</div>
                    <select v-model="form.account_head_id" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">--Select Any--</option>
                        <option v-for="h in admissionHeads" :key="'h-' + h.id" :value="String(h.id)">{{ h.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Amount</div>
                    <input v-model="form.amount" type="number" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Store ID</div>
                    <input v-model="form.store_id" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Store Password</div>
                    <input v-model="form.store_password" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Account No</div>
                    <input v-model="form.account_no" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Timeline</div>
                    <div class="mt-2 flex items-center gap-2">
                        <input id="enable_date" v-model="form.enable_date" type="checkbox" class="h-4 w-4" :true-value="1" :false-value="0" />
                        <label for="enable_date" class="text-sm text-slate-700">Enable Date</label>
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Roll Validation</div>
                    <div class="mt-2 flex items-center gap-2">
                        <input id="enable_roll" v-model="form.enable_roll" type="checkbox" class="h-4 w-4" :true-value="1" :false-value="0" />
                        <label for="enable_roll" class="text-sm text-slate-700">Enable Roll</label>
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Session Applicable</div>
                    <div class="mt-2 flex items-center gap-2">
                        <input id="session" v-model="form.session" type="checkbox" class="h-4 w-4" :true-value="1" :false-value="0" />
                        <label for="session" class="text-sm text-slate-700">Session</label>
                    </div>
                </div>

                <div v-if="form.session" class="lg:col-span-12">
                    <div class="text-xs font-semibold text-slate-600">Select Sessions</div>
                    <div class="mt-2 flex flex-wrap gap-2 rounded-xl border border-slate-200 p-3">
                        <label v-for="s in sessionsSorted" :key="'ss-' + s.id" class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" :value="Number(s.id)" v-model="form.academic_session_ids" class="h-4 w-4" />
                            <span>{{ s.name }}</span>
                        </label>
                    </div>
                </div>

                <div v-if="form.enable_date" class="lg:col-span-4">
                    <div class="text-xs font-semibold text-slate-600">Start Date</div>
                    <input v-model="form.start_date" type="datetime-local" step="1" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>
                <div v-if="form.enable_date" class="lg:col-span-4">
                    <div class="text-xs font-semibold text-slate-600">Expire Date</div>
                    <input v-model="form.expire_date" type="datetime-local" step="1" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>
                <div v-if="form.enable_date" class="lg:col-span-4">
                    <div class="text-xs font-semibold text-slate-600">Additional Date</div>
                    <input v-model="form.additional_date" type="datetime-local" step="1" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div v-if="form.enable_roll" class="lg:col-span-6">
                    <div class="text-xs font-semibold text-slate-600">Admission Roll Minimum Value</div>
                    <input v-model="form.admission_roll_min_value" type="number" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>
                <div v-if="form.enable_roll" class="lg:col-span-6">
                    <div class="text-xs font-semibold text-slate-600">Admission Roll Maximum Value</div>
                    <input v-model="form.admission_roll_max_value" type="number" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AdmissionFeeSetupCreate',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        feeSetupId: {
            type: [String, Number],
            default: null,
        },
    },
    data() {
        return {
            loading: false,
            submitting: false,
            error: '',
            form: {
                account_head_id: '',
                amount: '',
                store_id: '',
                store_password: '',
                account_no: '',
                enable_date: 0,
                enable_roll: 0,
                start_date: '',
                expire_date: '',
                additional_date: '',
                session: 0,
                academic_session_ids: [],
                admission_roll_min_value: 0,
                admission_roll_max_value: 0,
            },
        }
    },
    computed: {
        isEdit() {
            return !!this.feeSetupId
        },
        sessions() {
            const list = this.systems?.global?.academic_sessions
            return Array.isArray(list) ? list : []
        },
        sessionsSorted() {
            const list = [...this.sessions]
            const sortKey = (s) => {
                const name = String(s?.name || '').trim()
                const m = name.match(/(19|20)\d{2}/)
                return m ? Number(m[0]) : 0
            }
            return list.sort((a, b) => sortKey(b) - sortKey(a))
        },
        admissionHeads() {
            const raw = this.systems?.global?.admission_heads || {}
            return Object.keys(raw)
                .map((k) => ({ id: Number(k), name: raw[k] }))
                .filter((x) => Number.isFinite(x.id) && x.name != null)
        },
    },
    created() {
        this.load()
    },
    watch: {
        feeSetupId() {
            this.load()
        },
    },
    methods: {
        goBack() {
            window.history.pushState({}, '', '/admin/admissionFeeSetup')
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        apiDatetime(v) {
            if (!v) return ''
            const s = String(v)
            return s.replace('T', ' ')
        },
        normalizeSessions(v) {
            if (Array.isArray(v)) return v.map((x) => Number(x)).filter((x) => Number.isFinite(x))
            if (typeof v === 'string') {
                try {
                    const d = JSON.parse(v)
                    if (Array.isArray(d)) return d.map((x) => Number(x)).filter((x) => Number.isFinite(x))
                } catch {
                    return []
                }
            }
            return []
        },
        async load() {
            this.error = ''
            if (!this.isEdit) return

            this.loading = true
            try {
                const res = await window.axios.get(`/admin/admissionFeeSetup/${this.feeSetupId}`)
                const row = res?.data || null
                if (!row) return

                this.form.account_head_id = row?.account_head_id != null ? String(row.account_head_id) : ''
                this.form.amount = row?.amount != null ? String(row.amount) : ''
                this.form.store_id = row?.store_id != null ? String(row.store_id) : ''
                this.form.store_password = row?.store_password != null ? String(row.store_password) : ''
                this.form.account_no = row?.account_no != null ? String(row.account_no) : ''
                this.form.enable_date = Number(row?.enable_date || 0)
                this.form.enable_roll = Number(row?.enable_roll || 0)
                this.form.session = Number(row?.session || 0)
                this.form.start_date = row?.start_date ? String(row.start_date).replace(' ', 'T') : ''
                this.form.expire_date = row?.expire_date ? String(row.expire_date).replace(' ', 'T') : ''
                this.form.additional_date = row?.additional_date ? String(row.additional_date).replace(' ', 'T') : ''
                this.form.admission_roll_min_value = row?.admission_roll_min_value != null ? Number(row.admission_roll_min_value) : 0
                this.form.admission_roll_max_value = row?.admission_roll_max_value != null ? Number(row.admission_roll_max_value) : 0
                this.form.academic_session_ids = this.normalizeSessions(row?.academic_session_ids)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load fee setup.'
            } finally {
                this.loading = false
            }
        },
        validate() {
            if (!this.form.account_head_id) return 'Purpose is required'
            if (this.form.amount === '' || this.form.amount == null) return 'Amount is required'
            if (!this.form.store_id) return 'Store ID is required'
            if (!this.form.store_password) return 'Store Password is required'
            return ''
        },
        async submit() {
            this.error = ''
            const msg = this.validate()
            if (msg) {
                this.error = msg
                return
            }

            this.submitting = true
            try {
                const payload = {
                    ...this.form,
                    account_head_id: Number(this.form.account_head_id),
                    amount: Number(this.form.amount),
                    enable_date: Number(this.form.enable_date || 0),
                    enable_roll: Number(this.form.enable_roll || 0),
                    session: Number(this.form.session || 0),
                    start_date: this.apiDatetime(this.form.start_date),
                    expire_date: this.apiDatetime(this.form.expire_date),
                    additional_date: this.apiDatetime(this.form.additional_date),
                }

                if (this.isEdit) {
                    await window.axios.put(`/admin/admissionFeeSetup/${this.feeSetupId}`, payload)
                } else {
                    await window.axios.post('/admin/admissionFeeSetup', payload)
                }

                this.goBack()
            } catch (e) {
                this.error = e?.response?.data?.message || (this.isEdit ? 'Update failed.' : 'Create failed.')
            } finally {
                this.submitting = false
            }
        },
    },
}
</script>
