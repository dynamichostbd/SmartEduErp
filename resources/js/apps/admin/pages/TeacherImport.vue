<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Teacher Import</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="submitting" @click="goIndex">Back</button>
                    <button type="button" class="rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="submitting || !file" @click="submit">
                        {{ submitting ? '...' : 'Upload' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>
        <div v-if="success" class=" border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ success }}</div>

        <div class=" border border-slate-300 bg-white p-5">
            <div class="text-xs font-semibold text-slate-600">Excel File</div>
            <input type="file" class="mt-2 block w-full text-sm" accept=".xlsx,.xls,.csv" @change="onFile" />
        </div>
    </div>
</template>

<script>
export default {
    name: 'TeacherImport',
    data() {
        return {
            submitting: false,
            error: '',
            success: '',
            file: null,
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
            this.go('/admin/teacher')
        },
        onFile(e) {
            this.file = e?.target?.files?.[0] || null
        },
        async submit() {
            if (!this.file) return
            this.submitting = true
            this.error = ''
            this.success = ''
            try {
                const fd = new FormData()
                fd.append('excel_file', this.file)
                const res = await window.axios.post('/admin/teacher-import', fd, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                })
                const m = res?.data?.message || 'Import Successfully!'
                this.success = m
                this.toast(m, 'success')
            } catch (e) {
                this.error = e?.response?.data?.message || e?.response?.data?.exception || 'Import failed.'
                this.toast(this.error, 'error')
            } finally {
                this.submitting = false
            }
        },
    },
}
</script>
