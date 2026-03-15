<template>
    <form class="flex flex-col gap-4" @submit.prevent="submit">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Certificate Template</div>
                    <div class="mt-1 text-sm text-slate-600">Create new certificate template</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="submitting" @click="goBack">Back</button>
                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="submitting">
                        {{ submitting ? 'Processing...' : 'Submit' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="message" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">{{ message }}</div>
        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-12">
                <div class="xl:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Academic Level <span class="text-red-600">*</span></div>
                    <select v-model="form.academic_qualification_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required>
                        <option value="">Select</option>
                        <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                    </select>
                </div>

                <div class="xl:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Account Head <span class="text-red-600">*</span></div>
                    <select v-model="form.account_head_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required>
                        <option value="">Select</option>
                        <option v-for="h in accountHeads" :key="'h-' + h.id" :value="String(h.id)">{{ h.name }}</option>
                    </select>
                </div>

                <div class="xl:col-span-6">
                    <div class="text-xs font-semibold text-slate-600">Title <span class="text-red-600">*</span></div>
                    <input v-model="form.title" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required />
                </div>

                <div class="xl:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Certificate Fees <span class="text-red-600">*</span></div>
                    <div class="mt-2 flex items-center gap-4 text-sm">
                        <label class="inline-flex items-center gap-2"><input v-model="form.certificate_fees" type="radio" value="single" /> Single</label>
                        <label class="inline-flex items-center gap-2"><input v-model="form.certificate_fees" type="radio" value="double" /> Double</label>
                        <label class="inline-flex items-center gap-2"><input v-model="form.certificate_fees" type="radio" value="no" /> N/A</label>
                    </div>
                </div>

                <div class="xl:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Print Layout <span class="text-red-600">*</span></div>
                    <div class="mt-2 flex items-center gap-4 text-sm">
                        <label class="inline-flex items-center gap-2"><input v-model="form.print_layout" type="radio" value="portrait" /> Portrait</label>
                        <label class="inline-flex items-center gap-2"><input v-model="form.print_layout" type="radio" value="landscape" /> Landscape</label>
                    </div>
                </div>

                <div class="xl:col-span-6">
                    <div class="text-xs font-semibold text-slate-600">Payment Gateway <span class="text-red-600">*</span></div>
                    <select v-model="form.payment_gateway_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required>
                        <option value="">Select</option>
                        <option v-for="g in gateways" :key="'g-' + g.id" :value="String(g.id)">{{ g.account_no }}</option>
                    </select>
                </div>

                <div class="xl:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Amount <span class="text-red-600">*</span></div>
                    <input v-model="form.amount" type="number" step="0.01" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required />
                </div>

                <div class="xl:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Status <span class="text-red-600">*</span></div>
                    <select v-model="form.status" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required>
                        <option value="draft">Draft</option>
                        <option value="active">Active</option>
                        <option value="deactive">Deactive</option>
                    </select>
                </div>

                <div class="xl:col-span-6">
                    <div class="text-xs font-semibold text-slate-600">Background Image (EN) <span class="text-red-600">*</span></div>
                    <input type="file" accept="image/*" class="mt-1 block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" @change="onPickFile($event, 'bg_en_image')" required />
                </div>

                <div class="xl:col-span-6">
                    <div class="text-xs font-semibold text-slate-600">Background Image (BN) <span class="text-red-600">*</span></div>
                    <input type="file" accept="image/*" class="mt-1 block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" @change="onPickFile($event, 'bg_bn_image')" required />
                </div>
            </div>
        </div>
    </form>
</template>

<script>
export default {
    name: 'CertificateTemplateCreate',
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
            accountHeads: [],
            gateways: [],
            files: {
                bg_en_image: null,
                bg_bn_image: null,
            },
            form: {
                academic_qualification_id: '',
                account_head_id: '',
                payment_gateway_id: '',
                title: '',
                certificate_fees: 'single',
                print_layout: 'landscape',
                amount: '',
                status: 'active',
            },
        }
    },
    computed: {
        qualifications() {
            const list = this.systems?.global?.academic_qualifications
            return Array.isArray(list) ? list : []
        },
    },
    mounted() {
        this.loadMeta()
    },
    methods: {
        goBack() {
            window.location.href = '/admin/certificateTemplate'
        },
        onPickFile(e, key) {
            const f = e?.target?.files?.[0] || null
            this.files[key] = f
        },
        async loadMeta() {
            try {
                const [headsRes, gatewaysRes] = await Promise.all([
                    window.axios.get('/admin/accountHead', { params: { allData: true, type: 'certificate' } }),
                    window.axios.get('/admin/get-paymentGateway', { params: { allData: true } }),
                ])
                this.accountHeads = Array.isArray(headsRes?.data) ? headsRes.data : []
                this.gateways = Array.isArray(gatewaysRes?.data) ? gatewaysRes.data : []
            } catch (e) {
                this.error = 'Failed to load dropdown data.'
            }
        },
        buildFormData() {
            const fd = new FormData()
            Object.keys(this.form).forEach((k) => fd.append(k, this.form[k] ?? ''))
            if (this.files.bg_en_image) fd.append('bg_en_image', this.files.bg_en_image)
            if (this.files.bg_bn_image) fd.append('bg_bn_image', this.files.bg_bn_image)
            return fd
        },
        async submit() {
            this.submitting = true
            this.error = ''
            this.message = ''
            try {
                const res = await window.axios.post('/admin/certificateTemplate', this.buildFormData(), {
                    headers: { 'Content-Type': 'multipart/form-data' },
                })
                const id = res?.data?.id
                this.message = res?.data?.message || 'Create Successfully!'
                if (id) {
                    setTimeout(() => {
                        window.location.href = `/admin/certificateTemplate/${id}/edit`
                    }, 300)
                } else {
                    setTimeout(() => {
                        window.location.href = '/admin/certificateTemplate'
                    }, 300)
                }
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to create.'
            } finally {
                this.submitting = false
            }
        },
    },
}
</script>
