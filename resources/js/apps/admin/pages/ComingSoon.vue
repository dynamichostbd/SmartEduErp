<template>
    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <div class="text-sm font-semibold text-slate-900">{{ title }}</div>
        <div class="mt-1 text-sm text-slate-600">This module is being migrated to the new ERP.</div>
    </div>
</template>

<script>
export default {
    name: 'ComingSoon',
    props: {
        title: {
            type: String,
            default: 'Coming Soon',
        },
    },
}
</script>
