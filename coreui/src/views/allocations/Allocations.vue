<template>
  <CRow>
    <CCol col="12" xl="12">
      <transition name="slide">
      <CCard>
        <CCardHeader>
            <h4>Allocations Data Entry</h4>
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

          <v-client-table :data="table.items" :columns="table.columns" :options="table.options">
            <template slot="allocation_number" slot-scope="props">
                {{props.row.AllocationNo}}
            </template>
            <template slot="period_coverage" slot-scope="props">
                {{format_date(props.row.MonthYear,"yyyy-MM-DD")}}
            </template>
            <template slot="account_number" slot-scope="props">
                {{props.row.AccountNo}}
            </template>            
            <template slot="date_issued" slot-scope="props">
                {{format_date(props.row.Date,"yyyy-MM-DD")}}
            </template>
            <template slot="reference" slot-scope="props">
                {{props.row.Reference}}
            </template>
            <template slot="purpose" slot-scope="props">
                {{props.row.Purpose}}
            </template>
            <template slot="amount_issued" slot-scope="props">
                {{props.row.CAIssued}}
            </template>
            <template slot="amount_recieved" slot-scope="props">
                {{props.row.CAReceived}}
            </template>
            <template slot="show" slot-scope="props">
                <CButton color="primary" @click="showAllocation(props.row)">Show</CButton>
            </template>
            <template slot="edit" slot-scope="props">
                <CButton color="primary" @click = "modalEditShow(props.row)">Edit</CButton>
            </template>
            <template slot="delete" slot-scope="props">
                <CButton color="danger" @click="deleteAllocation( props.row.id )">Delete</CButton>
            </template>
          </v-client-table>

          <div v-if = "accountList.length > 0">
            <create-allocation
              modalType="create"
              title = "Create Allocation"
              @updated = "getAllocations"
              :accountList = "accountList"
            >
            </create-allocation>

            <create-allocation
              modalType="edit"
              title = "Edit Allocation"
              @updated = "getAllocations"
              :accountList = "accountList"
            >
            </create-allocation>
          </div>

        </CCardBody>
      </CCard>
      </transition>
    </CCol>
  </CRow>
</template>

<script>
import axios from 'axios'
import moment from 'moment'

import CreateAllocation from './CreateAllocation.vue';

