<template>
    <InvoiceIndex :systems="systems" mode="accountHeadWise" />
</template>

<script>
import InvoiceIndex from './InvoiceIndex.vue'

export default {
    name: 'InvoiceAccountHeadWisePayment',
    components: { InvoiceIndex },
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
}
</script>
