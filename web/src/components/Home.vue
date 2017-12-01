<template>
  <div class="main main-fixed">
    <left-aside></left-aside>
    <router-view></router-view>
  </div>
</template>
<script>
  import LeftAside from '@/components/LeftAside.vue'
  export default {
    name: 'Home',
    data () {
      return {}
    },
    created () {
      if (this.$route.name == 'home') {
        this.$router.push('/upload')
      }
    },
    components: {
      LeftAside
    }
  }
</script>