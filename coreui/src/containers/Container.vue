<template>
  <div v-if = "logged_in">
    <TheContainer/>
  </div>
  <div v-else>
    <ThePageContainer/>
  </div>
</template>

<script>
import TheContainer from './TheContainer'
import ThePageContainer from './pagecontainers/ThePageContainer'

export default {
  name: 'Container',
  components: {
    TheContainer,
    ThePageContainer
  },
  data: () => {
    return {
      logged_in : localStorage.getItem("roles")
    }
  }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter,
.fade-leave-to {
  opacity: 0;
}
</style>
