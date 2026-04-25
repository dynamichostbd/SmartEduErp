<template>
    <div class="max-w-md mx-auto rounded-sm border border-slate-300 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50/50 p-4">
            <h2 class="text-lg font-bold text-slate-800">Change Password</h2>
        </div>
        <form class="p-6 space-y-4" @submit.prevent="submit">
            <div v-if="error" class="rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                {{ error }}
            </div>
            <div v-if="success" class="rounded-sm border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">
                {{ success }}
            </div>

            <div class="space-y-1">
                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Old Password</label>
                <div class="relative">
                    <input v-model="form.old_password" :type="showOld ? 'text' : 'password'" class="w-full rounded-sm border border-slate-300 p-2 pr-10 text-sm outline-none focus:border-emerald-500" required />
                    <button type="button" class="absolute right-3 top-2 text-slate-400 hover:text-slate-600" @click="showOld = !showOld">
                        <svg v-if="showOld" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                    </button>
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">New Password</label>
                <input v-model="form.new_password" type="password" class="w-full rounded-sm border border-slate-300 p-2 text-sm outline-none focus:border-emerald-500" required minlength="6" />
            </div>

            <div class="space-y-1">
                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Confirm New Password</label>
                <input v-model="form.confirm_password" type="password" class="w-full rounded-sm border border-slate-300 p-2 text-sm outline-none focus:border-emerald-500" required minlength="6" />
            </div>

            <div class="pt-2">
                <button 
                    type="submit" 
                    class="w-full rounded-sm bg-slate-900 py-2.5 text-sm font-bold text-white hover:bg-emerald-600 transition-colors"
                    :disabled="loading"
                >
                    {{ loading ? 'Updating...' : 'Update Password' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue'

const form = ref({
    old_password: '',
    new_password: '',
    confirm_password: ''
})

const loading = ref(false)
const error = ref('')
const success = ref('')
const showOld = ref(false)

const submit = async () => {
    if (form.value.new_password !== form.value.confirm_password) {
        error.value = 'Passwords do not match.'
        return
    }
    
    loading.value = true
    error.value = ''
    success.value = ''
    
    try {
        const res = await window.axios.post('/api/public/student/change-password', form.value)
        success.value = res?.data?.message || 'Password changed successfully.'
        form.value = { old_password: '', new_password: '', confirm_password: '' }
    } catch (e) {
        error.value = e?.response?.data?.message || 'Failed to change password.'
    } finally {
        loading.value = false
    }
}
</script>
