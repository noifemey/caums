<template>
  <CRow>
    <CCol col="12" xl="12">
      <transition name="slide">
      <CCard>
        <CCardHeader>
            <h4>Fund Account Entry</h4>
        </CCardHeader>
        <CCardBody>
          <div class="row">
            <div class="col-3">
              <button type="button" class="btn btn-dark btn-lg btn-block" @click = "modalCreateShow"> Create</button>
            </div>
            <div class="col-3">
              <button type="button" class="btn btn-dark btn-lg btn-block" @click = "generateReport"> Export </button>
            </div>
          </div>

          <v-client-table :data="accountstable.items" :columns="accountstable.columns" :options="accountstable.options">
            <template slot="account_number" slot-scope="props">
                {{props.row.AccountNumber}}
            </template>
            <template slot="account_name" slot-scope="props">
                {{props.row.AccountName}}
            </template>
            <template slot="bank_name" slot-scope="props">
                {{props.row.BankName}}
            </template>
            <template slot="show" slot-scope="props">
                <CButton color="primary" @click="showAccount(props.row)">Show</CButton>
            </template>
            <template slot="edit" slot-scope="props">
                <CButton color="primary" @click = "modalEditShow(props.row)">Edit</CButton>
            </template>
            <template slot="delete" slot-scope="props">
                <CButton color="danger" @click="deleteAccount( props.row.id )">Delete</CButton>
            </template>
          </v-client-table>

                
          <create-accounts
            modalType="create"
            title = "Create Accounts"
            @updated = "updatedfn"
          >
          </create-accounts>

          <create-accounts
            modalType="edit"
            title = "Edit Accounts"
            @updated = "updatedfn"
          >
          </create-accounts>

          <div>
          <vue-html2pdf
              :show-layout="false"
              :float-layout="true"
              :enable-download="false"
              :preview-modal="true"
              :paginate-elements-by-height="1100"
              filename="List_of_accounts"
              :pdf-quality="2"
              :manual-pagination="false"
              pdf-format="a4"
              pdf-orientation="portrait"
              pdf-content-width="800px"
              :html-to-pdf-options = "htmlToPdfOptions"
      
              @progress="onProgress($event)"
              @hasStartedGeneration="hasStartedGeneration()"
              @hasGenerated="hasGenerated($event)"
              @beforeDownload="beforeDownload($event)"
              ref="html2Pdf"
          >
              <section slot="pdf-content">
                  <!-- PDF Content Here -->
                  
                  <strong>Fund Accounts List</strong>
                    
                  <table class="table">
                    <thead class="thead-default">
                      <tr>
                        <th>#</th>
                        <th>Account Number</th>
                        <th>Account Name</th>
                        <th>Bank Name</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(account,i) in accountstable.items" :key="i">
                        <th scope="row">{{i + 1}}</th>
                        <td>{{account.AccountNumber}}</td>
                        <td>{{account.AccountName}}</td>
                        <td>{{account.BankName}}</td>
                      </tr>
                    </tbody>
                  </table>
              </section>
          </vue-html2pdf>
        </div>

        </CCardBody>
      </CCard>
      </transition>
    </CCol>
  </CRow>
</template>

<script>
import axios from 'axios'
import { ServerTable, ClientTable, Event } from "vue-tables-2";
import Vue from "vue";
Vue.use(ClientTable);
Vue.use(ServerTable);

import VueHtml2pdf from 'vue-html2pdf'

import CreateAccounts from './CreateAccounts.vue';

