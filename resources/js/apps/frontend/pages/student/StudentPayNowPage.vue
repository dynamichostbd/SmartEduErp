<template>
    <div class="mt-3 rounded-sm border border-slate-300 bg-white p-4 sm:p-6">
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
                    <div class="rounded-sm border border-slate-300 bg-white">
                        <div class="border-b border-slate-300 bg-slate-50 px-4 py-3 text-sm font-extrabold text-slate-800">PAY NOW</div>
                        <div class="p-4">
                            <div v-if="statusMessage" class="mb-4 rounded-sm border p-3 text-sm" :class="statusClass">{{ statusMessage }}</div>

                            <div v-if="loading" class="text-sm text-slate-600">Loading...</div>
                            <div v-else>
                                <div v-if="error" class="mb-4 rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-800">{{ error }}</div>

                                <form class="mx-auto mt-2 w-full max-w-2xl" @submit.prevent="initPayment">
                                    <div class="grid grid-cols-1 gap-4">
                                        <div class="grid grid-cols-1 items-center gap-2 sm:grid-cols-3">
                                            <div class="text-sm font-semibold text-slate-700">Date (Y-m-d) <span class="text-red-600">*</span></div>
                                            <div class="sm:col-span-2">
                                                <div class="relative">
                                                    <input v-model="form.date" type="text" class="h-10 w-full rounded-sm border border-emerald-400 bg-slate-100 px-3 pr-10 text-sm outline-none" disabled />
                                                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-emerald-600">✓</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 items-center gap-2 sm:grid-cols-3">
                                            <div class="text-sm font-semibold text-slate-700">Invoice Number <span class="text-red-600">*</span></div>
                                            <div class="sm:col-span-2">
                                                <div class="relative">
                                                    <input v-model="form.invoice_number" type="text" class="h-10 w-full rounded-sm border border-emerald-400 bg-slate-100 px-3 pr-10 text-sm outline-none" disabled />
                                                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-emerald-600">✓</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 items-center gap-2 sm:grid-cols-3">
                                            <div class="text-sm font-semibold text-slate-700">Payment Type <span class="text-red-600">*</span></div>
                                            <div class="sm:col-span-2">
                                                <select v-model="form.account_head_id" class="h-10 w-full rounded-sm border border-blue-400 bg-white px-3 text-sm outline-none" required>
                                                    <option value="">--Select Any--</option>
                                                    <option v-for="h in heads" :key="'spn-head-' + h.id" :value="String(h.account_head_id)">{{ h.account_head?.name || '' }}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 items-center gap-2 sm:grid-cols-3">
                                            <div class="text-sm font-semibold text-slate-700">Payable Amount <span class="text-red-600">*</span></div>
                                            <div class="sm:col-span-2">
                                                <input v-model="form.amount" type="text" class="h-10 w-full rounded-sm border border-slate-300 bg-slate-100 px-3 text-sm outline-none" placeholder="Amount" disabled />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-10 text-center">
                                        <button
                                            type="submit"
                                            class="rounded-sm bg-[#0b1d4d] px-10 py-2.5 text-sm font-extrabold text-white hover:bg-[#09163c]"
                                            :disabled="submitting || !form.account_head_id"
                                        >
                                            {{ submitting ? 'Processing...' : 'Pay Now' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, inject, onMounted, reactive, ref, watch } from 'vue'
import StudentSidebar from '../../components/student/StudentSidebar.vue'

const app = inject('app')

const loading = ref(false)
const submitting = ref(false)
const error = ref('')
const heads = ref([])
const invoice = ref(null)
const nextInvoiceNumber = ref('')

const form = reactive({
    date: '',
    invoice_number: '',
    account_head_id: '',
    amount: '',
})

const query = computed(() => {
    const qs = String(app?.search || '')
    try {
        return new URLSearchParams(qs.startsWith('?') ? qs : `?${qs}`)
    } catch {
        return new URLSearchParams()
    }
})

const status = computed(() => String(query.value.get('status') || ''))

const statusMessage = computed(() => {
    const st = status.value
    if (st === 'success') return 'Payment successful'
    if (st === 'failed') return 'Payment failed'
    if (st === 'cancelled') return 'Payment cancelled'
    return ''
})

const statusClass = computed(() => {
    const st = status.value
    if (st === 'success') return 'border-emerald-200 bg-emerald-50 text-emerald-900'
    if (st === 'failed') return 'border-red-200 bg-red-50 text-red-800'
    if (st === 'cancelled') return 'border-amber-200 bg-amber-50 text-amber-900'
    return 'border-slate-300 bg-slate-50 text-slate-700'
})

const selectedHead = computed(() => {
    const headId = String(form.account_head_id || '')
    return heads.value.find((x) => String(x?.account_head_id) === headId) || null
})

function syncAmountFromHead() {
    const amt = Number(selectedHead.value?.amount || 0) || 0
    form.amount = amt > 0 ? amt.toFixed(2) : ''
    invoice.value = null
    form.invoice_number = nextInvoiceNumber.value || ''
}

let headLookupSeq = 0

async function silentSyncInvoiceNumberForHead() {
    const seq = ++headLookupSeq
    if (!form.account_head_id) {
        invoice.value = null
        form.invoice_number = nextInvoiceNumber.value || ''
        return
    }

    try {
        const res = await window.axios.post('/api/public/student/pay-now/check-invoice', {
            account_head_id: form.account_head_id,
        })

        if (seq !== headLookupSeq) return

        const inv = res?.data || null
        invoice.value = inv
        if (inv?.invoice_number) {
            form.invoice_number = String(inv.invoice_number)
            return
        }
    } catch {
    }

    if (seq !== headLookupSeq) return

    invoice.value = null
    form.invoice_number = nextInvoiceNumber.value || ''
}

watch(
    () => form.account_head_id,
    () => {
        syncAmountFromHead()
        silentSyncInvoiceNumberForHead()
    }
)

async function loadSystems() {
    loading.value = true
    error.value = ''
    try {
        const res = await window.axios.get('/api/public/student/pay-now/systems')
        heads.value = Array.isArray(res?.data?.heads) ? res.data.heads : []
        nextInvoiceNumber.value = String(res?.data?.next_invoice_number || '')
        if (!form.invoice_number) {
            form.invoice_number = nextInvoiceNumber.value || ''
        }
    } catch (e) {
        heads.value = []
        error.value = e?.response?.data?.message || 'Failed to load invoices.'
    } finally {
        loading.value = false
    }
}

async function findPendingInvoiceForHead() {
    if (!form.account_head_id) return null
    error.value = ''
    invoice.value = null
    try {
        const res = await window.axios.post('/api/public/student/pay-now/check-invoice', {
            account_head_id: form.account_head_id,
        })
        const inv = res?.data || null
        invoice.value = inv
        if (inv?.invoice_number) {
            form.invoice_number = String(inv.invoice_number)
        }
        return inv
    } catch {
        invoice.value = null
        form.invoice_number = nextInvoiceNumber.value || ''
        return null
    }
}

async function initPayment() {
    if (!form.account_head_id) return
    if (submitting.value) return

    submitting.value = true
    error.value = ''
    try {
        const existing = await findPendingInvoiceForHead()
        if (existing?.invoice_number) {
            const pay = await window.axios.post('/api/public/student/pay-now/pay-existing', {
                invoice_number: String(existing.invoice_number),
            })
            const url = pay?.data?.gateway_url
            if (url) {
                window.location.href = url
                return
            }
            error.value = 'Sorry!! Payment cannot proceed at this time, please try again'
            return
        }

        const res = await window.axios.post('/api/public/student/pay-now/init', {
            account_head_id: form.account_head_id,
        })
        const url = res?.data?.gateway_url
        if (url) {
            window.location.href = url
            return
        }
        error.value = 'Sorry!! Payment cannot proceed at this time, please try again'
    } catch (e) {
        error.value = e?.response?.data?.message || 'Failed to initiate payment.'
    } finally {
        submitting.value = false
    }
}

onMounted(async () => {
    if (app?.studentSidebarActive !== 'pay') {
        try {
            app.studentSidebarActive = 'pay'
        } catch {
        }
    }

    const today = new Date()
    const mm = String(today.getMonth() + 1).padStart(2, '0')
    const dd = String(today.getDate()).padStart(2, '0')
    form.date = `${today.getFullYear()}-${mm}-${dd}`

    await loadSystems()
})
</script>
