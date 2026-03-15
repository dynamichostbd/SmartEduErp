<template>
    <InvoiceIndex :systems="systems" mode="accountWise" />
</template>

<script>
import InvoiceIndex from './InvoiceIndex.vue'

export default {
    name: 'InvoiceAccountWisePayment',
    components: { InvoiceIndex },
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
}
</script>
