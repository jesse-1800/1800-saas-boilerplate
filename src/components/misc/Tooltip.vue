<template>
    <v-tooltip :location="location ?? 'bottom center'">
        <template v-slot:activator="{ props }">
            <div v-bind="props">
                <slot></slot>
            </div>
        </template>
        <slot v-if="$slots.text" name="text"/>
        <div v-else v-html="text"></div>
    </v-tooltip>
</template>
<script>
export default {
    props: ['text','location'],
}
</script>
