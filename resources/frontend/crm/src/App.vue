<template>
  <div id="app">
    <router-view v-if="show"/>
  </div>
</template>

<script>
  import { getToken } from '@/utils/auth'

  export default{
    name: 'App',
    data() {
        return {
          show: true
        }
    },

    mounted () {
      this.check(this.$route.meta.logged)
    },

    methods: {
      check(status) {
        this.show = false
        if (!status) {
          this.show = true
        } else if (status && getToken()) {
          this.show = true
        } else {
          this.$router.push('/login')
          this.show = false
        }
      }
    },

    watch: {
      '$route.meta.logged' (val) {
        this.check(val)
      }
    }
  }
</script>
