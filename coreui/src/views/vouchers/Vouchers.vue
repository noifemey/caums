<template>
  <CRow>
    <CCol col="12" xl="12">
      <transition name="slide">
      <CCard>
        <CCardHeader>
            <h4>Check Data Entry</h4>
        </CCardHeader>
        <CCardBody>
          <form @submit.prevent="getData">   
          <div class="row">
            <div class="col-4">
              <label>Check Number</label>
              <input class="form-control  form-control-lg" type="text" v-model="check_number">
            </div>  
            <div class="col-4">
              <label>DV Number</label>
              <input class="form-control  form-control-lg" type="text" v-model="dv_number">
            </div>  
            <div class="col-4">
              <label>Payee</label>
              <input class="form-control  form-control-lg" type="text" v-model="payee">
            </div>  
            <div  v-if = "accountList.length > 0" class="col-6">
              <select-account 
										v-model="account_number"
										:default-value="dataAccount"
								    :defaultList = "accountList"
                    @input = "input"
									>
							</select-account>
            </div>
            <div class="col-6">
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
            <div class="col-12">
              <label></label>
              <button type="submit" class="btn btn-success btn-lg btn-block"> Search </button>
            </div>
          </div>
          </form>
          <hr>
          <div class="row">
            <div class="col-3">
              <button type="button" class="btn btn-dark btn-lg btn-block" @click = "modalCreateShow"> Create New</button>
            </div>
          </div>

          <v-client-table :data="table.items" :columns="table.columns" :options="table.options">

              <template slot="check_date" slot-scope="props">
                <strong> {{format_date(props.row.check_date)}} </strong>
              </template>
              <template slot="check_amount" slot-scope="props">
                <strong> ₱ {{Number(props.row.check_amount).toLocaleString()}} </strong>
              </template>
              <template slot="amount" slot-scope="props">
                <strong> ₱ {{Number(props.row.amount).toLocaleString()}} </strong>
              </template>
              <template slot="action" slot-scope="props">
                  <CButton color="primary" @click = "modalEditShow(props.row)">Edit</CButton>
                  <CButton color="danger" @click = "deleteVoucher(props.row.check_no)">Delete</CButton>
              </template>

              <template slot="child_row" slot-scope="props">
                <div v-if = "props.row.children.length > 0">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th>Obligation Number</th>
                        <th>Total Amount</th>
                        <th>Object</th>
                        <th>Amount</th>
                        <th>Specification</th>
                        <th>Allocation</th>
                        <th>PPA</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(data,i) in props.row.children" :key="i">
                        <td>{{ data.obligation_no }}</td>  
                        <td> ₱ {{Number(data.total_amount).toLocaleString() }}</td>
                        <td>{{ data.object }}</td>  
                        <td> ₱ {{Number(data.amount).toLocaleString() }}</td>  
                        <td>{{ data.Description }}</td>  
                        <td>{{ data.allocation }}</td>  
                        <td>{{ data.ppa }}</td>  
                      </tr>
                    </tbody>
                  </table>
                </div>
              </template>
              <template slot="CenterCode">
              </template>
          </v-client-table>

          <div v-if = "accountList.length > 0">
            <create-voucher
              title = "Create Voucher"
              @updated = "getData"
              :objectList = "objectList"
              :allocationList = "allocationList"
              :ppaList = "ppaList"
              :accountList = "accountList"
            >
            </create-voucher>
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
import SelectAccount from '../list/Accounts.vue'
import CreateVoucher from './CreateVoucher.vue';

