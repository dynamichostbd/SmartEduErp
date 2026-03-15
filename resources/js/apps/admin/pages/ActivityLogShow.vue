<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Activity Log View</div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goIndex">Back</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div v-if="row" class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="lg:col-span-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">Before Process Data</div>
                    <div class="mt-3 overflow-hidden rounded-xl border border-slate-200">
                        <table class="min-w-full border-collapse">
                            <tbody>
                                <tr v-for="(v, k) in beforeData" :key="'b-' + k" class="text-sm">
                                    <td class="border border-slate-200 px-3 py-2 font-semibold text-slate-700">{{ prettyKey(k) }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-slate-700">{{ formatVal(v) }}</td>
                                </tr>
                                <tr v-if="Object.keys(beforeData).length === 0" class="text-sm text-slate-600">
                                    <td class="border border-slate-200 px-3 py-2" colspan="2">No data</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">After Process Data</div>
                    <div class="mt-3 overflow-hidden rounded-xl border border-slate-200">
                        <table class="min-w-full border-collapse">
                            <tbody>
                                <tr v-for="(v, k) in afterData" :key="'a-' + k" class="text-sm">
                                    <td class="border border-slate-200 px-3 py-2 font-semibold text-slate-700">{{ prettyKey(k) }}</td>
                                    <td class="border border-slate-200 px-3 py-2 text-slate-700">{{ formatVal(v) }}</td>
                                </tr>
                                <tr v-if="Object.keys(afterData).length === 0" class="text-sm text-slate-600">
                                    <td class="border border-slate-200 px-3 py-2" colspan="2">No data</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ActivityLogShow',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        logId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            error: '',
            row: null,
        }
    },
    computed: {
        propsObj() {
            const p = this.row?.properties
            if (!p) return {}
            if (typeof p === 'string') {
                try {
                    return JSON.parse(p)
                } catch {
                    return {}
                }
            }
            return p
        },
        beforeData() {
            const o = this.propsObj?.old
            return o && typeof o === 'object' ? o : {}
        },
        afterData() {
            const o = this.propsObj?.attributes
            return o && typeof o === 'object' ? o : {}
        },
    },
    created() {
        this.load()
    },
    methods: {
        go(path) {
            window.history.pushState({}, '', path)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        goIndex() {
            this.go('/admin/activityLog')
        },
        prettyKey(k) {
            return String(k || '').replace(/_/g, ' ')
        },
        formatVal(v) {
            if (v === null || v === undefined) return ''
            if (typeof v === 'object') return JSON.stringify(v)
            return String(v)
        },
        async load() {
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/activityLog/${this.logId}`)
                this.row = res?.data || null
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load activity log.'
            }
        },
    },
}
</script>
