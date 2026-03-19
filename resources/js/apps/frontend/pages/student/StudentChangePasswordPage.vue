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
                        <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 text-sm font-extrabold text-slate-800">CHANGE PASSWORD</div>
                        <div class="p-4">
                            <div v-if="error" class="mb-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-800">{{ error }}</div>
                            <div v-if="success" class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">{{ success }}</div>

                            <form class="mx-auto w-full max-w-2xl" @submit.prevent="submit">
                                <div class="grid grid-cols-1 gap-4">
                                    <div class="grid grid-cols-1 items-center gap-2 sm:grid-cols-3">
                                        <div class="text-sm font-semibold text-slate-700">Old Password <span class="text-red-600">*</span></div>
                                        <div class="sm:col-span-2">
                                            <input v-model="form.old_password" type="password" class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 text-sm outline-none" placeholder="Old Password" @blur="checkOld" required />
                                            <div v-if="oldChecked && !oldOk" class="mt-1 text-xs font-semibold text-red-600">Old password do not match our records!!</div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 items-center gap-2 sm:grid-cols-3">
                                        <div class="text-sm font-semibold text-slate-700">New Password <span class="text-red-600">*</span></div>
                                        <div class="sm:col-span-2">
                                            <input v-model="form.new_password" type="password" class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 text-sm outline-none" placeholder="New Password" required />
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 items-center gap-2 sm:grid-cols-3">
                                        <div class="text-sm font-semibold text-slate-700">Confirm Password <span class="text-red-600">*</span></div>
                                        <div class="sm:col-span-2">
                                            <input v-model="form.confirm_password" type="password" class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 text-sm outline-none" placeholder="Confirm Password" required />
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-8 text-center">
                                    <button type="submit" class="rounded-md bg-[#0b1d4d] px-10 py-2.5 text-sm font-extrabold text-white hover:bg-[#09163c]" :disabled="submitting">
                                        {{ submitting ? 'Processing...' : 'Change Password' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { inject, reactive, ref } from 'vue'
import StudentSidebar from '../../components/student/StudentSidebar.vue'

const app = inject('app')

const submitting = ref(false)
const error = ref('')
const success = ref('')

const oldChecked = ref(false)
const oldOk = ref(true)

const form = reactive({
    old_password: '',
    new_password: '',
    confirm_password: '',
})

async function checkOld() {
    oldChecked.value = false
    oldOk.value = true
    if (!form.old_password || form.old_password.length < 1) return
    try {
        const res = await window.axios.post('/api/public/student/change-password/check-old-password', { old_password: form.old_password })
        oldChecked.value = true
        oldOk.value = !!res?.data
    } catch {
        oldChecked.value = true
        oldOk.value = false
    }
}

async function submit() {
    error.value = ''
    success.value = ''

    if (!form.old_password) {
        error.value = 'Old password is required'
        return
    }
    if (!form.new_password || form.new_password.length < 6) {
        error.value = 'New password is required (min 6)'
        return
    }
    if (!form.confirm_password || form.confirm_password.length < 6) {
        error.value = 'Password confirmation is required'
        return
    }
    if (form.new_password !== form.confirm_password) {
        error.value = 'Password confirmation does not match'
        return
    }

    submitting.value = true
    try {
        const res = await window.axios.post('/api/public/student/change-password', {
            old_password: form.old_password,
            new_password: form.new_password,
            confirm_password: form.confirm_password,
        })
        success.value = res?.data?.message || 'Password change successfully!!'
        setTimeout(() => {
            app?.go?.('/login')
        }, 400)
    } catch (e) {
        error.value = e?.response?.data?.message || 'Something went wrong'
    } finally {
        submitting.value = false
    }
}
</script>
