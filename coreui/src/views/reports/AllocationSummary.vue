<template>
  <CRow>
    <CCol col="12" xl="12">
      <transition name="slide">
      <CCard>
        <CCardHeader>
             <h4>Summary List of N.C.A and N.T.A Received </h4>
        </CCardHeader>
        <CCardBody>
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
                <label>Year</label>
                <input class="form-control" type="text" v-model = "year">
              </div>
            </div>
            <div class="col-2">
              <label></label>
              <button type="button" class="btn btn-success btn-lg btn-block" @click = "getAllocations"> Search </button>
            </div>
            <div class="col-2">
              <label></label>
              <button type="button" class="btn btn-primary btn-lg btn-block" @click = "exportAllocations"> Export </button>
            </div>
          </div>

          <v-client-table :data="table.items" :columns="table.columns" :options="table.options">
              <template slot="received_summary" slot-scope="props">
                <strong> ₱ {{Number(props.row.received_summary).toLocaleString()}} </strong>
              </template>
              <template slot="child_row" slot-scope="props">
                <div v-if = "props.row.data.length > 0">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th>Allocation Number</th>
                        <th>Date of Issuance</th>
                        <th>Reference</th>
                        <th>Purpose of Payment</th>
                        <th>Allocation Received</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(data,i) in props.row.data" :key="i">
                        <th>No. {{ data.AllocationNo  }}</th>  
                        <td>{{ format_date(data.Date) }}</td> 
                        <td>{{ data.Reference }}</td>  
                        <td>{{ data.Purpose }}</td>  
                        <td> ₱ {{Number(data.CAReceived).toLocaleString() }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </template>
          </v-client-table>
        </CCardBody>
      </CCard>
      </transition>
    </CCol>
  </CRow>
</template>

<script>
import axios from 'axios'
import moment from 'moment'
import SelectAccount from './../list/Accounts.vue'

export default {
  name: 'Users',
  components: {
    'select-account': SelectAccount,
  },
  data: () => {
    return {
      you: null,
      dataAccount: "",
      account_number: "",
      year:"",
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
        columns: ['month_name', 'received_summary'],
        items: [],
        options: {
            headings: {
              month_name: 'Calendar Year',
              received_summary: 'Summary for the month'
            },                    
            rowClassCallback: function(row) { 
                return (row.month_name == "Grand Total") ?'bg-warning':'';
            },
            filterable: ['month_name'],
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
      if(this.year == ""){
        this.year = new Date().getFullYear();
      }
      axios.get(  this.$apiAdress + '/api/reports/get-summary?token=' + localStorage.getItem("api_token"),
        { params : {
          account_number: self.account_number.AccountNumber,
          year: self.year,
        }
      }).then(function (response) {
        self.table.items = response.data.allocation_summary;
        self.you = response.data.you;
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
    exportAllocations(){
      var params = {
        year: this.year,
        account_number : this.account_number,
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
        this.$apiAdress + '/api/reports/export-summary?token=' + localStorage.getItem("api_token"),
          params, {
          responseType: 'blob'
        }).then(function (response) {
          const link = document.createElement('a');
          link.href = window.URL.createObjectURL(
                new Blob([response.data])
            );
          var today = new Date();
          link.setAttribute('download', 'Allocation-Summary_' + today +'.xlsx');
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
    this.getAllocations();
  },
}
</script>
