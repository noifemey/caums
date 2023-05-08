<template>
  <CRow>
    <CCol col="12" xl="12">
      <transition name="slide">
      <CCard>
        <CCardHeader>
            <h4>Check Disbursement Record</h4>
        </CCardHeader>
        <CCardBody>
          <form @submit.prevent="getData">   
          <div class="row">
            <div class="col-5">
              <select-account 
										v-model="account_number"
										:default-value="dataAccount"
									>
							</select-account>
              <!-- <button type="button" class="btn btn-dark btn-lg btn-block" @click = "modalCreateShow"> Create</button> -->
            </div>
            <div class="col-3">
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
            <div class="col-2">
              <label></label>
              <button type="submit" class="btn btn-success btn-lg btn-block"> Search </button>
            </div>
            <div class="col-2">
              <label></label>
              <button type="button" class="btn btn-primary btn-lg btn-block" @click = "exportCDR"> Export </button>
            </div>
          </div>
          </form>

          <v-client-table :data="table.items" :columns="table.columns" :options="table.options">
              <template slot="total" slot-scope="props">
                <strong> ₱ {{Number(props.row.total).toLocaleString()}} </strong>
              </template>
              <template slot="dateIssued" slot-scope="props">
                <strong> {{format_date(props.row.dateIssued)}} </strong>
              </template>
              <template slot="child_row" slot-scope="props">
                <div v-if = "props.row.children.length > 0">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th>Check Number</th>
                        <th>Voucher Number</th>
                        <th>Obligation Number</th>
                        <th>Allocation Number</th>
                        <th>Reference</th>
                        <th>Description</th>
                        <th>Object</th>
                        <th>Payee</th>
                        <th>Purpose</th>
                        <th>Amount</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(data,i) in props.row.children" :key="i">
                        <th>{{ data.checkNo  }}</th>  
                        <td>No. {{ data.voucherNo }}</td> 
                        <td>{{ data.obligationNo }}</td>  
                        <td>{{ data.allocationNo }}</td>  
                        <td>{{ data.reference }}</td>  
                        <td>{{ data.description }}</td>  
                        <td>{{ data.object }}</td>  
                        <td>{{ data.payee }}</td>  
                        <td>{{ data.purpose }}</td>  
                        <td> ₱ {{Number(data.amount).toLocaleString() }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
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

export default {
  name: 'Users',
  components: {
    'select-account': SelectAccount,
  },
  data: () => {
    return {
      account_number: "",
      year:"",  
      date : "",
      date_start : "",
      date_end : "",
      dataAccount: "",
      grandTotal:"",
      table : {
        columns: ['dateIssued', 'total'],
        items: [],
        options: {
            headings: {
              DateIssued: 'Check Date',
              CheckNo: 'Check Number',
            },                    
            rowClassCallback: function(row) { 
                return (row.month_name == "Grand Total") ?'bg-warning':'';
            },
            filterable: ['dateIssued', 'total'],
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
      axios.get(  this.$apiAdress + '/api/reports/get-cdr?token=' + localStorage.getItem("api_token"),
        { params : {
          account_number: self.account_number.AccountNumber,
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
    exportCDR(){
      var params = {          
          account_number: this.account_number.AccountNumber,
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
        this.$apiAdress + '/api/reports/export-cdr?token=' + localStorage.getItem("api_token"),
          params, {
          responseType: 'blob'
        }).then(function (response) {
          const link = document.createElement('a');
          link.href = window.URL.createObjectURL(
                new Blob([response.data])
            );
          var today = new Date();
          link.setAttribute('download', 'CDR-Report_' + today +'.xlsx');
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