export default {
  name: 'Users',
  components: {
    'create-allocation': CreateAllocation
  },
  data: () => {
    return {
      accountList:[],
      you: null,
      editValue : {
        warningModal: true,
				allocation_id: 0,
				allocation_number: "0000-0000-00",
				period_coverage: "",
				account_number: "",
				date_issued: "",
				reference: "",
				purpose : "",
				amount_issued: "",
				amount_recieved: ""
      },
      table : {
        columns: ['allocation_number', 'period_coverage', 'account_number', 'date_issued', 'reference', 'purpose', 'amount_issued', 'amount_recieved', 'show', 'edit', 'delete'],
        items: [],
        options: {
            filterable: ['AllocationNo', 'reference', "purpose", 'AccountNo'],
        }
      }
    }
  },
  methods: {
    format_date(value,format){
      if (value) {
      return moment(String(value)).format(format);
      }
    },
    modalCreateShow(){
      console.log("Create Show");
      
      this.editValue.warningModal = true;
      this.editValue.allocation_id = 0;
      this.editValue.allocation_number = "0000-0000-00";
      this.editValue.period_coverage = "";
      this.editValue.account_number = "";
      this.editValue.date_issued = "";
      this.editValue.reference = "";
      this.editValue.purpose = "";
      this.editValue.amount_issued = "";
      this.editValue.amount_recieved = "";

      this.$emit('create', this.editValue);
    },
    modalEditShow(props){
      console.log("Edit Show");
      this.editValue.warningModal = true;
      this.editValue.allocation_id = props.id;
      this.editValue.allocation_number = props.AllocationNo;
      this.editValue.period_coverage = props.MonthYear;
      this.editValue.account_number = props.AccountNo;
      this.editValue.date_issued = props.Date;
      this.editValue.reference = props.Reference;
      this.editValue.purpose = props.Purpose;
      this.editValue.amount_issued = props.CAIssued;
      this.editValue.amount_recieved = props.CAReceived;

      this.$emit('edit', this.editValue);
    },
    showAllocation(props) {
      this.$swal.fire({
      title: '<strong>Allocation DETAILS</strong>',
      icon: 'info',
      html:
        '<strong>Allocation Number: </strong>' +  props.AllocationNo + "<br>" +
        '<strong>Period Coverage: </strong>' +  props.MonthYear + "<br>" +
        '<strong>Account Number: </strong>' +  props.AccountNo + "<br>" + 
        '<strong>Date Issued: </strong>' +  props.Date + "<br>" + 
        '<strong>Reference: </strong>' +  props.Reference + "<br>" + 
        '<strong>Purpose: </strong>' +  props.Purpose + "<br>" + 
        '<strong>Amount Issued: </strong>' +  props.CAIssued + "<br>" + 
        '<strong>Amount Received: </strong>' +  props.CAReceived ,
        showCloseButton: true,
        showCancelButton: true,
        focusConfirm: false
      })
    },
    deleteAllocation ( id ) {
      let self = this;
      this.$swal.fire({
        title: 'Are you Sure you want to delete this record?',
        showDenyButton: false,
        showCancelButton: true,
        confirmButtonText: `Delete`,
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
          axios.get(  this.$apiAdress + '/api/allocations/delete/' + id + '?token=' + localStorage.getItem("api_token"))
          .then(function (response) {
              self.$swal.fire({
                position: 'top-end',
                icon: 'success',
                title: 'Successfully deleted user.',
                showConfirmButton: false,
                timer: 1500
              })
              self.getAllocations();
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
    getAllocations (){
      let self = this;
      self.$swal.fire({
        title: 'Please Wait !',
        html: 'Loading..',// add html attribute if you want or remove
        allowOutsideClick: false,
        showConfirmButton:false,
        onBeforeOpen: () => {
            self.$swal.showLoading()
        },
      });
      axios.get(  this.$apiAdress + '/api/allocations?token=' + localStorage.getItem("api_token"))
      .then(function (response) {
        self.table.items = response.data.allocations;
        self.you = response.data.you;
        self.accountList    = response.data.accounts;
        self.$swal.close();
      }).catch(function (error) {
        console.log(error);
        self.$swal.close();
        if(error.response.status == 401){
        	self.$router.push({ path: '/login' });
        }else{
          alert(error);
        }
      });
    },
    generateReport () {
      let self = this;
      self.$swal.fire({
        title: 'Please Wait !',
        html: 'Downloading...',// add html attribute if you want or remove
        allowOutsideClick: false,
        showConfirmButton:false,
        onBeforeOpen: () => {
            self.$swal.showLoading()
        },
      });
      axios.get(  this.$apiAdress + '/api/allocations/generate-pdf/?token=' + localStorage.getItem("api_token"), {
          responseType: 'blob', // had to add this one here
      })
      .then(function (response) {
          console.log(response);
          var fileURL = window.URL.createObjectURL(new Blob([response.data]));
          var fileLink = document.createElement('a');

          fileLink.href = fileURL;
          fileLink.setAttribute('download', 'allocations.pdf');
          document.body.appendChild(fileLink);

          fileLink.click();

          self.$swal.close();
      }).catch(function (error) {
        console.log(error);
        
        self.$swal.close();
        self.$swal.fire({
          icon: 'error',
          title: 'Action Failed!!!',
          text: 'There is something wrong.!',
          footer: '<a href="">Please contact your administrator.</a>'
        })
      });
    }
  },
  mounted: function(){
    this.getAllocations();
  }
}
</script>
