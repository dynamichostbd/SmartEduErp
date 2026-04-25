<template>
    <div class="rounded-sm border border-slate-300 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50/50 p-4">
            <h2 class="text-lg font-bold text-slate-800">Admit Cards</h2>
        </div>
        <div class="p-6">
            <div v-if="loading" class="text-center py-10 text-slate-500 italic">Checking for available admit cards...</div>
            <div v-else-if="!admitCards.length" class="text-center py-12 flex flex-col items-center gap-3">
                <div class="p-4 bg-slate-50 rounded-full text-slate-300">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                </div>
                <div class="text-sm font-medium text-slate-600">No Admit Cards Available</div>
                <div class="text-xs text-slate-400">Your admit card will appear here once issued by the administration.</div>
            </div>
            <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="card in admitCards" :key="card.id" class="group relative rounded-sm border border-slate-200 bg-white p-5 hover:border-emerald-500 transition-all hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div class="h-10 w-10 flex items-center justify-center rounded-sm bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Examination</div>
                        <div class="mt-1 text-sm font-bold text-slate-900">{{ card.exam?.name || 'Annual Exam' }}</div>
                    </div>
                    <div class="mt-6">
                        <button 
                            class="w-full rounded-sm bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-600 transition-colors flex items-center justify-center gap-2"
                            @click="download(card.id)"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            Download PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { inject } from 'vue'

const admitCards = inject('studentAdmitCards', [])
const loading = inject('studentAdmitCardLoading', false)

const download = (id) => {
    // Implement download logic or call parent method
    const url = `/api/public/student/admit-cards/${id}/download`
    window.open(url, '_blank')
}
</script>
