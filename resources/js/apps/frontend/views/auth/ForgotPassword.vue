<template>
<div class="mt-3 rounded-sm border border-slate-300 bg-white p-4 sm:p-6">
    <div class="mx-auto w-full max-w-xl">
        <div class="text-center text-xl font-extrabold text-slate-900 sm:text-2xl">FORGOT PASSWORD</div>
        <div class="mx-auto mt-3 h-[2px] w-full max-w-md bg-red-500"></div>

        <div class="mt-6 rounded-sm border border-slate-300 bg-slate-50 p-4 text-sm text-slate-700">
            This feature will be implemented next.
        </div>

        <div class="mt-6 text-center">
            <button type="button" class="text-sm font-bold text-[#0d6b75] hover:underline" @click="app.go('/login')">Back to Login</button>
        </div>
    </div>
</div>
</template>

<script setup>
import { inject } from 'vue'

const app = inject('app')
</script>
