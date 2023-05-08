<template>
  <CRow>
    <CCol col="12" xl="12">
      <transition name="slide">
      <CCard>
        <CCardHeader>
             <h4>Statement of Utilization as per P.A.P for F.Y </h4>
        </CCardHeader>
        <CCardBody>
          <form @submit.prevent="getData">   
          <div class="row">
            <div class="col-5">
              <select-account 
										v-model="account_number"
										:default-value="dataAccount"
								    :defaultList = "accountList"
									>
							</select-account>
            </div>
            <div class="col-3">
              <div class="form-group">
                <label>Date</label>
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
              <button type="button" class="btn btn-primary btn-lg btn-block" @click = "exportStatement"> Export </button>
            </div>
          </div>
          </form>

          <v-client-table :data="table.items" :columns="table.columns" :options="table.options">
              <template slot="January" slot-scope="props">
                <strong> ₱ {{Number(props.row.January).toLocaleString()}} </strong>
              </template>
              <template slot="February" slot-scope="props">
                <strong> ₱ {{Number(props.row.February).toLocaleString()}} </strong>
              </template>
              <template slot="March" slot-scope="props">
                <strong> ₱ {{Number(props.row.March).toLocaleString()}} </strong>
              </template>
              <template slot="April" slot-scope="props">
                <strong> ₱ {{Number(props.row.April).toLocaleString()}} </strong>
              </template>
              <template slot="May" slot-scope="props">
                <strong> ₱ {{Number(props.row.May).toLocaleString()}} </strong>
              </template>
              <template slot="June" slot-scope="props">
                <strong> ₱ {{Number(props.row.June).toLocaleString()}} </strong>
              </template>
              <template slot="July" slot-scope="props">
                <strong> ₱ {{Number(props.row.July).toLocaleString()}} </strong>
              </template>
              <template slot="August" slot-scope="props">
                <strong> ₱ {{Number(props.row.August).toLocaleString()}} </strong>
              </template>
              <template slot="September" slot-scope="props">
                <strong> ₱ {{Number(props.row.September).toLocaleString()}} </strong>
              </template>
              <template slot="October" slot-scope="props">
                <strong> ₱ {{Number(props.row.October).toLocaleString()}} </strong>
              </template>
              <template slot="November" slot-scope="props">
                <strong> ₱ {{Number(props.row.November).toLocaleString()}} </strong>
              </template>
              <template slot="December" slot-scope="props">
                <strong> ₱ {{Number(props.row.December).toLocaleString()}} </strong>
              </template>
              <template slot="Total" slot-scope="props">
                <strong> ₱ {{Number(props.row.Total).toLocaleString()}} </strong>
              </template>
          </v-client-table>

          <v-client-table :data="all_total.items" :columns="all_total.columns" :options="all_total.options">
              <template slot="Grand_Total">
                <strong> Grand Total </strong>
              </template>
              <template slot="January" slot-scope="props">
                <strong> ₱ {{Number(props.row.January).toLocaleString()}} </strong>
              </template>
              <template slot="February" slot-scope="props">
                <strong> ₱ {{Number(props.row.February).toLocaleString()}} </strong>
              </template>
              <template slot="March" slot-scope="props">
                <strong> ₱ {{Number(props.row.March).toLocaleString()}} </strong>
              </template>
              <template slot="April" slot-scope="props">
                <strong> ₱ {{Number(props.row.April).toLocaleString()}} </strong>
              </template>
              <template slot="May" slot-scope="props">
                <strong> ₱ {{Number(props.row.May).toLocaleString()}} </strong>
              </template>
              <template slot="June" slot-scope="props">
                <strong> ₱ {{Number(props.row.June).toLocaleString()}} </strong>
              </template>
              <template slot="July" slot-scope="props">
                <strong> ₱ {{Number(props.row.July).toLocaleString()}} </strong>
              </template>
              <template slot="August" slot-scope="props">
                <strong> ₱ {{Number(props.row.August).toLocaleString()}} </strong>
              </template>
              <template slot="September" slot-scope="props">
                <strong> ₱ {{Number(props.row.September).toLocaleString()}} </strong>
              </template>
              <template slot="October" slot-scope="props">
                <strong> ₱ {{Number(props.row.October).toLocaleString()}} </strong>
              </template>
              <template slot="November" slot-scope="props">
                <strong> ₱ {{Number(props.row.November).toLocaleString()}} </strong>
              </template>
              <template slot="December" slot-scope="props">
                <strong> ₱ {{Number(props.row.December).toLocaleString()}} </strong>
              </template>
              <template slot="Total" slot-scope="props">
                <strong> ₱ {{Number(props.row.Total).toLocaleString()}} </strong>
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
import SelectAccount from '../list/Accounts.vue'

export default {
  name: 'Users',
  components: {
    'select-account': SelectAccount,
  },
  data: () => {
    return {
      accountList: [],
      account_number: "",
      year:"",  
      date : "",
      date_start : "",
      date_end : "",
      dataAccount: "",
      all_total:{
        columns: ['Grand_Total','January','February','March','April','May','June','July','August','September','October','November','December','Total'],
        items: [],
        options: {
            headings: {
              Grand_Total: '',
            },
            filterable: false
        }
      },
      table : {
        columns: ['pap', 'pap_title','January','February','March','April','May','June','July','August','September','October','November','December','Total'],
        items: [],
        options: {
            headings: {
              pap: 'Particulars',
            },
            filterable: ['pap', 'pap_title'],
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
      axios.get(  this.$apiAdress + '/api/reports/get-papStatement?token=' + localStorage.getItem("api_token"),
        { params : {
          account_number: self.account_number.AccountNumber,
          date_start: self.date_start,
          date_end: self.date_end,
        }
      }).then(function (response) {
        self.table.items    = response.data.data;
        self.all_total.items = response.data.months_total;
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
    exportStatement(){
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
        this.$apiAdress + '/api/reports/export-pap-statement?token=' + localStorage.getItem("api_token"),
          params, {
          responseType: 'blob'
        }).then(function (response) {
          const link = document.createElement('a');
          link.href = window.URL.createObjectURL(
                new Blob([response.data])
            );
          var today = new Date();
          link.setAttribute('download', 'Status-Report_' + today +'.xlsx');
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
