<template>
    <div class="mt-3 rounded-sm border border-slate-300 bg-white p-4 sm:p-6">
        <div class="mx-auto w-full max-w-6xl">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <!-- Sidebar -->
                <div class="lg:col-span-3">
                    <div class="overflow-hidden rounded-sm border border-slate-300 bg-[#3f4b7a] text-white">
                        <div class="border-b border-white/15 px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 overflow-hidden rounded-sm bg-white/10">
                                    <template v-if="studentMe?.profile">
                                        <img :src="studentMe.profile" class="h-full w-full object-cover" />
                                    </template>
                                    <div v-else
                                        class="flex h-full w-full items-center justify-center text-xs font-bold">
                                        {{ (studentMe?.name || 'NA').slice(0, 2).toUpperCase() }}
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-bold">{{ (studentMe?.name || '').toUpperCase() }}
                                    </div>
                                    <div class="mt-1 text-xs font-semibold opacity-90 truncate">
                                        ID: {{ studentMe?.student_id || '—' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="py-2">
                            <button v-for="item in menuItems" :key="item.key" @click="injectGo(item.href)"
                                class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition"
                                :class="studentSidebarActive === item.key ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10 text-white/80 hover:text-white'">
                                <span v-if="studentSidebarActive === item.key"
                                    class="absolute left-0 top-0 h-full w-1 bg-emerald-400"></span>
                                <div v-html="item.icon" class="h-4 w-4 opacity-90"></div>
                                <span>{{ item.label }}</span>
                            </button>
                        </div>
                        <div class="border-t border-white/15 px-4 py-3">
                            <button type="button"
                                class="w-full rounded-sm bg-white/10 px-4 py-2 text-xs font-bold transition hover:bg-white/20"
                                @click="injectDoStudentLogout" :disabled="studentAuthLoading">
                                LOGOUT
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="lg:col-span-9">
                    <slot></slot>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { inject, computed } from 'vue'

const studentMe = inject('studentMe')
const studentSidebarActive = inject('studentSidebarActive')
const studentAuthLoading = inject('studentAuthLoading', false)
const injectGo = inject('go')
const injectDoStudentLogout = inject('doStudentLogout')

const menuItems = [
    { key: 'dashboard', label: 'Dashboard', href: '/dashboard', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-8 9 8" /><path d="M5 10v10h5v-6h4v6h5V10" /></svg>' },
    { key: 'profile', label: 'My Profile', href: '/student/profile', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21a8 8 0 10-16 0" /><circle cx="12" cy="7" r="4" /></svg>' },
    { key: 'pay', label: 'Pay Now', href: '/student/pay-now', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2" /><path d="M2 10h20" /></svg>' },
    { key: 'history', label: 'Payment History', href: '/student/payment-history', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 101-4" /><path d="M3 4v4h4" /><path d="M12 7v5l3 3" /></svg>' },
    { key: 'fees', label: 'Fees List', href: '/student/fees', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6h13" /><path d="M8 12h13" /><path d="M8 18h13" /><path d="M3 6h.01" /><path d="M3 12h.01" /><path d="M3 18h.01" /></svg>' },
    { key: 'admit', label: 'Admit Card', href: '/student/admit-card', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H6a2 2 0 00-2 2v14a2 2 0 002 2h12a2 2 0 002-2V9z" /><path d="M14 3v6h6" /><path d="M8 13h8" /><path d="M8 17h6" /></svg>' },
    { key: 'result', label: 'Result', href: '/student/result', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18" /><path d="M7 14l4-4 3 3 5-6" /></svg>' },
    { key: 'subject', label: 'Subject Choice', href: '/student/subjects', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 016.5 17H20" /><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z" /></svg>' },
    { key: 'password', label: 'Change Password', href: '/student/change-password', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" /><path d="M7 11V7a5 5 0 0110 0v4" /></svg>' },
]
</script>
