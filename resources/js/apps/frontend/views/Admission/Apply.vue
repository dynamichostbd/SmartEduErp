<template>
    <div class="mt-3 rounded-sm border border-slate-300 bg-white p-4 sm:p-6">
        <div class="mx-auto w-full max-w-6xl">
            <div class="flex flex-wrap items-center justify-between gap-6 mb-8">
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#0d6b75] text-white shadow-lg shadow-[#0d6b75]/20">
                        <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22 10L12 5L2 10L12 15L22 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M6 12V17C6 17 6 20 12 20C18 20 18 17 18 17V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-black tracking-tight text-slate-800">STUDENT PAYMENT</div>
                        <div class="text-sm font-medium text-slate-500">Pay your application fees securely and easily</div>
                        <div class="mt-2 h-1 w-24 rounded-full bg-[#0d6b75]"></div>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-1.5 bg-white/50 backdrop-blur-sm rounded-2xl">
                    <button type="button" 
                        class="flex items-center gap-2 px-5 py-3 text-[13px] font-bold rounded-xl transition-all duration-300"
                        :class="app.applyFeesTab === 'fees' ? 'bg-[#0d6b75] text-white shadow-lg shadow-[#0d6b75]/30 -translate-y-0.5' : 'bg-white border border-slate-100 text-slate-500 hover:bg-slate-50 hover:text-[#0d6b75] shadow-sm'"
                        @click="app.applyFeesTab = 'fees'">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                        APPLICATION FEES
                    </button>
                    <button type="button" 
                        class="flex items-center gap-2 px-5 py-3 text-[13px] font-bold rounded-xl transition-all duration-300"
                        :class="app.applyFeesTab === 'certificate' ? 'bg-[#0d6b75] text-white shadow-lg shadow-[#0d6b75]/30 -translate-y-0.5' : 'bg-white border border-slate-100 text-slate-500 hover:bg-slate-50 hover:text-[#0d6b75] shadow-sm'"
                        @click="app.applyFeesTab = 'certificate'">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        APPLY FOR CERTIFICATE
                    </button>
                    <button type="button" 
                        class="flex items-center gap-2 px-5 py-3 text-[13px] font-bold rounded-xl transition-all duration-300"
                        :class="app.applyFeesTab === 'invoice' ? 'bg-[#0d6b75] text-white shadow-lg shadow-[#0d6b75]/30 -translate-y-0.5' : 'bg-white border border-slate-100 text-slate-500 hover:bg-slate-50 hover:text-[#0d6b75] shadow-sm'"
                        @click="app.applyFeesTab = 'invoice'">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><path d="M9 15h3"></path><path d="M9 19h6"></path></svg>
                        CHECK INVOICE
                    </button>
                </div>
            </div>

            <div class="mt-4 rounded-sm border border-slate-300 bg-white p-4 sm:p-6">

                <div v-if="app.applyFeesStatusMessage" class="mt-6 rounded-sm border p-3 text-sm"
                    :class="app.applyFeesStatusClass">
                    {{ app.applyFeesStatusMessage }}
                    <span v-if="app.applyFeesTranId" class="font-semibold"> ({{ app.applyFeesTranId }})</span>
                </div>

                <div v-if="app.applyFeesTab === 'fees'">
                    <div v-if="app.applyFeesLoading" class="mt-6 text-sm text-slate-600">Loading...</div>
                    <div v-else>
                        <div v-if="app.applyFeesError"
                            class="mt-6 rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                            {{ app.applyFeesError }}
                        </div>

                        <div v-if="!selectedFeeId" class="mt-6">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                                <div v-for="(p, index) in app.applyFeesPurposes" :key="'fee-card-' + p.id"
                                    class="relative flex flex-col justify-between rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition hover:shadow-md hover:-translate-y-1 duration-300">
                                    
                                    <!-- Top Section: Icon and Title -->
                                    <div class="flex items-center gap-4">
                                        <!-- Circular Icon with themed background -->
                                        <div :class="[
                                            'flex h-14 w-14 shrink-0 items-center justify-center rounded-full',
                                            index % 3 === 0 ? 'bg-emerald-100 text-emerald-600' : 
                                            index % 3 === 1 ? 'bg-blue-100 text-blue-600' : 'bg-purple-100 text-purple-600'
                                        ]">
                                            <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M13 2H6C4.89543 2 4 2.89543 4 4V20C4 21.1046 4.89543 22 6 22H18C19.1046 22 20 21.1046 20 20V9L13 2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M13 2V9H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <line x1="7" y1="13" x2="12" y2="13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                <line x1="7" y1="16" x2="10" y2="16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                <circle cx="17.5" cy="18.5" r="4.5" fill="currentColor" />
                                                <path d="M17.5 16v5M16 17.5c0-.8 1-.8 1.5-.8s1.5 0 1.5.8c0 .8-1 1-1.5 1.2s-1.5.4-1.5 1.2c0 .8 1 .8 1.5.8s1.5 0 1.5-.8" stroke="white" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                        
                                        <!-- Title and Dotted Line -->
                                        <div class="flex-1 overflow-hidden">
                                            <div class="text-[16px] font-bold text-slate-800 leading-tight truncate">
                                                {{ p?.head?.name || 'Fee' }}
                                            </div>
                                            <div class="mt-2 border-b border-dotted border-slate-300 w-full opacity-60"></div>
                                        </div>
                                    </div>

                                    <!-- Amount Box: Styled with themed background -->
                                    <div :class="[
                                        'mt-4 flex items-center justify-between rounded-xl px-5 py-2',
                                        index % 3 === 0 ? 'bg-emerald-50' : 
                                        index % 3 === 1 ? 'bg-blue-50' : 'bg-purple-50'
                                    ]">
                                        <div :class="[
                                            'flex items-center gap-2',
                                            index % 3 === 0 ? 'text-emerald-600' : 
                                            index % 3 === 1 ? 'text-blue-600' : 'text-purple-600'
                                        ]">
                                            <svg class="w-9 h-9" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <rect x="2" y="6" width="20" height="12" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
                                                <circle cx="12" cy="12" r="1.2" fill="currentColor"/>
                                                <path d="M6 12h.01M18 12h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        </div>
                                        <div class="flex items-baseline gap-1">
                                            <span class="text-2xl font-black" :class="[
                                                index % 3 === 0 ? 'text-emerald-600' : 
                                                index % 3 === 1 ? 'text-blue-600' : 'text-purple-600'
                                            ]">
                                                {{ Number(p?.amount || 0).toFixed(2) }}
                                            </span>
                                            <span class="text-xs font-bold text-slate-500 tracking-wider">BDT</span>
                                        </div>
                                    </div>

                                    <!-- Action Button: Gradient teal style -->
                                    <button type="button" @click="proceedToPay(p.id)"
                                        class="mt-8 flex w-full items-center justify-center gap-2 rounded-xl bg-[#0d6b75] py-3.5 text-[15px] font-semibold text-white shadow-md transition-all hover:shadow-lg hover:brightness-110 active:scale-[0.98]">
                                        Proceed to Pay 
                                        <i class="fa-solid fa-arrow-right text-sm"></i>
                                    </button>
                                </div>
                            </div>
                            <div v-if="!app.applyFeesPurposes || app.applyFeesPurposes.length === 0"
                                class="mt-8 text-center py-10 rounded-2xl border border-dashed border-slate-300 bg-slate-50 text-slate-500 font-medium">
                                <i class="fa-solid fa-receipt text-3xl mb-3 opacity-20 block"></i>
                                No fees available at this moment.
                            </div>
                        </div>

                        <div v-else class="mt-8">
                            <div class="mb-8">
                                <button type="button" @click="selectedFeeId = null"
                                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-teal-50 text-[#0d6b75] font-bold text-sm hover:bg-teal-100 transition-colors">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                                    Back to Fees
                                </button>
                            </div>
                            
                            <form @submit.prevent="app.submitApplyFees" class="relative">
                                <!-- Decorative background dots -->
                                <div class="absolute -top-10 -right-10 opacity-20 pointer-events-none">
                                    <svg width="100" height="100" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="2" cy="2" r="2" fill="#0d6b75"/><circle cx="22" cy="2" r="2" fill="#0d6b75"/><circle cx="42" cy="2" r="2" fill="#0d6b75"/><circle cx="62" cy="2" r="2" fill="#0d6b75"/><circle cx="82" cy="2" r="2" fill="#0d6b75"/>
                                        <circle cx="2" cy="22" r="2" fill="#0d6b75"/><circle cx="22" cy="22" r="2" fill="#0d6b75"/><circle cx="42" cy="22" r="2" fill="#0d6b75"/><circle cx="62" cy="22" r="2" fill="#0d6b75"/><circle cx="82" cy="22" r="2" fill="#0d6b75"/>
                                        <circle cx="2" cy="42" r="2" fill="#0d6b75"/><circle cx="22" cy="42" r="2" fill="#0d6b75"/><circle cx="42" cy="42" r="2" fill="#0d6b75"/><circle cx="62" cy="42" r="2" fill="#0d6b75"/><circle cx="82" cy="42" r="2" fill="#0d6b75"/>
                                    </svg>
                                </div>

                                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                                    <!-- Column 1: Personal Info -->
                                    <div class="space-y-5">
                                        <div class="relative group">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 text-[#0d6b75]">
                                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                            </div>
                                            <input v-model="app.applyFeesForm.name" type="text" placeholder="Full Name"
                                                class="h-14 w-full rounded-2xl border border-slate-100 bg-white pl-12 pr-4 text-[15px] font-medium outline-none focus:border-[#0d6b75] focus:ring-4 focus:ring-[#0d6b75]/5 transition-all shadow-sm group-hover:border-slate-200"
                                                required />
                                        </div>
                                        <div class="relative group">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 text-emerald-500">
                                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                            </div>
                                            <input v-model="app.applyFeesForm.mobile" type="text" placeholder="Mobile"
                                                class="h-14 w-full rounded-2xl border border-slate-100 bg-[#f8fafc] pl-12 pr-4 text-[15px] font-medium outline-none focus:border-[#0d6b75] focus:ring-4 focus:ring-[#0d6b75]/5 transition-all shadow-sm group-hover:border-slate-200"
                                                required />
                                        </div>
                                        <div class="relative group">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 text-orange-400">
                                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"></rect><path d="M7 8h10"></path><path d="M7 12h10"></path><path d="M7 16h10"></path></svg>
                                            </div>
                                            <input v-model="app.applyFeesForm.admission_roll" type="text"
                                                placeholder="Application ID / Admission Roll"
                                                class="h-14 w-full rounded-2xl border border-slate-100 bg-white pl-12 pr-4 text-[15px] font-medium outline-none focus:border-[#0d6b75] focus:ring-4 focus:ring-[#0d6b75]/5 transition-all shadow-sm group-hover:border-slate-200"
                                                required />
                                        </div>
                                    </div>

                                    <!-- Column 2: Academic Info -->
                                    <div class="space-y-5">
                                        <div class="relative group">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 text-blue-500">
                                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                            </div>
                                            <select v-model="app.applyFeesForm.academic_session_id"
                                                class="h-14 w-full appearance-none rounded-2xl border border-slate-100 bg-white pl-12 pr-10 text-[15px] font-medium outline-none focus:border-[#0d6b75] focus:ring-4 focus:ring-[#0d6b75]/5 transition-all shadow-sm group-hover:border-slate-200"
                                                required>
                                                <option value="">Session</option>
                                                <option v-for="s in app.applyFeesSessionsSorted" :key="'afs-' + s.id"
                                                    :value="String(s.id)">{{ s.name }}</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                            </div>
                                        </div>

                                        <div class="relative group">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 text-[#8b5cf6]">
                                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10L12 5L2 10L12 15L22 10Z"></path><path d="M6 12V17C6 17 6 20 12 20C18 20 18 17 18 17V12"></path></svg>
                                            </div>
                                            <select v-model="app.applyFeesForm.academic_qualification_id"
                                                class="h-14 w-full appearance-none rounded-2xl border border-slate-100 bg-white pl-12 pr-10 text-[15px] font-medium outline-none focus:border-[#0d6b75] focus:ring-4 focus:ring-[#0d6b75]/5 transition-all shadow-sm group-hover:border-slate-200"
                                                required>
                                                <option value="">Academic Level</option>
                                                <option v-for="q in app.applyFeesQualifications" :key="'afq-' + q.id"
                                                    :value="String(q.id)">{{ q.name }}</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                            </div>
                                        </div>

                                        <div class="relative group">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 text-sky-500">
                                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"></path><path d="M3 7v1a3 3 0 0 0 6 0V7m0 1a3 3 0 0 0 6 0V7m0 1a3 3 0 0 0 6 0V7H3"></path><path d="M19 21V11"></path><path d="M5 21V11"></path><path d="M12 21V11"></path></svg>
                                            </div>
                                            <select v-model="app.applyFeesForm.department_id"
                                                :disabled="!app.applyFeesForm.academic_qualification_id"
                                                class="h-14 w-full appearance-none rounded-2xl border border-slate-100 bg-white pl-12 pr-10 text-[15px] font-medium outline-none focus:border-[#0d6b75] focus:ring-4 focus:ring-[#0d6b75]/5 transition-all shadow-sm group-hover:border-slate-200 disabled:bg-slate-50 disabled:text-slate-400"
                                                required>
                                                <option value="">Department/Group</option>
                                                <option v-for="d in app.applyFeesFilteredDepartments" :key="'afd-' + d.id"
                                                    :value="String(d.id)">{{ d.name }}</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                            </div>
                                        </div>

                                        <div class="relative group">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 text-pink-500">
                                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                                            </div>
                                            <select v-model="app.applyFeesForm.academic_class_id"
                                                :disabled="!app.applyFeesForm.academic_qualification_id"
                                                class="h-14 w-full appearance-none rounded-2xl border border-slate-100 bg-white pl-12 pr-10 text-[15px] font-medium outline-none focus:border-[#0d6b75] focus:ring-4 focus:ring-[#0d6b75]/5 transition-all shadow-sm group-hover:border-slate-200 disabled:bg-slate-50 disabled:text-slate-400"
                                                required>
                                                <option value="">Class</option>
                                                <option v-for="c in app.applyFeesFilteredClasses" :key="'afc-' + c.id"
                                                    :value="String(c.id)">{{ c.name }}</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Column 3: Display & Actions -->
                                    <div class="space-y-6">
                                        <div class="relative group">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 text-emerald-500">
                                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>
                                            </div>
                                            <select v-model="app.applyFeesForm.purpose_id"
                                                class="h-14 w-full appearance-none rounded-2xl border border-slate-100 bg-white pl-12 pr-10 text-[15px] font-medium outline-none shadow-sm cursor-not-allowed opacity-80"
                                                disabled required>
                                                <option value="">Fees</option>
                                                <option v-for="p in app.applyFeesPurposes" :key="'afp-' + p.id"
                                                    :value="String(p.id)">{{ p?.head?.name || '' }}</option>
                                            </select>
                                        </div>

                                        <!-- Amount Display Box -->
                                        <div class="relative overflow-hidden rounded-3xl bg-teal-50 border border-teal-100 p-6 flex items-center justify-between group transition-all hover:shadow-md">
                                            <div>
                                                <div class="text-sm font-bold text-slate-500 mb-1">Amount</div>
                                                <div class="flex items-baseline gap-2">
                                                    <div class="text-3xl font-black text-[#0d6b75]">
                                                        {{ app.applyFeesSelectedAmountText }}
                                                    </div>
                                                    <div class="text-sm font-bold text-[#0d6b75]/60 uppercase tracking-wider">BDT</div>
                                                </div>
                                            </div>
                                            <div class="relative w-16 h-16 transform transition-transform group-hover:scale-110">
                                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M19 7H5C3.89543 7 3 7.89543 3 9V18C3 19.1046 3.89543 20 5 20H19C20.1046 20 21 19.1046 21 18V9C21 7.89543 20.1046 7 19 7Z" fill="#0d6b75" fill-opacity="0.1" stroke="#0d6b75" stroke-width="1.5"/>
                                                    <path d="M16 11H21V16H16V11Z" fill="white" stroke="#0d6b75" stroke-width="1.5" stroke-linejoin="round"/>
                                                    <circle cx="18.5" cy="13.5" r="1.5" fill="#0d6b75"/>
                                                    <path d="M7 7V5C7 4.44772 7.44772 4 8 4H16C16.5523 4 17 4.44772 17 5V7" stroke="#0d6b75" stroke-width="1.5" stroke-linecap="round"/>
                                                </svg>
                                                <!-- Tiny tick -->
                                                <div class="absolute -top-1 -right-1 bg-emerald-500 text-white rounded-full p-1 shadow-sm">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                </div>
                                            </div>
                                        </div>

                                        <div v-if="app.applyFeesInvoiceError"
                                            class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 flex items-center gap-2">
                                            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                            {{ app.applyFeesInvoiceError }}
                                        </div>

                                        <!-- Pay Now Button -->
                                        <button type="submit"
                                            class="group relative w-full h-16 rounded-2xl bg-[#0d6b75] text-white font-bold text-lg shadow-xl shadow-[#0d6b75]/30 transition-all hover:shadow-[#0d6b75]/40 hover:-translate-y-1 active:scale-95 disabled:opacity-50 disabled:pointer-events-none overflow-hidden"
                                            :disabled="app.applyFeesSubmitting">
                                            <div class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                                            <div class="relative flex items-center justify-center gap-3">
                                                <svg class="w-5 h-5 transition-transform group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                                <span>{{ app.applyFeesSubmitting ? 'Processing...' : 'Pay Now' }}</span>
                                                <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>


                <div v-else-if="app.applyFeesTab === 'certificate'">
                    <div v-if="app.certificateLoading" class="mt-6 text-sm text-slate-600">Loading...</div>
                    <div v-else>
                        <div v-if="app.certificateError"
                            class="mt-6 rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                            {{ app.certificateError }}
                        </div>

                        <form class="mt-8" @submit.prevent="app.submitCertificate">
                            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                                <div class="rounded-sm border border-slate-300 bg-white p-4">
                                    <div class="border-l-4 border-orange-500 pl-3 text-sm font-bold text-slate-900">
                                        STUDENT INFO</div>

                                    <div class="mt-4 flex items-center gap-2">
                                        <input v-model="app.certificateForm.mobile" type="text" placeholder="Mobile"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            required />
                                        <button type="button"
                                            class="h-10 w-12 rounded-sm border border-slate-300 bg-slate-50 text-sm font-bold text-slate-700 hover:bg-white"
                                            :disabled="app.certificateLookupLoading" @click="app.certificateLookup">
                                            {{ app.certificateLookupLoading ? '…' : 'Go' }}
                                        </button>
                                    </div>
                                    <div v-if="app.certificateLookupError"
                                        class="mt-3 rounded-sm border border-red-200 bg-red-50 p-2 text-xs text-red-800">
                                        {{ app.certificateLookupError }}</div>

                                    <div class="mt-4 text-sm font-semibold text-slate-800">Certificate Type :</div>
                                    <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-slate-700">
                                        <label class="inline-flex items-center gap-2">
                                            <input v-model="app.certificateForm.certificate_type" type="radio"
                                                name="certificate_type" value="en" />
                                            English
                                        </label>
                                        <label class="inline-flex items-center gap-2">
                                            <input v-model="app.certificateForm.certificate_type" type="radio"
                                                name="certificate_type" value="bn" />
                                            Bangla
                                        </label>
                                        <label class="inline-flex items-center gap-2">
                                            <input v-model="app.certificateForm.certificate_type" type="radio"
                                                name="certificate_type" value="both" />
                                            Both
                                        </label>
                                    </div>

                                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <input v-model="app.certificateForm.student_name_en" type="text"
                                            placeholder="Student name (EN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateEnDisabled"
                                            :required="!app.certificateEnDisabled" />
                                        <input v-model="app.certificateForm.student_name_bn" type="text"
                                            placeholder="Student name (BN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateBnDisabled"
                                            :required="!app.certificateBnDisabled" />

                                        <input v-model="app.certificateForm.fathers_name_en" type="text"
                                            placeholder="Father's name (EN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateEnDisabled"
                                            :required="!app.certificateEnDisabled" />
                                        <input v-model="app.certificateForm.fathers_name_bn" type="text"
                                            placeholder="Father's name (BN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateBnDisabled"
                                            :required="!app.certificateBnDisabled" />

                                        <input v-model="app.certificateForm.mothers_name_en" type="text"
                                            placeholder="Mother's name (EN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateEnDisabled"
                                            :required="!app.certificateEnDisabled" />
                                        <input v-model="app.certificateForm.mothers_name_bn" type="text"
                                            placeholder="Mother's name (BN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateBnDisabled"
                                            :required="!app.certificateBnDisabled" />

                                        <input v-model="app.certificateForm.academic_year_en" type="text"
                                            placeholder="Academic year (EN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateEnDisabled"
                                            :required="!app.certificateEnDisabled" />
                                        <input v-model="app.certificateForm.academic_year_bn" type="text"
                                            placeholder="Academic year (BN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateBnDisabled"
                                            :required="!app.certificateBnDisabled" />

                                        <input v-model="app.certificateForm.registration_no_en" type="text"
                                            placeholder="Registration no (EN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateEnDisabled"
                                            :required="!app.certificateEnDisabled" />
                                        <input v-model="app.certificateForm.registration_no_bn" type="text"
                                            placeholder="Registration no (BN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateBnDisabled"
                                            :required="!app.certificateBnDisabled" />

                                        <input v-model="app.certificateForm.exam_roll_en" type="text"
                                            placeholder="Exam roll (EN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateEnDisabled"
                                            :required="!app.certificateEnDisabled" />
                                        <input v-model="app.certificateForm.exam_roll_bn" type="text"
                                            placeholder="Exam roll (BN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateBnDisabled"
                                            :required="!app.certificateBnDisabled" />

                                        <input v-model="app.certificateForm.exam_year_en" type="text"
                                            placeholder="Exam year (EN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateEnDisabled"
                                            :required="!app.certificateEnDisabled" />
                                        <input v-model="app.certificateForm.exam_year_bn" type="text"
                                            placeholder="Exam year (BN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateBnDisabled"
                                            :required="!app.certificateBnDisabled" />

                                        <input v-model="app.certificateForm.gpa_en" type="text" placeholder="GPA (EN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateEnDisabled"
                                            :required="!app.certificateEnDisabled" />
                                        <input v-model="app.certificateForm.gpa_bn" type="text" placeholder="GPA (BN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateBnDisabled"
                                            :required="!app.certificateBnDisabled" />

                                        <input v-model="app.certificateForm.division_en" type="text"
                                            placeholder="Division (EN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateEnDisabled"
                                            :required="!app.certificateEnDisabled" />
                                        <input v-model="app.certificateForm.division_bn" type="text"
                                            placeholder="Division (BN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateBnDisabled"
                                            :required="!app.certificateBnDisabled" />
                                    </div>
                                </div>

                                <div class="rounded-sm border border-slate-300 bg-white p-4">
                                    <div class="border-l-4 border-orange-500 pl-3 text-sm font-bold text-slate-900">
                                        APPLY FOR CERTIFICATE</div>

                                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <select v-model="app.certificateForm.academic_session_id"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            required>
                                            <option value="">--Select Session--</option>
                                            <option v-for="s in app.certificateSessionsSorted" :key="'cs-' + s.id"
                                                :value="String(s.id)">{{ s.name }}</option>
                                        </select>

                                        <select v-model="app.certificateForm.template_id"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            required>
                                            <option value="">--Select Certificate--</option>
                                            <option v-for="t in app.certificateFilteredTemplates" :key="'ct-' + t.id"
                                                :value="String(t.id)">{{ t.title }}</option>
                                        </select>

                                        <select v-model="app.certificateForm.academic_qualification_id"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            required>
                                            <option value="">--Select Academic Level--</option>
                                            <option v-for="q in app.certificateQualifications" :key="'cq-' + q.id"
                                                :value="String(q.id)">{{ q.name }}</option>
                                        </select>

                                        <select v-model="app.certificateForm.department_id"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :required="app.certificateDepartmentRequired">
                                            <option value="">--Select Department/Group--</option>
                                            <option v-for="d in app.certificateFilteredDepartments" :key="'cd-' + d.id"
                                                :value="String(d.id)">{{ d.name }}</option>
                                        </select>
                                    </div>

                                    <div v-if="app.certificateClasses && app.certificateClasses.length" class="mt-3">
                                        <select v-model="app.certificateForm.academic_class_id"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :required="app.certificateClassRequired">
                                            <option value="">--Select Class--</option>
                                            <option v-for="c in app.certificateFilteredClasses" :key="'cc-' + c.id"
                                                :value="String(c.id)">{{ c.name }}</option>
                                        </select>
                                    </div>

                                    <div
                                        class="mt-4 rounded-sm border border-slate-300 bg-slate-50 p-4 text-sm text-slate-700">
                                        <div class="font-semibold">Amount</div>
                                        <div class="mt-1 text-lg font-bold text-slate-900">{{
                                            app.certificateSelectedAmountText }}</div>
                                    </div>

                                    <div class="mt-6 text-center">
                                        <button type="submit"
                                            class="rounded-sm bg-[#0b1d4d] px-10 py-2.5 text-sm font-bold text-white hover:bg-[#09163c]"
                                            :disabled="app.certificateSubmitting">
                                            {{ app.certificateSubmitting ? 'Submitting...' : 'SUBMIT' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div v-else-if="app.applyFeesTab === 'invoice'">
                    <div
                        class="mt-6 flex flex-col md:flex-row min-h-[400px] rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <!-- Left Side -->
                        <div
                            class="w-full md:w-2/5 bg-[#f8fafc] p-8 sm:p-12 flex flex-col items-center justify-center relative border-b md:border-b-0 md:border-r border-slate-200">
                            <div class="mb-8 relative z-10">
                                <svg width="120" height="120" viewBox="0 0 120 120" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="20" cy="20" r="4" fill="#69b4bd" opacity="0.5" />
                                    <circle cx="100" cy="90" r="6" fill="#12919e" opacity="0.3" />
                                    <path d="M10 80 L15 75 L20 80 L15 85 Z" fill="#0d6b75" opacity="0.4" />
                                    <path
                                        d="M40 25 H80 V85 L75 80 L70 85 L65 80 L60 85 L55 80 L50 85 L45 80 L40 85 V25 Z"
                                        fill="#ffffff" stroke="#0d6b75" stroke-width="4" stroke-linejoin="round" />
                                    <line x1="50" y1="40" x2="70" y2="40" stroke="#e2e8f0" stroke-width="3"
                                        stroke-linecap="round" />
                                    <line x1="50" y1="50" x2="65" y2="50" stroke="#e2e8f0" stroke-width="3"
                                        stroke-linecap="round" />
                                    <line x1="50" y1="60" x2="60" y2="60" stroke="#e2e8f0" stroke-width="3"
                                        stroke-linecap="round" />
                                    <circle cx="65" cy="70" r="12" fill="#0d6b75" />
                                    <path d="M61 70 L64 73 L69 67" stroke="#ffffff" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            <h2 class="text-3xl font-bold text-slate-800 mb-4 z-10 text-center tracking-tight">
                                Check <br />
                                <span
                                    class="bg-gradient-to-r from-[#0d6b75] to-[#20a3b2] bg-clip-text text-transparent">Invoice</span>
                            </h2>
                            <div class="h-1.5 w-16 bg-gradient-to-r from-[#0d6b75] to-[#20a3b2] rounded-full z-10 mb-5">
                            </div>
                            <p class="text-sm text-slate-500 z-10 text-center leading-relaxed">
                                Select invoice type and enter your details to verify status.
                            </p>
                        </div>

                        <!-- Right Side - Form -->
                        <div class="w-full md:w-3/5 p-8 sm:p-10 flex flex-col justify-center bg-white">
                            <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label
                                    class="flex items-center gap-4 cursor-pointer rounded-xl border-2 p-4 transition-all"
                                    :class="invoiceCheckType === 'application' ? 'border-[#0d6b75] bg-[#0d6b75]/5 shadow-sm' : 'border-slate-200 bg-white hover:border-[#0d6b75]/40'">
                                    <input type="radio" value="application" v-model="invoiceCheckType"
                                        class="accent-[#0d6b75] w-4 h-4 shrink-0" />
                                    <div>
                                        <div class="font-bold text-slate-800 text-sm">Application Fee</div>
                                        <div class="text-xs text-slate-500 mt-0.5">Check admission application invoice
                                        </div>
                                    </div>
                                    <div class="ml-auto shrink-0 w-9 h-9 flex items-center justify-center rounded-full"
                                        :class="invoiceCheckType === 'application' ? 'bg-[#0d6b75] text-white' : 'bg-slate-100 text-slate-400'">
                                        <i class="fa-solid fa-file-invoice text-sm"></i>
                                    </div>
                                </label>
                                <label
                                    class="flex items-center gap-4 cursor-pointer rounded-xl border-2 p-4 transition-all"
                                    :class="invoiceCheckType === 'certificate' ? 'border-[#0d6b75] bg-[#0d6b75]/5 shadow-sm' : 'border-slate-200 bg-white hover:border-[#0d6b75]/40'">
                                    <input type="radio" value="certificate" v-model="invoiceCheckType"
                                        class="accent-[#0d6b75] w-4 h-4 shrink-0" />
                                    <div>
                                        <div class="font-bold text-slate-800 text-sm">Certificate Fee</div>
                                        <div class="text-xs text-slate-500 mt-0.5">Check certificate application invoice
                                        </div>
                                    </div>
                                    <div class="ml-auto shrink-0 w-9 h-9 flex items-center justify-center rounded-full"
                                        :class="invoiceCheckType === 'certificate' ? 'bg-[#0d6b75] text-white' : 'bg-slate-100 text-slate-400'">
                                        <i class="fa-solid fa-certificate text-sm"></i>
                                    </div>
                                </label>
                            </div>

                            <div v-if="invoiceCheckType" class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="relative group">
                                        <div
                                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[#0d6b75]/60">
                                            <i class="fa-solid fa-phone text-lg"></i>
                                        </div>
                                        <input v-model="invoiceCheckMobile" type="text" placeholder="Mobile Number"
                                            class="h-12 w-full rounded-md border border-slate-300 bg-white pl-11 pr-4 text-sm outline-none focus:border-[#0d6b75] focus:ring-1 focus:ring-[#0d6b75]" />
                                    </div>
                                    <div class="relative group">
                                        <div
                                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[#0d6b75]/60">
                                            <i class="fa-regular fa-user text-lg"></i>
                                        </div>
                                        <input v-model="invoiceCheckRoll" type="text"
                                            placeholder="Admission Roll / Reg No"
                                            class="h-12 w-full rounded-md border border-slate-300 bg-white pl-11 pr-4 text-sm outline-none focus:border-[#0d6b75] focus:ring-1 focus:ring-[#0d6b75]" />
                                    </div>
                                </div>

                                <button @click="handleInvoiceCheck" type="button"
                                    class="w-full sm:w-auto mt-2 rounded-md bg-gradient-to-r from-[#0d6b75] to-[#12919e] px-8 py-3 text-sm font-bold text-white shadow-md hover:shadow-lg transition-transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                                    <span v-if="invoiceChecking"><i class="fa-solid fa-spinner fa-spin"></i>
                                        Checking...</span>
                                    <span v-else><i class="fa-solid fa-magnifying-glass"></i> Check Invoice</span>
                                </button>

                                <div v-if="invoiceCheckError"
                                    class="mt-4 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800 flex items-center gap-2">
                                    <i class="fa-solid fa-circle-exclamation"></i> {{ invoiceCheckError }}
                                </div>
                            </div>
                            <div v-else class="flex flex-col items-center justify-center py-10 text-slate-400">
                                <i class="fa-solid fa-arrow-up text-2xl mb-2 animate-bounce"></i>
                                <p class="text-sm font-medium">Please select an invoice type above</p>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Result: full-width below the card -->
                    <div v-if="invoiceResult" class="mt-6">
                        <!-- Action Buttons row -->
                        <div class="flex items-center justify-between mb-4 flex-wrap gap-2 px-1">
                            <div class="flex items-center gap-2 text-sm font-semibold text-slate-600">
                                <i class="fa-solid fa-file-invoice text-[#0d6b75]"></i> Invoice Found
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="printInvoice"
                                    class="flex items-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 shadow-sm transition">
                                    <i class="fa-solid fa-print"></i> Print
                                </button>
                            </div>
                        </div>

                        <!-- Two invoice copies — stacked vertically like admin panel -->
                        <div id="invoicePrintArea" class="w-[60%] mx-auto">
                            <div v-for="copy in 2" :key="copy"
                                class="invoice mb-4 border border-slate-300 bg-white p-4">

                                <!-- Header: logo left, title + dates right (grid-cols-12) -->
                                <div class="grid grid-cols-12 gap-4 border-b-2 border-slate-300 pb-2">
                                    <div class="col-span-3">
                                        <img v-if="app.site?.logo"
                                            :src="app.site.logo?.startsWith('http') ? app.site.logo : ((laravel?.baseurl || '').replace(/\/$/, '')) + '/storage/' + (app.site.logo || '').replace(/^\//, '')"
                                            class="h-[75px] w-auto object-contain" />
                                    </div>
                                    <div class="col-span-9">
                                        <div class="text-lg font-semibold text-slate-900">{{ app.collegeName ||
                                            '' }}</div>
                                        <div class="mt-1 text-right text-xs font-semibold text-slate-800">
                                            <template v-if="invoiceResult.payment_date">
                                                <span class="text-slate-500 underline"
                                                    style="font-size:10px">PAYMENT:</span>
                                                <span class="ml-1">{{ invoiceResult.payment_date }}</span>
                                                <span class="mx-2">|</span>
                                            </template>
                                            <span class="text-slate-500 underline"
                                                style="font-size:10px">CREATED:</span>
                                            <span class="ml-1">{{ invoiceResult.invoice_date }}</span>
                                            <span class="mx-2">|</span>
                                            <span>{{ invoiceResult.invoice_number }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Student info (col-span-7) + Paid stamp (col-span-5) -->
                                <div class="mt-3 grid grid-cols-12 gap-4">
                                    <div class="col-span-7 text-sm text-slate-800">
                                        <div><span class="font-semibold">Name :</span> {{ invoiceResult.name || '—' }}
                                        </div>
                                        <div><span class="font-semibold">Mobile :</span> {{ invoiceResult.mobile || '—'
                                            }}</div>
                                        <div class="mt-2">
                                            <span class="font-semibold">Level :</span> {{
                                                invoiceResult.qualification_name || '' }}
                                            <span v-if="invoiceResult.session_name">({{ invoiceResult.session_name
                                                }})</span>
                                        </div>
                                        <div>
                                            <span class="font-semibold">Dept. :</span> {{ invoiceResult.department_name
                                                || '' }}
                                            <span v-if="invoiceResult.class_name">({{ invoiceResult.class_name
                                                }})</span>
                                        </div>
                                        <div v-if="invoiceResult.admission_roll" class="mt-2">
                                            <div><span class="font-semibold">Admission Roll :</span> {{
                                                invoiceResult.admission_roll }}</div>
                                        </div>
                                    </div>
                                    <div class="col-span-5 flex items-center justify-center">
                                        <img v-if="['success', 'paid'].includes(String(invoiceResult.status || '').toLowerCase().trim())"
                                            :src="((laravel?.baseurl || '').replace(/\/$/, '')) + '/images/paid.png'"
                                            class="h-[120px] w-[120px] object-contain" />
                                    </div>
                                </div>

                                <!-- EPAYMENT INVOICE label -->
                                <div class="mt-4 text-center text-base font-semibold text-slate-900">EPAYMENT INVOICE
                                </div>

                                <!-- Fee table -->
                                <div class="mt-3 overflow-hidden rounded-xl border border-slate-300">
                                    <table class="w-full border-collapse text-sm">
                                        <thead class="bg-emerald-100">
                                            <tr>
                                                <th class="border border-slate-300 px-3 py-2 text-center font-semibold"
                                                    style="width:90px">Sl No.</th>
                                                <th class="border border-slate-300 px-3 py-2 text-left font-semibold">
                                                    Description</th>
                                                <th class="border border-slate-300 px-3 py-2 text-right font-semibold"
                                                    style="width:130px">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white">
                                            <tr>
                                                <td class="border border-slate-300 px-3 py-2 text-center">01</td>
                                                <td class="border border-slate-300 px-3 py-2">{{ invoiceResult.head_name
                                                    || (invoiceCheckType ===
                                                        'certificate' ? 'Certificate Fee' : 'Application Fee') }}</td>
                                                <td class="border border-slate-300 px-3 py-2 text-right font-semibold">
                                                    {{ Number(invoiceResult.amount ||
                                                        0).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Inword -->
                                <div class="mt-3 text-xs text-slate-700">
                                    <span class="font-semibold">Inword :</span> {{ Number(invoiceResult.amount ||
                                        0).toFixed(2) }} taka only
                                </div>

                                <div class="mt-6 text-center text-xs text-slate-500">Powered By Dynamic Host BD</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



</template>

<script setup>
import { inject, ref } from 'vue'
const app = inject('app')
const laravel = window.laravel

const selectedFeeId = ref(null)

const proceedToPay = (feeId) => {
    app.applyFeesForm.purpose_id = String(feeId)
    selectedFeeId.value = String(feeId)
}

const invoiceCheckType = ref('')
const invoiceCheckMobile = ref('')
const invoiceCheckRoll = ref('')
const invoiceChecking = ref(false)
const invoiceCheckError = ref('')
const invoiceResult = ref(null)
const invoicePaying = ref(false)

const printInvoice = () => {
    const el = document.getElementById('invoicePrintArea')
    if (!el) return
    const win = window.open('', '_blank', 'width=900,height=800')
    win.document.write(`
        <html>
            <head>
                <title>Invoice Print</title>
                <script src="https://cdn.tailwindcss.com"><\/script>
                <style>
                    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
                    body { font-family: 'Inter', sans-serif; }
                    @media print {
                        @page { margin: 5mm; }
                        body { margin: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    }
                    /* Ensure grid works in print */
                    .grid { display: grid !important; }
                    .grid-cols-12 { grid-template-columns: repeat(12, minmax(0, 1fr)) !important; }
                    .col-span-3 { grid-column: span 3 / span 3 !important; }
                    .col-span-9 { grid-column: span 9 / span 9 !important; }
                    .col-span-7 { grid-column: span 7 / span 7 !important; }
                    .col-span-5 { grid-column: span 5 / span 5 !important; }
                </style>
            </head>
            <body class="p-6 bg-white">
                <div class="w-full max-w-[800px] mx-auto">
                    ${el.innerHTML}
                </div>
                <script>
                    window.onload = function() {
                        setTimeout(() => {
                            window.print();
                            window.close();
                        }, 800);
                    };
                <\/script>
            </body>
        </html>
    `)
    win.document.close()
}

const handleInvoiceCheck = async () => {
    if (!invoiceCheckMobile.value || !invoiceCheckRoll.value) {
        invoiceCheckError.value = "Please enter both Mobile and Admission Roll."
        return
    }
    invoiceChecking.value = true
    invoiceCheckError.value = ''
    invoiceResult.value = null

    try {
        let endpoint = ''
        if (invoiceCheckType.value === 'application') {
            endpoint = '/api/public/apply-fees/check-invoice-by-roll'
        } else if (invoiceCheckType.value === 'certificate') {
            endpoint = '/api/public/certificate/check-invoice-by-roll'
        }

        const res = await window.axios.post(endpoint, {
            mobile: invoiceCheckMobile.value,
            admission_roll: invoiceCheckRoll.value
        })
        invoiceResult.value = res.data
    } catch (e) {
        invoiceCheckError.value = e?.response?.data?.message || 'Invoice not found or invalid details.'
    } finally {
        invoiceChecking.value = false
    }
}

const handleInvoicePay = async () => {
    if (!invoiceResult.value?.invoice_number) return
    invoicePaying.value = true
    try {
        let endpoint = ''
        if (invoiceCheckType.value === 'application') {
            endpoint = '/api/public/apply-fees/pay-existing'
        } else if (invoiceCheckType.value === 'certificate') {
            endpoint = '/api/public/certificate/pay-existing'
        }

        const res = await window.axios.post(endpoint, {
            invoice_number: invoiceResult.value.invoice_number
        })
        if (res.data?.gateway_url) {
            window.location.href = res.data.gateway_url
        } else {
            alert('Payment gateway URL not returned. Please try again.')
        }
    } catch (e) {
        alert(e?.response?.data?.message || 'Failed to initiate payment.')
    } finally {
        invoicePaying.value = false
    }
}
</script>