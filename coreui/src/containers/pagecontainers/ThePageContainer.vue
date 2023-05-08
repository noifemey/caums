<template>
  <div class="c-app">
    <!-- <TheSidebar/> -->
    <CWrapper>
      <CHeader fixed with-subheader light>
        <CHeaderBrand class="mx-auto d-lg-none" to="/">
          <!-- <CIcon name="logo" height="48" alt="Logo"/> -->
          <h6>CAUMS</h6>
        </CHeaderBrand>

        <CHeaderNav class="d-md-down-none mr-auto">
          <CHeaderNavItem key="dashboard">
            <CHeaderNavLink href="#/dashboard">
              <strong>Dashboard</strong> 
            </CHeaderNavLink>
          </CHeaderNavItem>

          <CDropdown color="link" :caret="false">
          <template #toggler-content>
            <a class="c-header-nav-link">
              <strong>Reports</strong>
            </a>
          </template>
          <CDropdownItem key="allocation-summary" href="#/reports/allocation-summary" >
            Summary List of N.C.A and N.T.A Received
          </CDropdownItem>
          <CDropdownItem key="allocation-status" href="#/reports/allocation-status" >
            Status of Cash Allocations, Utilized and Balances
          </CDropdownItem>
          <CDropdownItem key="pap" href="#/reports/pap" >
            Report of Programs, Activity and Project
          </CDropdownItem>
          <CDropdownItem key="rci" href="#/reports/rci" >
            Report of Checks Issued
          </CDropdownItem>
          <CDropdownItem key="cdr" href="#/reports/cdr" >
            Check Disbursement Record
          </CDropdownItem>
        </CDropdown>
          
        </CHeaderNav>

        <CHeaderNav class="mr-4">
          <CHeaderNavItem class="d-md-down-none mx-2">
            <CHeaderNavLink  href="#/login">
              <CIcon name="cil-bell"/>Login
            </CHeaderNavLink>
          </CHeaderNavItem>
        </CHeaderNav>
      </CHeader>
      <div class="c-body">
        <main class="c-main">
          <CContainer fluid>
            <transition name="fade">
              <router-view></router-view>
            </transition>
          </CContainer>
        </main>
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
      </div>
    </CWrapper>
  </div>
</template>

<script>
export default {
  name: 'ThePageContainer'
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
