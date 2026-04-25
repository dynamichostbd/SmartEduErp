<template>
    <div class="rounded-sm border border-slate-300 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50/50 p-4 flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-800">View Invoice</h2>
            <div class="flex gap-2">
                <button 
                    class="rounded-sm border border-slate-300 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 flex items-center gap-1.5"
                    @click="download"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Download
                </button>
                <button 
                    class="rounded-sm bg-slate-900 border border-slate-900 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-600 hover:border-emerald-600 transition-colors flex items-center gap-1.5"
                    @click="print"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                    Print
                </button>
            </div>
        </div>
        <div class="p-0">
            <div class="relative w-full overflow-hidden aspect-[1/1.414]">
                <iframe
                    ref="iframe"
                    :src="invoiceUrl"
                    class="absolute inset-0 h-full w-full border-none"
                    title="Invoice Preview"
                ></iframe>
            </div>
        </div>
    </div>
</template>

<script setup>
import { inject, ref, computed } from 'vue'

const invoiceId = inject('studentInvoiceId')

const iframe = ref(null)

const invoiceUrl = computed(() => {
    return `/api/public/student/invoices/${encodeURIComponent(String(invoiceId.value))}`
})

const download = () => {
    const url = `/api/public/student/invoices/${encodeURIComponent(String(invoiceId.value))}/download`
    window.open(url, '_blank')
}

const print = () => {
    const win = iframe.value?.contentWindow
    if (!win) return
    try {
        win.focus()
        win.print()
    } catch {
        // Fallback or error handling
    }
}
</script>
