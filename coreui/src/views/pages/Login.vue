<template>
  <!-- <CContainer class="d-flex content-center min-vh-100"> -->
  <CContainer class="d-flex content-center">
    <CRow>
      <CCol>
        <img src="img/brand/caums_logo_login.png" class="c-avatar-img "/>
        <!-- <h1>Cash Allocation and Utilization Maintenance System</h1>
        <h4>C.A.U.M.S</h4> -->
        <CCardGroup>
          <CCard class="p-4">
            <CCardBody>
              <CForm @submit.prevent="login" method="POST">
                <h1>Login</h1>
                <p class="text-muted">Sign In to your account</p>
                <CInput
                  v-model="username"
                  prependHtml="<i class='cui-user'></i>"
                  placeholder="Username"
                  autocomplete="username"
                >
                  <template #prepend-content><CIcon name="cil-user"/></template>
                </CInput>
                <CInput
                  v-model="password"
                  prependHtml="<i class='cui-lock-locked'></i>"
                  placeholder="Password"
                  type="password"
                  autocomplete="curent-password"
                >
                  <template #prepend-content><CIcon name="cil-lock-locked"/></template>
                </CInput>
                <CRow>
                  <CCol col="12" class="justify-content-md-center">
                    <CButton type="submit" color="primary" class="btn-block px-4">Login</CButton>
                  </CCol>
                  <!-- <CCol col="6" class="text-right">
                    <CButton color="link" class="px-0">Forgot password?</CButton>
                  </CCol> -->
                </CRow>
              </CForm>
            </CCardBody>
          </CCard>
        </CCardGroup>
      </CCol>
    </CRow>
  </CContainer>
</template>

<script>

import axios from "axios";

    export default {
      name: 'Login',
      data() {
        return {
          username: '',
          password: '',
          showMessage: false,
          message: '',
        }
      },
      methods: {
        goRegister(){
          this.$router.push({ path: 'register' });
        },
        login() {
          let self = this;
          axios.post(  this.$apiAdress + '/api/login', {
            username: self.username,
            password: self.password,
          }).then(function (response) {
            self.username = '';
            self.password = '';
            localStorage.setItem("api_token", response.data.access_token);
            localStorage.setItem('roles', response.data.roles);
            self.$router.push({ path: 'dashboard' });
          })
          .catch(function (error) {
            self.message = 'Incorrect E-mail or password';
            self.showMessage = true;
            self.$swal.fire({
              icon: 'error',
              title: 'Action Failed!!!',
              text: 'Incorrect E-mail or password.',
            })
            console.log(error);
          });
  
        }
      }
    }

</script>
