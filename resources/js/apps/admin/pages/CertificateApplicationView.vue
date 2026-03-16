<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Certificate Application</div>
                    <div class="mt-1 text-sm text-slate-600">Preview and print certificate</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goBack">Back</button>
                    <button
                        v-if="String(data?.application_status) === 'approved'"
                        type="button"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                        :disabled="printing"
                        @click="printCertificate"
                    >
                        {{ printing ? 'Processing...' : 'Print' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-200">
                        <tr>
                            <th class="py-2 pr-3 text-left text-slate-600">Academic Session</th>
                            <td class="py-2">{{ data?.academic_session?.name || '' }}</td>
                            <th class="py-2 pr-3 text-left text-slate-600">Academic Level</th>
                            <td class="py-2">{{ data?.qualification?.name || '' }}</td>
                            <th class="py-2 pr-3 text-left text-slate-600">Department</th>
                            <td class="py-2">{{ data?.department?.name || '' }}</td>
                            <th class="py-2 pr-3 text-left text-slate-600">Account Head</th>
                            <td class="py-2">{{ data?.head?.name || '' }}</td>
                            <th class="py-2 pr-3 text-left text-slate-600">Title</th>
                            <td class="py-2">{{ data?.template?.title || '' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="w-full sm:w-56">
                    <select v-model="selected" class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm" @change="selectCertificate">
                        <option value="en">EN Certificate</option>
                        <option value="bn">BN Certificate</option>
                    </select>
                </div>
            </div>

            <div class="mt-4 flex justify-center overflow-x-auto">
                <div id="print-certificate" class="py-3">
                    <div class="certificate-template-main" :class="data?.template?.print_layout" :style="canvasStyle">
                        <div v-for="(item, index) in templateJson" :key="'p-' + index" v-show="item.value" :style="sectionStyle(item)">
                            {{ item.value }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'CertificateApplicationView',
    props: {
        applicationId: {
            type: [String, Number],
            required: true,
        },
    },
    data() {
        return {
            error: '',
            printing: false,
            selected: 'en',
            activeImage: 'bg_en_image',
            sectionField: 'en_template_json',
            data: { template: { en_template_json: [], bn_template_json: [] } },
        }
    },
    computed: {
        templateJson() {
            let jsonData = this.data?.template?.[this.sectionField]
            jsonData = Array.isArray(jsonData) ? jsonData : []

            if (jsonData.length) {
                const map = this.getLabelMapping()
                jsonData = jsonData.map((el) => ({ ...el, value: map[el.label] ? map[el.label] : '-' }))
            }

            return jsonData
        },
        canvasStyle() {
            const url = this.data?.template?.[this.activeImage] || ''
            return {
                position: 'relative',
                backgroundImage: url ? `url('${url}')` : 'none',
                backgroundSize: 'cover',
                backgroundRepeat: 'no-repeat',
            }
        },
    },
    mounted() {
        this.load()
    },
    methods: {
        goBack() {
            window.location.href = '/admin/certificateApplication'
        },
        selectCertificate() {
            this.activeImage = this.selected === 'en' ? 'bg_en_image' : 'bg_bn_image'
            this.sectionField = this.selected === 'en' ? 'en_template_json' : 'bn_template_json'
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
            const { left, top } = this.parsePosition(item)
            return {
                position: 'absolute',
                left: `${left}px`,
                top: `${top}px`,
                width: item.section_width || '250px',
                fontSize: `${Number(item.fornt_size || 16)}px`,
                fontWeight: item.font_weight || 'normal',
                color: item.color || '#111827',
                textAlign: item.text_align || 'left',
            }
        },
        getLabelMapping() {
            const lang = this.selected
            const now = new Date()
            const dateStr = now.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })

            return {
                '[__SL__]': this.data.serial_number,
                '[__Date__]': dateStr,
                '[__Student_Name__]': this.data[`student_name_${lang}`],
                '[__Fathers_Name__]': this.data[`fathers_name_${lang}`],
                '[__Mothers_Name__]': this.data[`mothers_name_${lang}`],
                '[__Academic_Year__]': this.data[`academic_year_${lang}`],
                '[__Reg_No__]': this.data[`registration_no_${lang}`],
                '[__Exam_Roll__]': this.data[`exam_roll_${lang}`],
                '[__Exam_Year__]': this.data[`exam_year_${lang}`],
                '[__GPA__]': this.data[`gpa_${lang}`],
                '[__College_Roll__]': this.data[`college_roll_${lang}`],
                '[__Address__]': this.data[`address_${lang}`],
                '[__Division__]': this.data[`division_${lang}`],
                '[__Academic_Session__]': this.data?.academic_session?.name,
                '[__Academic_Level__]': this.data?.qualification?.name,
                '[__Department__]': this.data?.department?.name,
                '[__Verified_By__]': this.data.approved_by_name,
                '[__Principal_Name__]': this.data.principal_name,
                '[__Principal_Index__]': this.data.principal_index,
            }
        },
        async load() {
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/certificateApplication/${this.applicationId}/details`)
                this.data = res?.data || this.data
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load.'
            }
        },
        async printCertificate() {
            const ok = window.confirm('Are you sure want to print?')
            if (!ok) return

            this.printing = true
            try {
                await window.axios.post('/admin/certificateApplication-print', { id: this.data.id })
                this.printNow()
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to print.'
            } finally {
                this.printing = false
            }
        },
        printNow() {
            const el = document.getElementById('print-certificate')
            if (!el) return

            const html = `<!doctype html><html><head><meta charset="utf-8" />
                <title>Certificate</title>
                <style>
                    @page { margin: 0; size: auto; }
                    body { margin: 0; }
                    .certificate-template-main { border: 1px dashed transparent !important; }
                    .certificate-template-main div { border: 1px dotted transparent !important; }
                    .portrait { width: 795px; height: 1120px; }
                    .landscape { width: 1300px; height: 915px; }
                    @media print {
                        .portrait { zoom: 98%; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                        .landscape { zoom: 85%; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    }
                </style>
            </head><body>${el.innerHTML}</body></html>`

            const w = window.open('', '_blank', 'width=1200,height=900')
            if (!w) return
            w.document.open()
            w.document.write(html)
            w.document.close()
            setTimeout(() => {
                w.focus()
                w.print()
                w.close()
            }, 250)
        },
    },
}
</script>

<style scoped>
.certificate-template-main {
    border: 1px dashed transparent !important;
}
.certificate-template-main div {
    border: 1px dotted transparent !important;
}
.portrait {
    width: 795px;
    height: 1120px;
}
.landscape {
    width: 1300px;
    height: 915px;
}
</style>
