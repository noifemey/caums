<template>
  <CRow>
    <CCol col="12" xl="12">
      <transition name="slide">
      <CCard>
        <CCardHeader>
            <h4>Report of Programs, Activity and Project</h4>
        </CCardHeader>
        <CCardBody>
          <form @submit.prevent="getData">   
          <div class="row">
            <div class="col-4">
              <select-account 
										v-model="account_number"
										:default-value="dataAccount"
									>
							</select-account>
              <!-- <button type="button" class="btn btn-dark btn-lg btn-block" @click = "modalCreateShow"> Create</button> -->
            </div>
            <div class="col-4">
              <div class="form-group">
                <label>Date</label>
                <!-- <input class="form-control" type="text" v-model = "year"> -->
                  <VueCtkDateTimePicker 
                  v-model="date"
                  :range="true"
                  format="YYYY-MM-DD"
                  formatted="ll"
                  color="#41b883"
                  button-color="#007bff"
                  :no-label="true"
                  label=" " />
              </div>
            </div>
            <div class="col-4">
              <select-pap 
                    v-model="pap_code"
                    :default-value="dataAccount"
                  >
              </select-pap>
              <!-- <button type="button" class="btn btn-dark btn-lg btn-block" @click = "modalCreateShow"> Create</button> -->
            </div>
            <div class="col-6">
              <label></label>
              <button type="submit" class="btn btn-success btn-lg btn-block"> Search </button>
            </div>
            <div class="col-6">
              <label></label>
              <button type="button" class="btn btn-primary btn-lg btn-block" @click = "exportPAPReport"> Export </button>
            </div>
          </div>
          </form>

          <v-client-table :data="table.items" :columns="table.columns" :options="table.options">
              <template slot="CkAmount" slot-scope="props">
                <strong> ₱ {{Number(props.row.CkAmount).toLocaleString()}} </strong>
              </template>
              <template slot="DateIssued" slot-scope="props">
                <strong> {{format_date(props.row.DateIssued)}} </strong>
              </template>
              <template slot="CenterCode">
              </template>
          </v-client-table>

          <table class="table table-striped">
              <tr>
                <td colspan = "2" align = "right"><h3><strong>Grand Total</strong></h3></td>
                <td align = "right"><h3><strong> ₱ {{ Number(grandTotal).toLocaleString() }}</strong></h3></td>
              </tr>
          </table>
        </CCardBody>
      </CCard>
      </transition>
    </CCol>
  </CRow>
</template>

<script>
import axios from 'axios'
import moment from 'moment'
import SelectAccount from '../list/Accounts.vue'
import SelectPAP from '../list/PapCodes.vue'

export default {
  name: 'Users',
  components: {
    'select-account': SelectAccount,
    'select-pap': SelectPAP,
  },
  data: () => {
    return {
      account_number: "",
      pap_code: "",
      year:"",  
      date : "",
      date_start : "",
      date_end : "",
      dataAccount: "",
      grandTotal:"",
      table : {
        columns: ['CheckNo', 'DateIssued','Payee','Specifications','Object','PPA','Amount','Allocation'],
        items: [],
        options: {
            headings: {
              CheckNo: 'Check Number',
              DateIssued: 'Date Issued',
              Payee: 'Payee',
              Specifications: 'Specifications',
              Object: 'Object',
              PPA: 'PPA',
              Amount: 'Amount',
              Allocation: 'Allocation'
            },                    
            rowClassCallback: function(row) { 
                return (row.month_name == "Grand Total") ?'bg-warning':'';
            },
            filterable: ['DateIssued','CheckNo','PPA','Payee','Object','Allocation'],
        }
      }
    }
  },
  methods: {      
    format_date(value){
        if (value) {
          return moment(String(value)).format('DD-MMM-YYYY');
        }
    },
    getData (){
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
      if(this.year == ""){
        this.year = new Date().getFullYear();
      }
      axios.get(  this.$apiAdress + '/api/reports/get-pap?token=' + localStorage.getItem("api_token"),
        { params : {
          account_number: self.account_number.AccountNumber,
          pap_code: self.pap_code.PAPCode,
          date_start: self.date_start,
          date_end: self.date_end,
        }
      }).then(function (response) {
        self.table.items    = response.data.data;
        self.grandTotal = response.data.grandTotal;
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
    exportPAPReport(){
      var params = {
          account_number: this.account_number.AccountNumber,
          pap_code: this.pap_code.PAPCode,
          date_start: this.date_start,
          date_end: this.date_end,
      }
      
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

      axios.post(  
        this.$apiAdress + '/api/reports/export-pap?token=' + localStorage.getItem("api_token"),
          params, {
          responseType: 'blob'
        }).then(function (response) {
          const link = document.createElement('a');
          link.href = window.URL.createObjectURL(
                new Blob([response.data])
            );
          var today = new Date();
          link.setAttribute('download', 'PAP-Report_' + today +'.xlsx');
          document.body.appendChild(link);
          // Make the magic happen!
          link.click();
          
          self.$swal.close();
      }).catch(function (error) {
        console.log(error);
        //self.$router.push({ path: '/login' });
        
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
    //this.getData();
  },
  watch: {
    date(){
      this.date_start = this.date.start;
      this.date_end = this.date.end;
    },
  },
}
</script>
