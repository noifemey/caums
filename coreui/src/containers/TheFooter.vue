<template>
  <CFooter :fixed="false">
    <div>
      <a href="https://car.dswd.gov.ph/" target="_blank">DSWD CAR</a>
      <span class="ml-1">&copy; {{new Date().getFullYear()}} RICTMS</span>
    </div>
    <div class="ml-auto">
      <span class="mr-1">Powered by</span>
      <a href="https://car.dswd.gov.ph/" target="_blank">DSWD RICTMS</a>
    </div>
  </CFooter>
</template>

<script>
export default {
  name: 'TheFooter'
}
</script>
