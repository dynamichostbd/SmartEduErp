<template>
    <div class="min-h-screen bg-white text-slate-900">
        <div class="border-b">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3">
                <div class="text-sm font-semibold">{{ appName }}</div>
                <div class="text-xs text-slate-600">Frontend</div>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 py-6">
            <div class="rounded-lg border p-4">
                Frontend app mounted.
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'FrontendApp',
    data() {
        return {
            appName: window.laravel?.app_name ?? 'ERP',
        }
    },
}
</script>
