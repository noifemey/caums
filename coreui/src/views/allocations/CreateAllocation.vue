<template>
	<div>

		<CModal :title="title" color="primary" :closeOnBackdrop="closeOnBackdrop" size="lg" :show.sync="warningModal">
			<template slot="body-wrapper">
				<div v-if="validationErrors">
					<ul class="alert alert-danger">
						<li v-for="(value, key, index) in validationErrors" v-bind:key="(value, key, index)">@{{ value }}</li>
					</ul>
				</div>

				<div class="card-body">
				<div class = "row">
					<!-- <div class = "col-12"> -->
						<div class="col-12 form-group">
							<label for="allocation_number">Allocation Number</label>
							<input type="text" class="form-control" id="allocation_number" placeholder="Allocation Number" v-model = "allocation_number">
						</div>
						<div class="col-12 form-group">
							<label for="period_coverage">Period Coverage</label>
							<input type="date" class="form-control" id="period_coverage" placeholder="Period Coverage" v-model = "period_coverage">
						</div>
						<div class="col-12 form-group">
							<label for="account_number">Account Number</label>
							<!-- <input type="text" class="form-control" id="account_number" placeholder="Account Number" v-model = "account_number"> -->
							<multiselect
								v-model="account_number" 
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
						<div class="col-12 form-group">
							<label for="date_issued">Date Issued</label>
							<input type="date" class="form-control" id="date_issued" placeholder="Date Issued" v-model = "date_issued">
						</div>
						<div class="col-12 form-group">
							<label for="reference">Reference</label>
							<input type="text" class="form-control" id="reference" placeholder="Reference" v-model = "reference">
						</div>
						<div class="col-12 form-group">
							<label for="purpose">Purpose</label>
							<textarea class="form-control" id="purpose" placeholder="Purpose" v-model = "purpose"></textarea>
						</div>
						<div class="col-12 form-group">
							<label for="amount_issued">Amount Issued</label>
							<input type="text" class="form-control" id="amount_issued" placeholder="Amount Issued" v-model = "amount_issued">
						</div>
						<div class="col-12 form-group">
							<label for="amount_recieved">Amount Received</label>
							<input type="text" class="form-control" id="amount_recieved" placeholder="Amount Received" v-model = "amount_recieved">
						</div>
					<!-- </div> -->
				</div>
				</div>
			</template>

			<template slot="footer">
				<button type="button" class="btn btn-danger" @click = "closeModal">Cancel</button> 
				
				<div v-if="modalType == 'create'">
					<button type="button" class="btn btn-success" @click = "storeAccount">Save</button>
				</div>
				<div v-else>
					<button type="button" class="btn btn-primary" @click = "updateAccount" >Update</button>
				</div>
			</template>
		</CModal>
	</div>
</template>

<style>
  /* .multiselect__content {
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
  } */
</style>

<script>

	import axios from 'axios'
	import moment from 'moment'

  	export default {
		props: {
			modalType: {
				type: String, // not 'string'
				required: true,
				default: "create"
			},
			title: {
				type: String, // not 'number'
				required: true,
				default: "Create Allocation"
			},
			today: {
				default: ""
			},
			accountList: {
				type: Array, // not 'number'
				required: true,
				default: () => ({})
			},
		},
		data () {
			return {
				warningModal: false,
				allocation_id: 0,
				allocation_number: "0000-0000-00",
				period_coverage: "",
				account_number: "",
				date_issued:"",
				reference:"",
				purpose:"",
				amount_issued:"",
				amount_recieved:"",
				validationErrors:"",
				closeOnBackdrop:false
			}
		},
		methods: {
			format_date(value,format){
				if (value) {
				return moment(String(value)).format(format);
				}
			},
			setValue: function(value) {
				this.warningModal = value.warningModal;
				this.allocation_id = value.allocation_id;
				this.allocation_number = value.allocation_number;
				//this.period_coverage = value.period_coverage;
				//this.account_number = value.account_number;
          		this.account_number 	= this.accountList.find(option => option.AccountNumber === value.account_number);
				//this.date_issued = value.date_issued;
				this.reference = value.reference;
				this.purpose = value.purpose;
				this.amount_issued = value.amount_issued;
				this.amount_recieved = value.amount_recieved;
				
				this.period_coverage = this.format_date(value.period_coverage,'yyyy-MM-DD');
				this.date_issued = this.format_date(value.date_issued,'yyyy-MM-DD');
			},
			closeModal(){
				this.warningModal = false;
				this.validationErrors = "";
			},
			storeAccount() {
				let self = this;
				var params = {
					AllocationNo: this.allocation_number,
					MonthYear : this.period_coverage,
					//AccountNo : this.account_number,
        			AccountNo : this.account_number.AccountNumber,
					Date : this.date_issued,
					Reference : this.reference,
					Purpose : this.purpose,
					CAIssued : this.amount_issued,
					CAReceived : this.amount_recieved,
				}

				axios.post(  this.$apiAdress + '/api/allocations/store?token=' + localStorage.getItem("api_token"),params)
				.then(function (response) {
					self.$emit('updated');
					self.validationErrors = "";
					self.warningModal = false;
					self.$swal.fire({
						position: 'top-end',
						icon: 'success',
						title: 'New Account has been added.',
						showConfirmButton: false,
						timer: 1500
					})
				}).catch(function (error) {
					console.log(error);
					if (error.response.status == 422){
						self.validationErrors = error.response.data.errors;
					}else if(error.response.status == 401){
        				self.$router.push({ path: '/login' });
					}else{
						alert(error);
					}
				});
			},
			updateAccount() {
				let self = this;
				var params = {
					allocation_id: this.allocation_id,
					AllocationNo: this.allocation_number,
					MonthYear : this.period_coverage,
					//AccountNo : this.account_number,
        			AccountNo : this.account_number.AccountNumber,
					Date : this.date_issued,
					Reference : this.reference,
					Purpose : this.purpose,
					CAIssued : this.amount_issued,
					CAReceived : this.amount_recieved,
				}

				axios.post(  this.$apiAdress + '/api/allocations/update?token=' + localStorage.getItem("api_token"),params)
				.then(function (response) {
					console.log(response);
					self.$emit('updated');
					self.validationErrors = "";
					self.warningModal = false;
					self.$swal.fire({
						position: 'top-end',
						icon: 'success',
						title: 'Allocation has been updated.',
						showConfirmButton: false,
						timer: 1500
					})
				}).catch(function (error) {
					console.log(error);
					if (error.response.status == 422){
						self.validationErrors = error.response.data.errors;
					}else if(error.response.status == 401){
        				self.$router.push({ path: '/login' });
					}else{
						alert(error);
					}
				});
			},
			AccountnameWithLang({ AccountNumber, AccountName }) {
				return `${AccountNumber} - ${AccountName}`;
    },
		},
		created: function() {
			if(this.modalType == "create"){
				this.$parent.$on('create', this.setValue);
			}else{
				this.$parent.$on('edit', this.setValue);
			}
		}
  	}
</script>