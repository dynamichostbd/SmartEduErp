<template>
    <div class="max-w-xl mx-auto rounded-sm border border-slate-300 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50/50 p-4">
            <h2 class="text-lg font-bold text-slate-800">Fees Payment</h2>
        </div>
        <div class="p-6">
            <div class="mb-6 rounded-sm border border-emerald-100 bg-emerald-50/50 p-4">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 flex items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2" ry="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Digital Payment</div>
                        <div class="text-sm text-emerald-600">Securely pay your fees via Credit Card, BKash, Rocket or Net Banking.</div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Selected Fee Summary -->
                <div v-if="selectedHead" class="rounded-sm border border-slate-200 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-slate-400 uppercase">Selected Purpose</div>
                            <div class="text-base font-bold text-slate-900 mt-1">{{ selectedHead.name }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-bold text-slate-400 uppercase">Amount</div>
                            <div class="text-lg font-bold text-emerald-600 mt-1">{{ Number(selectedHead.amount || 0).toFixed(2) }} BDT</div>
                        </div>
                    </div>
                </div>

                <!-- Selector if not pre-selected -->
                <div v-else class="space-y-1">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Select Fee Purpose</label>
                    <select v-model="form.account_head_id" class="w-full rounded-sm border border-slate-300 p-2.5 text-sm outline-none focus:border-emerald-500">
                        <option value="">Choose a purpose...</option>
                        <option v-for="f in unpaidFees" :key="f.account_head_id" :value="f.account_head_id">{{ f.account_head?.name }} ({{ f.amount }} BDT)</option>
                    </select>
                </div>

                <div v-if="error" class="rounded-sm border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    {{ error }}
                </div>

                <div class="pt-4 flex flex-col gap-3">
                    <button 
                        class="w-full rounded-sm bg-slate-900 py-3 text-sm font-bold text-white hover:bg-emerald-600 transition-colors flex items-center justify-center gap-2"
                        :disabled="loading || (unpaidFees.length === 0 && !form.account_head_id)"
                        @click="payNow"
                    >
                        {{ loading ? 'Processing...' : 'Proceed to Payment Gateway' }}
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </button>
                    <button 
                        class="w-full py-2 text-xs font-bold text-slate-400 hover:text-slate-600"
                        @click="injectGo('/student/fees')"
                    >
                        Cancel and return to fees
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { inject, ref, computed, onMounted } from 'vue'

const fees = inject('studentFees', [])
const injectGo = inject('go')
const injectSearch = inject('search', '')

const form = ref({
    account_head_id: ''
})

const loading = ref(false)
const error = ref('')

const unpaidFees = computed(() => (fees.value || []).filter(f => !f.paid))

const selectedHead = computed(() => {
    const id = form.value.account_head_id
    if (!id) return null
    const f = (fees.value || []).find(x => String(x.account_head_id) === String(id))
    return f ? { ...f.account_head, amount: f.amount } : null
})

onMounted(() => {
    // Check if head is in URL query
    try {
        const params = new URLSearchParams(injectSearch.value || '')
        const headId = params.get('head')
        if (headId) {
            form.value.account_head_id = headId
        }
    } catch {
        // ignore
    }
})

const payNow = async () => {
    if (!form.value.account_head_id) {
        error.value = 'Please select a fee purpose first.'
        return
    }

    loading.value = true
    error.value = ''
    try {
        const res = await window.axios.post('/api/public/student/pay-invoice', { 
            account_head_id: form.value.account_head_id 
        })
        const url = res?.data?.gateway_url
        if (url) {
            window.location.href = url
        } else {
            error.value = 'Failed to initiate payment gateway.'
        }
    } catch (e) {
        error.value = e?.response?.data?.message || 'Payment initiation failed.'
    } finally {
        loading.value = false
    }
}
</script>
