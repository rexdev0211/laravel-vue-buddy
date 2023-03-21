<template>
  <div class="box">
    <div class="static-page-content" v-html="page.content"></div>
  </div>
</template>

<script>
export default {
  props: ['page'],
}
</script>