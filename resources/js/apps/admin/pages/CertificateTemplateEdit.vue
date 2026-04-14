<template>
    <form class="flex flex-col gap-4" @submit.prevent="submit">
        <button type="button" class="fixed left-2 top-1/2 z-[90] -translate-y-1/2 rounded-full bg-sky-600 p-3 text-white shadow" @click="toggleSidebar">
            <span class="text-xl">⚙</span>
        </button>

        <div :class="showSidebar ? 'left-0' : '-left-full'" class="fixed top-0 z-[100] h-full w-[90%] max-w-4xl overflow-y-auto border-r border-slate-300 bg-white p-5 shadow-xl transition-all">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold uppercase text-slate-800">Certificate Information</div>
                <button type="button" class="rounded-sm border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="toggleSidebar">Close</button>
            </div>

            <div class="mt-4 rounded-xl border border-slate-300 p-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
                    <div class="md:col-span-3">
                        <div class="text-xs font-semibold text-slate-600">Academic Level</div>
                        <select v-model="data.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required>
                            <option value="">Select</option>
                            <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                        </select>
                    </div>

                    <div class="md:col-span-3">
                        <div class="text-xs font-semibold text-slate-600">Account Head</div>
                        <select v-model="data.account_head_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required>
                            <option value="">Select</option>
                            <option v-for="h in accountHeads" :key="'h-' + h.id" :value="String(h.id)">{{ h.name }}</option>
                        </select>
                    </div>

                    <div class="md:col-span-6">
                        <div class="text-xs font-semibold text-slate-600">Title</div>
                        <input v-model="data.title" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required />
                    </div>

                    <div class="md:col-span-6">
                        <div class="text-xs font-semibold text-slate-600">Background Image (EN)</div>
                        <input type="file" accept="image/*" class="mt-1 block h-9 w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm" @change="onPickFile($event, 'bg_en_image')" />
                    </div>

                    <div class="md:col-span-6">
                        <div class="text-xs font-semibold text-slate-600">Background Image (BN)</div>
                        <input type="file" accept="image/*" class="mt-1 block h-9 w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm" @change="onPickFile($event, 'bg_bn_image')" />
                    </div>

                    <div class="md:col-span-3">
                        <div class="text-xs font-semibold text-slate-600">Certificate Fees</div>
                        <div class="mt-2 flex items-center gap-4 text-sm">
                            <label class="inline-flex items-center gap-2"><input v-model="data.certificate_fees" type="radio" value="single" /> Single</label>
                            <label class="inline-flex items-center gap-2"><input v-model="data.certificate_fees" type="radio" value="double" /> Double</label>
                            <label class="inline-flex items-center gap-2"><input v-model="data.certificate_fees" type="radio" value="no" /> N/A</label>
                        </div>
                    </div>

                    <div class="md:col-span-3">
                        <div class="text-xs font-semibold text-slate-600">Print Layout</div>
                        <div class="mt-2 flex items-center gap-4 text-sm">
                            <label class="inline-flex items-center gap-2"><input v-model="data.print_layout" type="radio" value="portrait" /> Portrait</label>
                            <label class="inline-flex items-center gap-2"><input v-model="data.print_layout" type="radio" value="landscape" /> Landscape</label>
                        </div>
                    </div>

                    <div class="md:col-span-6">
                        <div class="text-xs font-semibold text-slate-600">Payment Gateway</div>
                        <select v-model="data.payment_gateway_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required>
                            <option value="">Select</option>
                            <option v-for="g in gateways" :key="'g-' + g.id" :value="String(g.id)">{{ g.account_no }}</option>
                        </select>
                    </div>

                    <div class="md:col-span-3">
                        <div class="text-xs font-semibold text-slate-600">Amount</div>
                        <input v-model="data.amount" type="number" step="0.01" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required />
                    </div>
                </div>
            </div>

            <div class="mt-4 rounded-xl border border-slate-300 p-4">
                <div class="flex items-center gap-2">
                    <button type="button" class="rounded-sm px-3 py-2 text-sm font-semibold" :class="activeTab === 'en' ? 'bg-slate-900 text-white' : 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50'" @click="setTab('en')">TEMPLATE SECTIONS - EN</button>
                    <button type="button" class="rounded-sm px-3 py-2 text-sm font-semibold" :class="activeTab === 'bn' ? 'bg-slate-900 text-white' : 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50'" @click="setTab('bn')">TEMPLATE SECTIONS - BN</button>
                </div>

                <div class="mt-4">
                    <div class="grid grid-cols-7 gap-2 text-xs font-semibold text-slate-600">
                        <div>Label</div>
                        <div>Font Size</div>
                        <div>Width</div>
                        <div>Height</div>
                        <div>Weight</div>
                        <div>Align</div>
                        <div>Color</div>
                    </div>

                    <div v-for="(item, index) in activeSections" :key="'sec-' + index" class="mt-2 border-b border-emerald-200 pb-3">
                        <div class="grid grid-cols-8 gap-2">
                            <div class="col-span-1">
                                <select v-model="item.label" class="h-9 w-full rounded-sm border border-slate-300 bg-white px-2 text-xs">
                                    <option value="">--Select--</option>
                                    <option v-for="o in labelOptions" :key="o.val" :value="o.val">{{ o.text }}</option>
                                </select>
                            </div>
                            <div class="col-span-1">
                                <select v-model.number="item.fornt_size" class="h-9 w-full rounded-sm border border-slate-300 bg-white px-2 text-xs">
                                    <option v-for="i in fontSizeRange" :key="'fs-' + i" :value="i">{{ i }}</option>
                                </select>
                            </div>
                            <div class="col-span-1"><input v-model="item.section_width" type="text" class="h-9 w-full rounded-sm border border-slate-300 bg-white px-2 text-xs" /></div>
                            <div class="col-span-1"><input v-model="item.section_height" type="text" class="h-9 w-full rounded-sm border border-slate-300 bg-white px-2 text-xs" /></div>
                            <div class="col-span-1">
                                <select v-model="item.font_weight" class="h-9 w-full rounded-sm border border-slate-300 bg-white px-2 text-xs">
                                    <option value="">--Select--</option>
                                    <option value="bold">Bold</option>
                                    <option value="normal">Normal</option>
                                </select>
                            </div>
                            <div class="col-span-1">
                                <select v-model="item.text_align" class="h-9 w-full rounded-sm border border-slate-300 bg-white px-2 text-xs">
                                    <option value="">--Select--</option>
                                    <option value="left">Left</option>
                                    <option value="center">Center</option>
                                    <option value="right">Right</option>
                                </select>
                            </div>
                            <div class="col-span-1"><input v-model="item.color" type="color" class="h-9 w-full rounded-sm border border-slate-300 bg-white px-2" /></div>
                            <div class="col-span-1 flex items-center justify-end">
                                <button type="button" class="rounded-sm border border-red-200 bg-red-50 px-2 py-1 text-xs font-semibold text-red-700 hover:bg-red-100" v-if="activeSections.length > 1" @click="removeSection(index)">X</button>
                            </div>
                        </div>

                        <div v-if="index + 1 === activeSections.length" class="mt-3 flex items-center justify-end gap-2">
                            <button type="button" class="rounded-sm bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700" @click="addSection">Add Section</button>
                            <button type="button" class="rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="toggleSidebar">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class=" border border-slate-300 bg-white">
            <div class="flex items-center justify-end border-b border-slate-300 p-4">
                <button type="button" class="rounded-sm bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" :disabled="submitting" @click="submit">
                    {{ submitting ? 'processing...' : 'Update' }}
                </button>
            </div>
            <div class="p-5">
                <div class="flex justify-center">
                    <div
                        ref="canvas"
                        class="certificate-template-main"
                        :class="data.print_layout"
                        :style="canvasStyle"
                    >
                        <div
                            v-for="(item, index) in activeSections"
                            :key="'drag-' + index"
                            class="select-none"
                            :style="sectionStyle(item)"
                            @pointerdown="startDrag($event, index)"
                        >
                            <span v-if="item.label" class="cursor-move">{{ item.label }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="message" class=" border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">{{ message }}</div>
        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>
    </form>
</template>

<script>
export default {
    name: 'CertificateTemplateEdit',
    props: {
        templateId: {
            type: [String, Number],
            required: true,
        },
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            showSidebar: false,
            activeTab: 'en',
            submitting: false,
            error: '',
            message: '',
            accountHeads: [],
            gateways: [],
            files: {
                bg_en_image: null,
                bg_bn_image: null,
            },
            data: {
                id: null,
                bg_bn_image: '',
                bg_en_image: '',
                en_template_json: [],
                bn_template_json: [],
                status: 'active',
                certificate_fees: 'single',
                print_layout: 'landscape',
                title: '',
                academic_qualification_id: '',
                account_head_id: '',
                payment_gateway_id: '',
                amount: '',
            },
            dragging: {
                active: false,
                index: null,
                startX: 0,
                startY: 0,
                baseLeft: 0,
                baseTop: 0,
                pointerId: null,
            },
        }
    },
    computed: {
        qualifications() {
            const list = this.systems?.global?.academic_qualifications
            return Array.isArray(list) ? list : []
        },
        labelOptions() {
            return [
                { val: '[__SL__]', text: 'SL' },
                { val: '[__Date__]', text: 'Date' },
                { val: '[__Academic_Session__]', text: 'Academic Session' },
                { val: '[__Academic_Level__]', text: 'Academic Level' },
                { val: '[__Department__]', text: 'Department' },
                { val: '[__Student_Name__]', text: 'Student Name' },
                { val: '[__Fathers_Name__]', text: 'Fathers Name' },
                { val: '[__Mothers_Name__]', text: 'Mothers Name' },
                { val: '[__Academic_Year__]', text: 'Academic Year' },
                { val: '[__Reg_No__]', text: 'Reg No' },
                { val: '[__College_Roll__]', text: 'College Roll' },
                { val: '[__Exam_Roll__]', text: 'Exam Roll' },
                { val: '[__Exam_Year__]', text: 'Exam Year' },
                { val: '[__GPA__]', text: 'GPA' },
                { val: '[__Division__]', text: 'Division' },
                { val: '[__Address__]', text: 'Address' },
                { val: '[__Verified_By__]', text: 'Verified By' },
                { val: '[__Principal_Name__]', text: 'Principal Name' },
                { val: '[__Principal_Index__]', text: 'Principal Index' },
            ]
        },
        fontSizeRange() {
            const out = []
            for (let i = 12; i <= 30; i++) out.push(i)
            return out
        },
        sectionField() {
            return this.activeTab === 'en' ? 'en_template_json' : 'bn_template_json'
        },
        activeImageField() {
            return this.activeTab === 'en' ? 'bg_en_image' : 'bg_bn_image'
        },
        activeSections() {
            const list = this.data?.[this.sectionField]
            return Array.isArray(list) && list.length ? list : []
        },
        canvasStyle() {
            const url = this.data?.[this.activeImageField] || ''
            return {
                position: 'relative',
                backgroundImage: url ? `url('${url}')` : 'none',
                backgroundSize: 'cover',
                backgroundRepeat: 'no-repeat',
            }
        },
    },
    mounted() {
        this.loadMeta()
        this.load()
        window.addEventListener('pointermove', this.onPointerMove)
        window.addEventListener('pointerup', this.onPointerUp)
        window.addEventListener('pointercancel', this.onPointerUp)
    },
    beforeUnmount() {
        window.removeEventListener('pointermove', this.onPointerMove)
        window.removeEventListener('pointerup', this.onPointerUp)
        window.removeEventListener('pointercancel', this.onPointerUp)
    },
    methods: {
        toggleSidebar() {
            this.showSidebar = !this.showSidebar
        },
        setTab(tab) {
            this.activeTab = tab
        },
        onPickFile(e, key) {
            const f = e?.target?.files?.[0] || null
            this.files[key] = f
            if (f) {
                this.data[key] = URL.createObjectURL(f)
            }
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
        async load() {
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/certificateTemplate/${this.templateId}/details`)
                const d = res?.data || {}
                this.data = {
                    ...this.data,
                    ...d,
                    en_template_json: Array.isArray(d.en_template_json) && d.en_template_json.length ? d.en_template_json : this.data.en_template_json,
                    bn_template_json: Array.isArray(d.bn_template_json) && d.bn_template_json.length ? d.bn_template_json : this.data.bn_template_json,
                }

                if (!Array.isArray(this.data.en_template_json) || !this.data.en_template_json.length) {
                    this.data.en_template_json = [this.emptySection()]
                }
                if (!Array.isArray(this.data.bn_template_json) || !this.data.bn_template_json.length) {
                    this.data.bn_template_json = [this.emptySection()]
                }
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load.'
            }
        },
        emptySection() {
            return {
                section_width: '200px',
                section_height: '25px',
                label: '',
                color: '',
                text_align: '',
                font_weight: '',
                fornt_size: 16,
                section_position: '',
            }
        },
        addSection() {
            this.data[this.sectionField].push(this.emptySection())
        },
        removeSection(index) {
            const ok = window.confirm('Are you sure remove this section?')
            if (!ok) return
            this.data[this.sectionField].splice(index, 1)
            if (this.data[this.sectionField].length === 0) {
                this.data[this.sectionField].push(this.emptySection())
            }
        },
        parsePosition(section) {
            const pos = String(section?.section_position || '')
            let left = 0
            let top = 0
            const lm = pos.match(/left\s*:\s*([0-9.]+)px/i)
            const tm = pos.match(/top\s*:\s*([0-9.]+)px/i)
            if (lm) left = Number(lm[1]) || 0
            if (tm) top = Number(tm[1]) || 0
            return { left, top }
        },
        sectionStyle(item) {
            if (!item?.label) {
                return { display: 'none' }
            }
            const { left, top } = this.parsePosition(item)
            return {
                position: 'absolute',
                left: `${left}px`,
                top: `${top}px`,
                width: item.section_width || '250px',
                height: item.section_height || '25px',
                fontSize: `${Number(item.fornt_size || 16)}px`,
                fontWeight: item.font_weight || 'normal',
                color: item.color || '#111827',
                textAlign: item.text_align || 'left',
                cursor: 'move',
                whiteSpace: 'nowrap',
                userSelect: 'none',
            }
        },
        startDrag(e, index) {
            const item = this.activeSections[index]
            if (!item?.label) return

            const { left, top } = this.parsePosition(item)
            this.dragging.active = true
            this.dragging.index = index
            this.dragging.startX = e.clientX
            this.dragging.startY = e.clientY
            this.dragging.baseLeft = left
            this.dragging.baseTop = top
            this.dragging.pointerId = e.pointerId

            try {
                e.target.setPointerCapture(e.pointerId)
            } catch (err) {
            }
        },
        onPointerMove(e) {
            if (!this.dragging.active) return
            if (this.dragging.pointerId !== null && e.pointerId !== this.dragging.pointerId) return

            const dx = e.clientX - this.dragging.startX
            const dy = e.clientY - this.dragging.startY

            const nextLeft = Math.max(0, this.dragging.baseLeft + dx)
            const nextTop = Math.max(0, this.dragging.baseTop + dy)

            const section = this.data[this.sectionField][this.dragging.index]
            if (!section) return
            section.section_position = `left:${Math.round(nextLeft)}px;top:${Math.round(nextTop)}px;`
        },
        onPointerUp(e) {
            if (!this.dragging.active) return
            if (this.dragging.pointerId !== null && e.pointerId !== this.dragging.pointerId) return

            this.dragging.active = false
            this.dragging.index = null
            this.dragging.pointerId = null
        },
        buildFormData() {
            const fd = new FormData()
            fd.append('data', JSON.stringify(this.data))
            if (this.files.bg_en_image) fd.append('bg_en_image', this.files.bg_en_image)
            if (this.files.bg_bn_image) fd.append('bg_bn_image', this.files.bg_bn_image)
            return fd
        },
        async submit() {
            if (!this.data?.id) return
            this.submitting = true
            this.error = ''
            this.message = ''
            try {
                const res = await window.axios.post(`/admin/certificateTemplate/${this.data.id}`, this.buildFormData(), {
                    headers: { 'Content-Type': 'multipart/form-data' },
                })
                this.message = res?.data?.message || 'Update Successfully!'
                setTimeout(() => {
                    this.message = ''
                }, 1200)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to update.'
            } finally {
                this.submitting = false
            }
        },
    },
}
</script>

<style scoped>
.certificate-template-main {
    border: 1px dashed #e2e8f0;
}
.portrait {
    width: 800px;
    min-height: 1300px;
}
.landscape {
    width: 1300px;
    min-height: 915px;
}
</style>
