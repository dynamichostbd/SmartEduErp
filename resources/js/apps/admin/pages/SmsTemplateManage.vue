<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">{{ isEdit ? 'Edit SMS Template' : 'Create SMS Template' }}</div>
                    <div class="mt-1 text-sm text-slate-600">{{ isEdit ? 'Update SMS template information' : 'Create a new SMS template' }}</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">Back</button>
                    <button type="button" class="rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="saving" @click="submit">{{ saving ? '...' : isEdit ? 'Update' : 'Create' }}</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>
        <div v-if="success" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ success }}</div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="space-y-4 lg:col-span-8 xl:col-span-9">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-slate-900">Template Details</div>
                        <div class="mt-1 text-xs text-slate-500">Type, status and body</div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Template Name</div>
                            <input v-model="form.name" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">SMS Type</div>
                            <select v-model="form.sms_type" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">Select</option>
                                <option v-for="t in smsTypesList" :key="'t-' + t.key" :value="t.key">{{ t.label }}</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Common Message</div>
                            <select v-model.number="form.common_message" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="1">YES</option>
                                <option :value="0">NO</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Sending Status</div>
                            <select v-model.number="form.sending_status" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="1">ONLINE</option>
                                <option :value="0">OFFLINE</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2 lg:col-span-3">
                            <div class="flex items-center justify-between">
                                <div class="text-xs font-semibold text-slate-600">SMS Body</div>
                                <div class="text-xs text-slate-500">Characters: {{ (form.sms_body || '').length }}</div>
                            </div>
                            <textarea v-model="form.sms_body" rows="12" class="mt-1 w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4 lg:col-span-4 xl:col-span-3">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-900">Keywords</div>
                    <div class="mt-3 grid grid-cols-1 gap-2">
                        <button v-for="k in smsKeywords" :key="'k-' + k" type="button" class="rounded-sm border border-slate-300 bg-white px-3 py-2 text-left text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="addKeyword(k)">
                            {{ k }}
                        </button>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <button type="button" class="h-9 w-full rounded-sm bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800" :disabled="saving" @click="submit">{{ saving ? '...' : isEdit ? 'Update' : 'Create' }}</button>
                    <button type="button" class="mt-2 h-9 w-full rounded-sm border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'SmsTemplateManage',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        smsTemplateId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            saving: false,
            error: '',
            success: '',
            form: {
                name: '',
                sms_type: '',
                sms_body: '',
                common_message: 0,
                sending_status: 0,
                status: 'active',
            },
        }
    },
    computed: {
        isEdit() {
            return !!this.smsTemplateId
        },
        smsTypes() {
            return this.systems?.global?.sms_types || {}
        },
        smsTypesList() {
            const out = []
            const obj = this.smsTypes || {}
            for (const k of Object.keys(obj)) {
                out.push({ key: k, label: obj[k] })
            }
            return out
        },
        smsKeywords() {
            return Array.isArray(this.systems?.global?.sms_keywords) ? this.systems.global.sms_keywords : []
        },
    },
    async created() {
        if (this.isEdit) {
            await this.load()
        }
    },
    methods: {
        toast(message, type = 'success') {
            window.dispatchEvent(new CustomEvent('app-toast', { detail: { message, type } }))
        },
        addKeyword(k) {
            this.form.sms_body = `${this.form.sms_body || ''}${k}`
        },
        go(path) {
            window.history.pushState({}, '', path)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        goIndex() {
            this.go('/admin/smsTemplate')
        },
        async load() {
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/smsTemplate/${this.smsTemplateId}`)
                const row = res?.data?.sms_template || res?.data || {}
                this.form.name = row?.name || ''
                this.form.sms_type = row?.sms_type || ''
                this.form.sms_body = row?.sms_body || ''
                this.form.common_message = Number(row?.common_message || 0)
                this.form.sending_status = Number(row?.sending_status || 0)
                this.form.status = row?.status || 'active'
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load template.'
            }
        },
        async submit() {
            this.saving = true
            this.error = ''
            this.success = ''
            try {
                const payload = {
                    name: this.form.name,
                    sms_type: this.form.sms_type,
                    sms_body: this.form.sms_body,
                    common_message: this.form.common_message ? 1 : 0,
                    sending_status: this.form.sending_status ? 1 : 0,
                    status: this.form.status,
                }

                let res
                if (this.isEdit) {
                    res = await window.axios.put(`/admin/smsTemplate/${this.smsTemplateId}`, payload)
                } else {
                    res = await window.axios.post('/admin/smsTemplate', payload)
                }

                const msg = res?.data?.message || (this.isEdit ? 'Update Successfully!' : 'Create Successfully!')
                this.success = msg
                this.toast(msg, 'success')

                if (!this.isEdit) {
                    const id = res?.data?.id
                    if (id) {
                        this.go(`/admin/smsTemplate/${id}/edit`)
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
