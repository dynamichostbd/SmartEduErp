<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Generate Hostel Fees</div>
                    <div class="mt-1 text-sm text-slate-600">Create fee invoices for a selected hostel and financial year</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">Back</button>
                    <button type="button" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="saving" @click="submit">{{ saving ? '...' : isEdit ? 'Update' : 'Generate Fees' }}</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>
        <div v-if="success" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ success }}</div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="space-y-4 lg:col-span-8 xl:col-span-9">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-slate-900">Filters</div>
                        <div class="mt-1 text-xs text-slate-500">Select hostel and financial year</div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Hostel *</div>
                            <select v-model="form.hostel_id" class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" :disabled="saving">
                                <option :value="null">Select Hostel</option>
                                <option v-for="h in hostels" :key="'h-' + h.id" :value="Number(h.id)">{{ h.name }}</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Select Financial Year *</div>
                            <select v-model="form.financial_year_id" class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" :disabled="saving">
                                <option :value="null">Select Financial Year</option>
                                <option v-for="s in academicSessions" :key="'s-' + s.id" :value="Number(s.id)">{{ s.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-slate-900">Fee Schedule</div>
                        <div class="mt-1 text-xs text-slate-500">Configure monthly items before generating invoices</div>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <div class="min-w-[1050px]">
                            <div class="grid grid-cols-12 gap-2 border-b border-slate-200 pb-2 text-xs font-semibold text-slate-600">
                                <div class="col-span-1 text-center">Active</div>
                                <div class="col-span-1 text-center">Sorting</div>
                                <div class="col-span-1 text-center">Month</div>
                                <div class="col-span-1 text-center">Year</div>
                                <div class="col-span-2">Date</div>
                                <div class="col-span-3">Purpose</div>
                                <div class="col-span-2">Establishment</div>
                                <div class="col-span-1 text-center">Amount</div>
                                <div class="col-span-1 text-center">Action</div>
                            </div>

                            <div v-for="(item, idx) in form.details" :key="'d-' + idx" class="grid grid-cols-12 items-center gap-2 py-2">
                                <div class="col-span-1 text-center">
                                    <input type="checkbox" class="rounded border-slate-300" v-model="item.status" :true-value="1" :false-value="0" />
                                </div>

                                <div class="col-span-1">
                                    <input type="number" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2 text-center text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" v-model.number="item.sorting" />
                                </div>

                                <div class="col-span-1">
                                    <input type="text" class="h-9 w-full rounded-lg border border-slate-300 bg-slate-50 px-2 text-center text-sm shadow-sm outline-none" :value="monthNameFromDate(item.date)" readonly />
                                </div>

                                <div class="col-span-1">
                                    <input type="text" class="h-9 w-full rounded-lg border border-slate-300 bg-slate-50 px-2 text-center text-sm shadow-sm outline-none" :value="yearFromDate(item.date)" readonly />
                                </div>

                                <div class="col-span-2">
                                    <input type="date" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" v-model="item.date" />
                                </div>

                                <div class="col-span-3">
                                    <select v-model="item.account_head_id" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                        <option :value="null">--Select Purpose--</option>
                                        <option v-for="(name, id) in accountHeads" :key="'ah-' + id" :value="Number(id)">{{ name }}</option>
                                    </select>
                                </div>

                                <div class="col-span-2 text-sm">
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio" :name="'est-' + idx" class="rounded border-slate-300" v-model="item.is_establishment" :value="1" />
                                        <span>Yes</span>
                                    </label>
                                    <label class="ml-3 inline-flex items-center gap-1">
                                        <input type="radio" :name="'est-' + idx" class="rounded border-slate-300" v-model="item.is_establishment" :value="0" />
                                        <span>No</span>
                                    </label>
                                </div>

                                <div class="col-span-1">
                                    <input type="number" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2 text-center text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" v-model.number="item.amount" placeholder="100" />
                                </div>

                                <div class="col-span-1 text-center">
                                    <button
                                        v-if="form.details.length > 1"
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-red-200 bg-white text-red-700 hover:bg-red-50"
                                        @click="removeRow(idx)"
                                        title="Remove"
                                    >
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <button
                                        v-if="form.details.length === idx + 1"
                                        type="button"
                                        class="ml-2 inline-flex h-8 w-8 items-center justify-center rounded-sm border border-slate-300 bg-white text-slate-700 hover:bg-slate-50"
                                        @click="addRow(0)"
                                        title="Add"
                                    >
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4 lg:col-span-4 xl:col-span-3">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-900">Actions</div>
                    <div class="mt-4">
                        <button type="button" class="h-9 w-full rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="saving" @click="submit">
                            {{ saving ? 'processing...' : isEdit ? 'Update' : 'Generate Fees' }}
                        </button>
                        <button type="button" class="mt-2 h-9 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'HostelFeeGenerateManage',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        generateId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            saving: false,
            loading: false,
            error: '',
            success: '',
            form: {
                hostel_id: null,
                financial_year_id: null,
                details: [],
            },
        }
    },
    computed: {
        isEdit() {
            return !!this.generateId
        },
        hostels() {
            return this.systems?.global?.hostels || []
        },
        academicSessions() {
            return this.systems?.global?.academic_sessions || []
        },
        accountHeads() {
            return this.systems?.global?.account_heads || {}
        },
        months() {
            return this.systems?.global?.months || []
        },
        currentFinancialYearId() {
            const list = this.academicSessions || []
            const cur = list.find((x) => Number(x?.current || 0) === 1)
            return cur?.id ? Number(cur.id) : null
        },
    },
    async created() {
        if (this.isEdit) {
            await this.load()
        } else {
            this.seedRows()
            if (this.currentFinancialYearId) {
                this.form.financial_year_id = this.currentFinancialYearId
            }
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
            this.go('/admin/hostelFeeGenerate')
        },
        monthNameFromDate(d) {
            if (!d) return ''
            const parts = String(d).split('-')
            if (parts.length < 2) return ''
            const m = Number(parts[1])
            const found = (this.months || []).find((x) => Number(x?.key) === m)
            return found?.name || ''
        },
        yearFromDate(d) {
            if (!d) return ''
            const parts = String(d).split('-')
            return parts[0] || ''
        },
        seedRows() {
            this.form.details = []
            for (let i = 0; i < 12; i++) {
                this.addRow(i)
            }
        },
        addRow(i = 0) {
            const year = new Date().getFullYear()
            const month = Number(i) + 1
            const mm = String(month).padStart(2, '0')
            this.form.details.push({
                date: `${year}-${mm}-01`,
                account_head_id: null,
                amount: null,
                status: 1,
                is_establishment: 0,
                sorting: month,
            })
        },
        removeRow(idx) {
            this.form.details.splice(idx, 1)
        },
        async load() {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/hostelFeeGenerate/${this.generateId}`)
                const base = res?.data?.data || {}
                const details = Array.isArray(res?.data?.details) ? res.data.details : []

                this.form.hostel_id = base?.hostel_id ? Number(base.hostel_id) : null
                this.form.financial_year_id = base?.financial_year_id ? Number(base.financial_year_id) : null
                this.form.details = details.map((d) => ({
                    date: d?.date || null,
                    account_head_id: d?.account_head_id ? Number(d.account_head_id) : null,
                    amount: d?.amount !== null && d?.amount !== undefined ? Number(d.amount) : null,
                    status: Number(d?.status ?? 1),
                    is_establishment: Number(d?.is_establishment ?? 0),
                    sorting: Number(d?.sorting ?? 0),
                }))

                if (!this.form.details.length) {
                    this.seedRows()
                }
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load.'
            } finally {
                this.loading = false
            }
        },
        async submit() {
            this.saving = true
            this.error = ''
            this.success = ''

            try {
                if (!this.form.hostel_id) {
                    this.error = 'Hostel is required'
                    this.toast(this.error, 'error')
                    return
                }
                if (!this.form.financial_year_id) {
                    this.error = 'Financial Year is required'
                    this.toast(this.error, 'error')
                    return
                }

                for (let i = 0; i < (this.form.details || []).length; i++) {
                    const d = this.form.details[i]
                    const active = String(d?.status) === '1'
                    if (active) {
                        if (!d?.account_head_id) {
                            this.error = `Purpose is required (row ${i + 1})`
                            this.toast(this.error, 'error')
                            return
                        }
                        if (!d?.amount || Number(d.amount) <= 0) {
                            this.error = `Amount is required (row ${i + 1})`
                            this.toast(this.error, 'error')
                            return
                        }
                    }
                }

                if (!confirm('Are you sure want to generate invoice?')) return

                const payload = {
                    hostel_id: this.form.hostel_id,
                    financial_year_id: this.form.financial_year_id,
                    details: (this.form.details || []).map((d) => ({
                        date: d?.date || null,
                        account_head_id: d?.account_head_id || null,
                        amount: d?.amount !== null && d?.amount !== undefined ? Number(d.amount) : null,
                        status: Number(d?.status ?? 1),
                        is_establishment: Number(d?.is_establishment ?? 0),
                        sorting: Number(d?.sorting ?? 0),
                    })),
                }

                let res
                if (this.isEdit) {
                    res = await window.axios.put(`/admin/hostelFeeGenerate/${this.generateId}`, payload)
                } else {
                    res = await window.axios.post('/admin/hostelFeeGenerate', payload)
                }

                const msg = res?.data?.message || (this.isEdit ? 'Update Successfully!' : 'Create Successfully!')
                this.success = msg
                this.toast(msg, 'success')

                if (!this.isEdit) {
                    this.goIndex()
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
