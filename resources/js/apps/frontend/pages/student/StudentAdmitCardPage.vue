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
                        <div class="border-b border-slate-300 bg-slate-50 px-4 py-3 text-sm font-extrabold text-slate-800">ADMIT CARD</div>
                        <div class="p-4">
                            <div v-if="loading" class="text-sm text-slate-600">Loading...</div>
                            <div v-else-if="error" class="rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-800">{{ error }}</div>
                            <div v-else>
                                <div v-if="cards.length" class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead>
                                            <tr class="border-b bg-slate-100 text-left text-xs font-extrabold text-slate-700">
                                                <th class="px-3 py-2">#</th>
                                                <th class="px-3 py-2">Exam</th>
                                                <th class="px-3 py-2">Name</th>
                                                <th class="px-3 py-2">Issue Date</th>
                                                <th class="px-3 py-2">Expired Date</th>
                                                <th class="px-3 py-2">Eligibility</th>
                                                <th class="px-3 py-2">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(c, idx) in cards" :key="'sac-' + c.id" class="border-b last:border-b-0">
                                                <td class="px-3 py-2">{{ idx + 1 }}</td>
                                                <td class="px-3 py-2 font-semibold">{{ c.exam_name || '' }}</td>
                                                <td class="px-3 py-2">{{ c.name || '' }}</td>
                                                <td class="px-3 py-2">{{ c.issue_date || '—' }}</td>
                                                <td class="px-3 py-2">{{ c.expired_date || '—' }}</td>
                                                <td class="px-3 py-2">
                                                    <span class="rounded-sm px-2 py-1 text-xs font-extrabold" :class="eligible(c) ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'">
                                                        {{ eligible(c) ? 'ELIGIBLE' : 'NOT ELIGIBLE' }}
                                                    </span>
                                                </td>
                                                <td class="px-3 py-2">
                                                    <button
                                                        type="button"
                                                        class="rounded-sm bg-[#0b1d4d] px-3 py-1.5 text-xs font-extrabold text-white hover:bg-[#09163c] disabled:cursor-not-allowed disabled:opacity-60"
                                                        :disabled="!eligible(c)"
                                                        @click="download(c)"
                                                    >
                                                        Download
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-else class="rounded-sm border border-slate-300 bg-slate-50 p-4 text-center text-sm text-slate-700">No admit card found.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import StudentSidebar from '../../components/student/StudentSidebar.vue'

const app = inject('app')

const loading = computed(() => app?.studentAdmitCardLoading)
const error = computed(() => app?.studentAdmitCardError)
const cards = computed(() => app?.studentAdmitCards || [])

function eligible(card) {
    if (!card) return false
    if (String(card.status || '').toUpperCase() !== 'A') return true
    const student = Number(card.student_percent || 0) || 0
    const required = Number(card.present_percent || 0) || 0
    return student >= required
}

function download(card) {
    const id = card?.id
    if (!id) return
    const url = `/api/public/student/admit-cards/${encodeURIComponent(String(id))}/download`
    window.open(url, '_blank')
}
</script>