export default {
  name: 'Users',
  components: {
    'select-account': SelectAccount,
    'create-voucher': CreateVoucher
  },
  data: () => {
    return {
      account_number: "",
      year:"",  
      date : "",
      date_start : "",
      date_end : "",
      dataAccount: "",
      check_number: "",
      dv_number: "",
      payee: "",
      grandTotal:"",
      table : {
        columns: ['check_no', 'check_date','voucher_no','payee','purpose','acct_no','check_amount','amount','action'],
        items: [],
        options: {
            headings: {
              check_date: 'Check Date',
              check_no: 'Check Number',
              voucher_no: 'DV No.',
              payee: 'Payee',
              acct_no: 'Account Number',
              purpose: 'Purpose',
              check_amount: 'Check Amount',
              amount: 'Total Amount',
            },
            filterable: ['payee','check_no','purpose','check_date','voucher_no','acct_no'],
        }
      },
      objectList:[],
      allocationList:[],
      ppaList:[],
      accountList:[],
      modal:{
        modalType: "edit",
        warningModal: true,
        voucher_id: 0,
				check_no: "",
				voucher_no: "",
				account_no: "",
        accountData: "",
				date_issued: "",
				voucher_amount: "",
				payee: "",
				purpose: "",

				obligations: [
					{
						obligation_id: 0,
						obligation_no: '',
						total_amount: 0,
						items: [{
							trans_id: 0,
							obligation_no: '',
							object_code: '',
							total_amount: 0,
							description: '',
							amount: 0,
							allocation: '',
							ppa: '',
						}]
					}
				]
      }
    }
  },
  methods: {    
    input(value){
      console.log(value);
    } ,
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
      axios.get(  this.$apiAdress + '/api/vouchers?token=' + localStorage.getItem("api_token"),
        { params : {
          account_number: self.account_number.AccountNumber,
          date_start: self.date_start,
          date_end: self.date_end,
          check_number: self.check_number,
          dv_number: self.dv_number,
          payee: self.payee,
        }
      }).then(function (response) {
        self.table.items    = response.data.data;
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
    getLib (){
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
      axios.get(  this.$apiAdress + '/api/vouchers/get-lib?token=' + localStorage.getItem("api_token"))
      .then(function (response) {
        self.allocationList    = response.data.allocations;
        self.objectList    = response.data.objectCodes;
        self.ppaList    = response.data.ppaCodes;
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
    modalCreateShow(){

     var modalValues = {
        modalType: "create",
        warningModal: true,
        voucher_id: 0,
				check_no: "",
				voucher_no: "",
				account_no: "",
				date_issued: "",
				voucher_amount: "",
				payee: "",
				purpose: "",

				obligations: [
					{
						obligation_id: 0,
						obligation_no: '',
						total_amount: 0,
						items: [{
							trans_id: 0,
							obligation_no: '',
							object_code: '',
							total_amount: 0,
							description: '',
							amount: 0,
							allocation: '',
							ppa: '',
						}]
					}
				]
      };

      this.$emit('create', modalValues);
    },
    modalEditShow(props){
      this.modal.modalType        = "edit";
      this.modal.warningModal     = true;
      this.modal.voucher_id       = props.id;
      this.modal.check_no         = props.check_no;
      this.modal.voucher_no       = props.voucher_no;
      this.modal.account_no       = props.acct_no;
      this.modal.accountData      = props.acct_no;
      this.modal.date_issued      = props.check_date;
      this.modal.voucher_amount   = props.check_amount;
      this.modal.payee            = props.payee;
      this.modal.purpose          = props.purpose;

      this.modal.obligations  = [];

      var objlist = [];

      props.children.forEach(element => {

        var id = element.fotrans_id;

        if (typeof objlist[id] === "undefined" ) {
            objlist[id] = [];
            objlist[id].obligation_id = element.fotrans_id;
            objlist[id].obligation_no = element.obligation_no;
            objlist[id].total_amount = element.total_amount;
        }
        
        var trans = {
						trans_id: element.id,
            obligation_no: element.obligation_no,
            object_code: element.object,
            total_amount: element.total_amount,
            description: element.Description,
            amount: element.amount,
            allocation: element.allocation,
            ppa: element.ppa,
        }

        if (typeof objlist[id].items === "undefined" ) {
            objlist[id].items = [];
        }
        objlist[id].items.push(trans);
      });
      
      objlist.forEach(element => {
        var dt = Object.assign({}, element);
        this.modal.obligations.push(dt);
      });

      console.log("modalEditShow");
      console.log(this.modal);

      this.$emit('create', this.modal);
    },
    deleteVoucher(check_no){
      let self = this;
      this.$swal.fire({
        title: 'Are you Sure you want to delete this?',
        showDenyButton: false,
        showCancelButton: true,
        confirmButtonText: `Delete`,
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
        axios.get(  this.$apiAdress + '/api/vouchers/delete?token=' + localStorage.getItem("api_token"),{
          params: {CheckNo : check_no}
        }).then(function (response) {
          self.$swal.fire({
            position: 'top-end',
            icon: 'success',
            title: 'Successfully deleted user.',
            showConfirmButton: false,
            timer: 1500
          })
          self.getData();
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
  created: function(){
    this.getLib();
  },
  watch: {
    date(){
      this.date_start = this.date.start;
      this.date_end = this.date.end;
    },
  },
}
</script>
