<template>
    <div class="mt-3 rounded-md border border-slate-200 bg-white p-4 sm:p-6">
        <div class="mx-auto w-full max-w-6xl">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-3">
                    <StudentSidebar
                        :studentMe="app.studentMe"
                        :active="app.studentSidebarActive"
                        :onNavigate="app.studentSidebarGo"
                        :onLogout="app.doStudentLogout"
                        :logoutDisabled="app.studentAuthLoading"
                    />
                </div>

                <div class="lg:col-span-9">
                    <div class="rounded-md border border-slate-200 bg-white">
                        <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 text-sm font-extrabold text-slate-800">INVOICE</div>
                        <div class="p-4">
                            <div class="flex flex-wrap items-center gap-3">
                                <button
                                    type="button"
                                    class="rounded-md bg-[#2b6cb0] px-3 py-1.5 text-xs font-extrabold text-white hover:bg-[#245a94]"
                                    @click="downloadInvoice"
                                >
                                    Download Invoice
                                </button>
                                <button
                                    type="button"
                                    class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-extrabold text-white hover:bg-emerald-700"
                                    @click="printInvoice"
                                >
                                    PRINT
                                </button>
                                <button
                                    type="button"
                                    class="ml-auto rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-extrabold text-slate-700 hover:bg-slate-50"
                                    @click="app.go('/dashboard')"
                                >
                                    Back
                                </button>
                            </div>

                            <div class="mt-4 rounded-md border border-slate-200 bg-slate-100 p-3">
                                <iframe
                                    ref="iframeRef"
                                    class="h-[80vh] w-full rounded-md border border-slate-200 bg-white"
                                    :src="pdfViewSrc"
                                ></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, inject, ref } from 'vue'
import StudentSidebar from '../../components/student/StudentSidebar.vue'

const app = inject('app')

const iframeRef = ref(null)

const pdfViewSrc = computed(() => {
    const id = app?.studentInvoiceId
    if (!id) return ''
    return `/api/public/student/invoices/${encodeURIComponent(String(id))}/view#toolbar=0&navpanes=0&scrollbar=0`
})

function downloadInvoice() {
    const id = app?.studentInvoiceId
    if (!id) return
    const url = `/api/public/student/invoices/${encodeURIComponent(String(id))}/download`
    window.open(url, '_blank')
}

function printInvoice() {
    const win = iframeRef?.value?.contentWindow
    if (!win) return
    try {
        win.focus()
        win.print()
    } catch {
    }
}
</script>
