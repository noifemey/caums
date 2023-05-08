<template>
  <CRow>
    <CCol col="12" xl="6">
      <CCard>
        <CCardHeader>
             <h6>Check Data Entry </h6>
        </CCardHeader>
        
        <form @submit.prevent="submitForm">   
        <CCardBody>
          
          <div v-if="validationErrors">
            <ul class="alert alert-danger">
              <li v-for="(value, key, index) in validationErrors" v-bind:key="(value, key, index)">{{ value[0] }}</li>
            </ul>
          </div>
          <div class = "row">
						<div class="col-4 form-group">
							<label for="check_no">Check Number</label>
							<input type="number" class="form-control form-control-sm" id="check_no" placeholder="Check Number" v-model = "check_no" @blur="editData()" required>
						</div>
						<div class="col-4 form-group">
							<label for="voucher_no">Voucher Number</label>
							<input type="text" class="form-control  form-control-sm" id="voucher_no" placeholder="Voucher Number" v-model = "voucher_no" required>
						</div>
						<div class="col-4 form-group">
							<label>Accounts</label>
							<multiselect
								v-model="account_no" 
								track-by="AccountName" 
								label="AccountName"
								:custom-label="AccountnameWithLang"
								placeholder=""
								:options="accountList"
								:allow-empty="false"
								:show-labels="false"
								:internalSearch="true"
							>
							</multiselect>

						</div>
						<div class="col-4 form-group">
							<label for="date_issued">Date Issued</label>
							<input type="date" class="form-control  form-control-sm" id="date_issued" v-model = "date_issued" required>
						</div>
						<div class="col-4 form-group">
							<label for="voucher_amount">Amount</label>
							<input type="text" class="form-control" id="voucher_amount" v-model = "voucher_amount" required>
						</div>
						<div class="col-4 form-group">
							<label for="payee">Payee</label>
							<input type="text" class="form-control  form-control-sm" id="payee" v-model = "payee" required>
						</div>
						<div class="col-12 form-group">
							<label for="purpose">Purpose</label>
							<textarea class="form-control  form-control-sm" id="purpose" v-model = "purpose" required></textarea>
						</div>
            <div class="col-12 form-group">
              <h6>Total Amount : <strong>₱ {{Number(total_amount).toLocaleString() }}</strong></h6>
              <span v-if="total_amount != voucher_amount"  class="text text-danger">
                Warning: Total amount ( ₱ {{Number(total_amount).toLocaleString() }} ) is not equal to check amount ( ₱ {{Number(voucher_amount).toLocaleString() }} ) . Please Check before saving!
              </span>
            </div>	
						<div class="col-12 form-group">
							<input type="button" class="btn btn-success btn-sm btn-block" @click="addNewSection" value="Add New Obligation">
								
						</div>
					</div>

					<div v-for="(section, index) in obligations" :key="index">
						<div class="row form-group">
							<div class="col-12 form-group">
								<hr>
							</div>
							<div class="col-3 form-group">
								<h5>Obligation {{ index + 1}}</h5>
							</div>
							<div class="col-5 form-group">
								<label for="obligation_no"><h6>Obligation Number</h6></label>
								<input type="text" name="obligation_no" class="form-control  form-control-sm" placeholder="00-00-000000" v-model="section.obligation_no"  required>
							</div>							
							<div class="col-3 form-group">
								<h6>Total Amount : <strong>₱ {{Number(section.total_amount).toLocaleString() }}</strong></h6>
							</div>				
							<div v-if="index>0" class="col-1 form-group">
								<span class="btn btn-danger btn-sm float-right" style="cursor:pointer" @click="deleteObligation(section.obligation_id,index)">
									X
								</span>
							</div>
							
							<div class="col-12">
								<h6>Obligation {{ index + 1}} Particulars</h6>
							</div>
						</div>
						<div class="row form-group" v-for="(item, iors) in section.items" :key="iors">
              
							<div class="col-12 form-group"  align="center">
								______________________________________ {{iors + 1}} ______________________________________
							</div>
							<div class="col-4 form-group">
								<label>Object Codes</label>
								<multiselect
									v-model="item.object_code" 
									track-by="AccountTitle" 
									label="AccountTitle"
									:custom-label="ObjectCodeName"
									placeholder=""
									:options="objectList"
									:allow-empty="false"
									:show-labels="false"
									:internalSearch="true"
								>
								</multiselect>
							</div>
							<div class="col-4 form-group">
								<label for="desc">Description</label>
								<textarea name="desc" class="form-control form-control-sm" v-model="item.description" required></textarea>
							</div>
							<div class="col-4 form-group">
								<label for="obj_code">Amount</label>
								<input type="text" name="obj_code" class="form-control" @blur="computeAmount(section.items,index)" placeholder="0.00" v-model="item.amount" required>
							</div>
							<div class="col-4 form-group">

        				<label>Allocations</label>
								<multiselect
									v-model="item.allocation" 
									track-by="AllocationNo" 
									label="AllocationNo"
									placeholder=""
									:options="allocationList"
									:allow-empty="false"
									:show-labels="false"
									:internalSearch="true"
								>
								</multiselect>
							</div>
							<div class="col-7 form-group">
        				<label>P.P.A Codes</label>
								<multiselect
									v-model="item.ppa" 
									track-by="PAPTitle" 
									label="PAPTitle"
									:custom-label="PapCodeName"
									placeholder=""
									:options="ppaList"
									:allow-empty="false"
									:show-labels="false"
									:internalSearch="true"
								>
								</multiselect>
							</div>
							<div v-if="iors>0" class="col-1 form-group">
								<span class="btn btn-danger btn-sm float-right" style="cursor:pointer" @click="deleteTransaction(item.trans_id,index,iors),computeAmount(section.items,index)">
									X
								</span>
							</div>
						</div>
						<div class="col-12">
							<input  class="btn btn-success btn-sm" @click="addNewItem(index)" value="Add Particulars"/> <!-- passing the index -->
						</div>
					</div>
        </CCardBody>
        
        <CCardFooter>
          <div class="row">
            <div class="col-6"  align="right">
				      <input type="button" class="btn btn-danger btn-lg btn-block" @click = "resetData" value="Clear"/>
            </div>
            <div class="col-6"  align="right">
              <div v-if="action_type == 'create'">
                <CButton type="submit" size="lg" color="primary" :block="block"><CIcon name="cil-check-circle"/> Save</CButton>
              </div>
              <div v-else>
                <CButton type="submit" size="lg" color="primary" :block="block"><CIcon name="cil-check-circle"/> Update</CButton>
              </div>
            </div>
            <div v-if="total_amount != voucher_amount" class="col-12"  align="center">
              <span class="text text-danger">
                Warning: Total amount ( ₱ {{Number(total_amount).toLocaleString() }} ) is not equal to check amount ( ₱ {{Number(voucher_amount).toLocaleString() }} ) . Please Check before saving!
              </span>
            </div>
          </div>
        </CCardFooter>
        </form>
      </CCard>
    </CCol>

    <CCol col="12" xl="6">
      <transition name="slide">
      <CCard>
        <CCardHeader>
             <h6>Status of Cash Allocations, Utilized and Balances </h6>
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
            <div class="col-5">
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
              <button type="submit" class="btn btn-success btn-sm btn-block">  Search </button>
            </div>
          </div>
          </form>
          <hr>

          <div class = "scroll">
          <v-client-table :data="table.items" :columns="table.columns" :options="table.options">
              <template slot="cash_received" slot-scope="props">
                <strong> ₱ {{Number(props.row.cash_received).toLocaleString()}} </strong>
              </template>
              <template slot="total" slot-scope="props">
                <strong> ₱ {{Number(props.row.total).toLocaleString()}} </strong>
              </template>
              <template slot="balance" slot-scope="props">
                <strong> ₱ {{Number(props.row.balance).toLocaleString()}} </strong>
              </template>
          </v-client-table>
          </div>

          <table class="table table-striped">
            <thead>
              <tr>
                <th colspan = "2">Total Cash Allocation Received for the {{format_date(date_start)}} to {{format_date(date_end)}}</th>
                <th></th>
                <th>Total Utilization</th>
                <th>Balance</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <th colspan = "2">₱ {{ Number(all_allocation).toLocaleString()  }}</th>  
                <td></td> 
                <td>₱ {{ Number(utilization_total).toLocaleString() }}</td>  
                <td>₱ {{ Number(all_balance).toLocaleString() }}</td>
              </tr>
            </tbody>
          </table>
        </CCardBody>
      </CCard>
      </transition>
    </CCol>
    
  </CRow>