export default {
  name: 'Users',
  components: {
    VueHtml2pdf,
    'create-accounts': CreateAccounts
  },
  data: () => {
    return {
      you: null,
      editValue : {
        warningModal: true,
				account_id: 0,
				account_number: "0000-0000-00",
				account_name: "",
				bank_name: ""
      },
      accountstable : {
        columns: ['account_number', 'account_name', 'bank_name', 'show', 'edit', 'delete'],
        items: [],
        options: {
            filterable: ['AccountNumber', 'AccountName', "BankName"],
        }
      },
      htmlToPdfOptions: {
          margin: 1.5
      }
    }
  },
  methods: {
    modalCreateShow(){
      console.log("Create Show");
      
      this.editValue.warningModal = true;
      this.editValue.account_id = 0;
      this.editValue.account_number = "0000-0000-00";
      this.editValue.account_name = "";
      this.editValue.bank_name = "";

      this.$emit('create', this.editValue);
    },
    modalEditShow(props){
      console.log("Edit Show");
      this.editValue.warningModal = true;
      this.editValue.account_id = props.id;
      this.editValue.account_number = props.AccountNumber;
      this.editValue.account_name = props.AccountName;
      this.editValue.bank_name = props.BankName;

      this.$emit('edit', this.editValue);
    },
    showAccount(props) {
      this.$swal.fire({
      title: '<strong>FUND ACCOUNT DETAILS</strong>',
      icon: 'info',
      html:
        '<strong>Account Number: </strong>' +  props.AccountNumber + "<br>" +
        '<strong>Account Name: </strong>' +  props.AccountName + "<br>" +
        '<strong>Bank Name: </strong>' +  props.BankName ,
        showCloseButton: true,
        showCancelButton: true,
        focusConfirm: false
      })
    },
    deleteAccount ( id ) {
      let self = this;
      this.$swal.fire({
        title: 'Are you Sure you want to delete this account?',
        showDenyButton: false,
        showCancelButton: true,
        confirmButtonText: `Delete`,
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
          axios.get(  this.$apiAdress + '/api/accounts/delete/' + id + '?token=' + localStorage.getItem("api_token"))
          .then(function (response) {
              self.$swal.fire({
                position: 'top-end',
                icon: 'success',
                title: 'Successfully deleted data.',
                showConfirmButton: false,
                timer: 1500
              })
              self.getAccounts();
          }).catch(function (error) {
            console.log(error);
            self.$swal.fire({
              icon: 'error',
              title: 'Action Failed!!!',
              text: 'There is something wrong.!',
              footer: '<a href="">Please contact your administrator.</a>'
            })
          });
        } else if (result.isDenied) {
          Swal.fire('Changes are not saved', '', 'info')
        }
      })

    },
    getAccounts (){
      let self = this;
      axios.get(  this.$apiAdress + '/api/accounts?token=' + localStorage.getItem("api_token"))
      .then(function (response) {
        console.log("Get Data Accounts");
        console.log(response.data.you);
        self.accountstable.items = response.data.accounts;
        self.you = response.data.you;
      }).catch(function (error) {
        console.log(error);
        if(error.response.status == 401){
        	self.$router.push({ path: '/login' });
        }else{
          alert(error);
        }
      });
    },
    updatedfn (){
      this.getAccounts();
    },
    generateReport () {
      let self = this;
        //this.$refs.html2Pdf.generatePdf()
        axios.get(  this.$apiAdress + '/api/accounts/generate-pdf/?token=' + localStorage.getItem("api_token"), {
            responseType: 'blob', // had to add this one here
        })
          .then(function (response) {
              console.log(response);
              var fileURL = window.URL.createObjectURL(new Blob([response.data]));
              var fileLink = document.createElement('a');

              fileLink.href = fileURL;
              fileLink.setAttribute('download', 'file.pdf');
              document.body.appendChild(fileLink);

              fileLink.click();

              self.$swal.fire({
                position: 'top-end',
                icon: 'success',
                title: 'Successfully exported the data.',
                showConfirmButton: false,
                timer: 1500
              })
              //self.getAccounts();
          }).catch(function (error) {
            console.log(error);
            //alert('Action Failed!!! There is something wrong.');
            self.$swal.fire({
              icon: 'error',
              title: 'Action Failed!!!',
              text: 'There is something wrong.!',
              footer: '<a href="">Please contact your administrator.</a>'
            })
          });
    },
    onProgress(){
      console.log("on progress");

    },
    hasStartedGeneration(){
      console.log("Started Generation");
    },
    hasGenerated($event){
      console.log("has Generated");
    },
    async beforeDownload ({ html2pdf, options, pdfContent }) {
        await html2pdf().set(options).from(pdfContent).toPdf().get('pdf').then((pdf) => {
            const totalPages = pdf.internal.getNumberOfPages()
            for (let i = 1; i <= totalPages; i++) {
                pdf.setPage(i)
                pdf.setFontSize(10)
                pdf.setTextColor(150)
                pdf.text('Page ' + i + ' of ' + totalPages, (pdf.internal.pageSize.getWidth() * 0.88), (pdf.internal.pageSize.getHeight() - 0.3))
            } 
        }).save()
    }
  },
  mounted: function(){
    this.getAccounts();
  }
}
</script>
