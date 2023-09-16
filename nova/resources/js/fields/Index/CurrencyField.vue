<template>
  <div>
    <template v-if="hasValue">
      <div v-if="field.asHtml" @click.stop v-html="fieldValue"></div>
      <span v-else>{{ fieldValue }}</span>
    </template>
    <p v-else>&mdash;</p>
  </div>
</template>

<script>
export default {
  props: ['resourceName', 'field'],

  computed: {
    /**
     * Determine if the field has a value other than null.
     */
    hasValue() {
      return this.field.value !== null
    },

    fieldValue() {
      return String(this.field.displayedAs || this.field.value)
    },
  },
}
</script>