</template>

<style scoped>
  .card {
    font-size: 11px;
  }
  .scroll{
    /* width: 100%;
    height: 600px;
    overflow: scroll; */
    width: 100%;
    height: 900px;
    padding: 0;
    margin-bottom: 0;
    overflow-x: auto;
    overflow-y: auto;
    list-style: none;
  }

</style>
<style>
  .multiselect__content {
      font-size: 11px;
  }
  .multiselect__content-wrapper{
      width: max-content !important;
  }
  .multiselect__input{
      font-size: 11px;
  }
  .multiselect__single {
      font-size: 11px;
      margin-bottom: 0px;
  }
</style>


<script>
import axios from 'axios'
import moment from 'moment'
import SelectAccount from '../list/Accounts.vue'
import SelectObjectCode from '../list/ObjectCodes.vue'
import SelectAllocation from '../list/Allocations.vue'

export default {
  name: 'Users',
  components: {
    'select-account': SelectAccount,
    'select-objectcode': SelectObjectCode,
    'select-allocation': SelectAllocation,
  },
  data: () => {
    return {
      block: true,
      accountList: [],
      objectList: [],
      allocationList: [],
      ppaList: [],
			validationErrors:"",

      action_type: "create",
      voucher_id: 0,
      check_no: "",
      voucher_no: "",
      account_no: "",
      date_issued: "",
      voucher_amount: "",
      payee: "",
      purpose: "",

      total_amount: 0,

      obligations: [
        {
          obligation_id: 0,
          obligation_no: '',
          total_amount: 0,
          items: [{
            trans_id: 0,
            obligation_no: '',
            object_code: '',
            total_amount: '',
            description: '',
            amount: '',
            allocation: '',
            ppa: '',
          }]
        }
      ],

      account_number: "",
      year:"",  
      date : "",
      date_start : "",
      date_end : "",
      dataAccount: "",
      all_allocation:"",
      all_balance:"",
      allocation_total:"",
      balance_total:"",
      utilization_total:"",
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
        columns: ['allocation_no', 'reference','purpose','cash_received','total','balance'],
        items: [],
        options: {
            headings: {
              allocation_no: 'Allotment Number',
              reference: 'Reference',
              purpose: 'Purpose',
              cash_received: 'Cash Received',
              total: 'Utilization',
              balance: 'Balance'
            },                    
            rowClassCallback: function(row) { 
                return (row.month_name == "Grand Total") ?'bg-warning':'';
            },
            filterable: ['allocation_no', 'reference','purpose'],
        }
      }
    }
  },
  methods: {    
    computeAmount(section,index){
      var total = 0;
      section.forEach(items => {
        var integer = parseFloat(items.amount);
        total += integer;
      });
      this.obligations[index].total_amount = total;

      this.total_amount = 0;
      this.obligations.forEach(items => {
        var integer = parseFloat(items.total_amount);
        this.total_amount += integer;
      });

      return total;
    },
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
      axios.get(  this.$apiAdress + '/api/reports/get-status?token=' + localStorage.getItem("api_token"),
        { params : {
          account_number: self.account_number.AccountNumber,
          date_start: self.date_start,
          date_end: self.date_end,
        }
      }).then(function (response) {
        self.table.items    = response.data.data;
        self.all_allocation = response.data.all_allocation;
        self.all_balance    = response.data.all_balance;
        self.allocation_total = response.data.allocation_total;
        self.balance_total    = response.data.balance_total;
        self.utilization_total = response.data.utilization_total;
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
    submitForm(){
      if(this.action_type == "create"){
        this.storeData();
      }else{
        this.updateData();
      }

    },
    storeData() {
      let self = this;
      var params = {
        voucher_id: this.voucher_id,
        check_no : this.check_no,
        voucher_no : this.voucher_no,
        account_no : this.account_no.AccountNumber,
        date_issued : this.date_issued,
        voucher_amount : this.voucher_amount,
        payee : this.payee,
        purpose : this.purpose,
        obligations : this.obligations,
      }
      self.$swal.fire({
        title: 'Please Wait !',
        html: 'Saving Voucher..',// add html attribute if you want or remove
        allowOutsideClick: false,
        showConfirmButton:false,
        onBeforeOpen: () => {
            self.$swal.showLoading()
        },
      });

      axios.post(  this.$apiAdress + '/api/vouchers/store?token=' + localStorage.getItem("api_token"),params)
      .then(function (response) {
        self.validationErrors = "";
        self.resetData();
        //self.$swal('Success', 'New Account has been added.', 'OK');
        self.$swal.fire({
          position: 'top-end',
          icon: 'success',
          title: 'New Voucher has been added.',
          showConfirmButton: true,
          //timer: 1500
        })
      }).catch(function (error) {
        self.$swal.close();
        if (error.response.status == 422){
          self.validationErrors = error.response.data.errors;
          var errors = '<ul class="alert alert-danger">';
          for (var key of Object.keys(self.validationErrors)) {
            var element = self.validationErrors[key];
            errors += "<li>" + element[0] + "</li>";
            console.log(key + " -> " + self.validationErrors[key])
          }

          errors += '</ul>';

          self.$swal.fire({
            position: 'top-end',
            icon: 'error',
            title: "Action Failed",
            html: errors,
            showConfirmButton: true
          })
        }else if(error.response.status == 401 || error.response.status == 403){
              self.$router.push({ path: '/login' });
        }else{
          alert(error);
        }
      });
    },
    resetData(){
        this.action_type= "create";
        this.voucher_id =  0,
				this.check_no =  "",
				this.voucher_no =  "",
				this.account_no =  "",
				this.date_issued =  "",
				this.voucher_amount =  "",
				this.payee =  "",
				this.purpose =  "",

				this.obligations = [
        {
          obligation_id: 0,
          obligation_no: '',
          total_amount: 0,
          items: [{
            trans_id: 0,
            obligation_no: '',
            object_code: '',
            total_amount: '',
            description: '',
            amount: '',
            allocation: '',
            ppa: '',
          }]
        }
      ];
    },
    editData(){
      let self = this;
      // self.$swal.fire({
      //   title: 'Please Wait !',
      //   html: 'Loading..',// add html attribute if you want or remove
      //   allowOutsideClick: false,
      //   showConfirmButton:false,
      //   onBeforeOpen: () => {
      //       self.$swal.showLoading()
      //   },
      // });
      var params = {
        check_no: self.check_no
      };

      console.log(params);

      axios.post(  this.$apiAdress + '/api/vouchers/edit?token=' + localStorage.getItem("api_token"),params
      ).then(function (response) {
        var data = response.data.data;
        if(data.success){
          // self.check_no 		= value.check_no;
          self.action_type  = "edit";
          self.voucher_id 	= data.data.voucher_id;
          self.voucher_no 	= data.data.voucher_no;
          self.account_no 	= self.accountList.find(option => option.AccountNumber === data.data.account_no);
          self.date_issued 	= data.data.date_issued;
          self.voucher_amount = data.data.voucher_amount;
          self.payee 			= data.data.payee;
          self.purpose 		= data.data.purpose;
          self.obligations 	= data.data.obligations;

          self.obligations.forEach(element => {
          	element.items.forEach(ors => {
          		ors.ppa = self.ppaList.find(option => option.PAPCode === ors.ppa);
          		ors.object_code = self.objectList.find(option => option.AccountCode === ors.object_code);
          		ors.allocation = self.allocationList.find(option => option.AllocationNo === ors.allocation);
          	});
          });

          console.log(response.data);

        }else{
          self.action_type= "create";
          self.voucher_id =  0,
          self.voucher_no =  "",
          self.account_no =  "",
          self.date_issued =  "",
          self.voucher_amount =  "",
          self.payee =  "",
          self.purpose =  "",

          self.obligations = [
          {
            obligation_id: 0,
            obligation_no: '',
            total_amount: 0,
            items: [{
              trans_id: 0,
              obligation_no: '',
              object_code: '',
              total_amount: '',
              description: '',
              amount: '',
              allocation: '',
              ppa: '',
            }]
          }
        ];
        }
        
        //self.$swal.close();
      }).catch(function (error) {
        console.log(error);
        //self.$swal.close();
        if (error.response.status == 422){
          var errors = '<ul class="alert alert-danger">';
          for (var key of Object.keys(error.response.data.errors)) {
            var element = error.response.data.errors[key];
            errors += "<li>" + element[0] + "</li>";
            console.log(key + " -> " + error.response.data.errors[key])
          }

          errors += '</ul>';

          self.$swal.fire({
            position: 'top-end',
            icon: 'error',
            title: "Action Failed",
            html: errors,
            showConfirmButton: true
          })
        }
        else if(error.response.status == 401 || error.response.status == 403){
        	self.$router.push({ path: '/login' });
        }else{
          alert(error);
        }
      });
    },
    updateData() {
      let self = this;
      var params = {
        voucher_id: this.voucher_id,
        check_no : this.check_no,
        voucher_no : this.voucher_no,
        account_no : this.account_no.AccountNumber,
        date_issued : this.date_issued,
        voucher_amount : this.voucher_amount,
        payee : this.payee,
        purpose : this.purpose,
        obligations : this.obligations,
      }
      
      self.$swal.fire({
        title: 'Please Wait !',
        html: 'Saving Voucher..',// add html attribute if you want or remove
        allowOutsideClick: false,
        showConfirmButton:false,
        onBeforeOpen: () => {
            self.$swal.showLoading()
        },
      });

      axios.post(  this.$apiAdress + '/api/vouchers/update?token=' + localStorage.getItem("api_token"),params)
      .then(function (response) {
        self.validationErrors = "";
        self.$swal.fire({
          position: 'top-end',
          icon: 'success',
          title: 'Account has been updated.',
          showConfirmButton: true,
          //timer: 1500
        })
        
        self.resetData();

      }).catch(function (error) {
        self.$swal.close();
        if (error.response.status == 422){
          self.validationErrors = error.response.data.errors;
          var errors = '<ul class="alert alert-danger">';
          for (var key of Object.keys(self.validationErrors)) {
            var element = self.validationErrors[key];
            errors += "<li>" + element[0] + "</li>";
            console.log(key + " -> " + self.validationErrors[key])
          }

          errors += '</ul>';

          self.$swal.fire({
            position: 'top-end',
            icon: 'error',
            title: "Action Failed",
            html: errors,
            showConfirmButton: true
          })
        }else if(error.response.status == 401 || error.response.status == 403){
              self.$router.push({ path: '/login' });
        }else{
          alert(error);
        }
      });
    },
    addNewSection() {
        this.obligations.push({
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
              })
    },
    addNewItem(id) {

        // passing the id of the section
        this.obligations[id].items.push({
  trans_id: 0,
  obligation_no: '',
  object_code: '',
  total_amount: 0,
  description: '',
  amount: 0,
  allocation: '',
  ppa: '',
        });

// if(this.modalType == 'edit'){
// 	this.obligations = Object.assign({}, this.obligations);
// }

    },
    deleteObligation(obligation_id,index){
      console.log(index);
      if(this.modalType == "edit" && obligation_id > 0){
        let self = this;
        this.$swal.fire({
          title: 'Are you Sure you want to delete this?',
          showDenyButton: false,
          showCancelButton: true,
          confirmButtonText: `Delete`,
        }).then((result) => {
          /* Read more about isConfirmed, isDenied below */
          if (result.isConfirmed) {
          axios.get(  this.$apiAdress + '/api/vouchers/deleteObligation?token=' + localStorage.getItem("api_token"),{
            params: {obligation_id : obligation_id}
          }).then(function (response) {
            self.$swal.fire({
              position: 'top-end',
              icon: 'success',
              title: 'Successfully deleted user.',
              showConfirmButton: false,
              timer: 1500
            })
            self.$emit('updated');
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
        
      }
      this.obligations.splice(index, 1);
    },
    deleteTransaction(TransNo,index,iors){
      console.log(index);
      if(this.modalType == "edit" && TransNo > 0){
        let self = this;
        this.$swal.fire({
          title: 'Are you Sure you want to delete this?',
          showDenyButton: false,
          showCancelButton: true,
          confirmButtonText: `Delete`,
        }).then((result) => {
          /* Read more about isConfirmed, isDenied below */
          if (result.isConfirmed) {
          axios.get(  this.$apiAdress + '/api/vouchers/deleteTran?token=' + localStorage.getItem("api_token"),{
            params: {TransNo : TransNo}
          }).then(function (response) {
            self.$swal.fire({
              position: 'top-end',
              icon: 'success',
              title: 'Successfully deleted user.',
              showConfirmButton: false,
              timer: 1500
            })
            self.$emit('updated');
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
        
      }
      this.obligations[index].items.splice(iors, 1);
    },
    ObjectCodeName({ AccountCode, AccountTitle }) {
              return `${AccountCode} - ${AccountTitle}`;
    },
    PapCodeName({ PAPCode, PAPTitle }) {
              return `${PAPCode} - ${PAPTitle}`;
    },
    AccountnameWithLang({ AccountNumber, AccountName }) {
        return `${AccountNumber} - ${AccountName}`;
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
        if(error.response.status == 401 || error.response.status == 403){
        	self.$router.push({ path: '/login' });
        }else{
          alert(error);
        }
      });
    },
    exportStatus(){
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
        this.$apiAdress + '/api/reports/export-status?token=' + localStorage.getItem("api_token"),
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
    },
  },
  mounted: function(){
    //this.getData();
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
