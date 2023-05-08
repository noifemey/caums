<template>
	<div>

		<CModal :title="title" color="primary" :show.sync="warningModal" size = "xl" :closeOnBackdrop = "closeOnBackdrop">
			<template slot="body-wrapper">
				<div v-if="validationErrors">
					<ul class="alert alert-danger">
						<li v-for="(value, key, index) in validationErrors" v-bind:key="(value, key, index)">@{{ value }}</li>
					</ul>
				</div>
				
				<div class="card-body">
					<div class = "row">
						<div class="col-3 form-group">
							<label for="check_no">Check Number</label>
							<input type="number" class="form-control" id="check_no" placeholder="Check Number" v-model = "check_no">
						</div>
						<div class="col-3 form-group">
							<label for="voucher_no">Voucher Number</label>
							<input type="text" class="form-control" id="voucher_no" placeholder="Voucher Number" v-model = "voucher_no">
						</div>
						<div class="col-4 form-group">
							<!-- <label for="account_no">Account Number</label> -->
							<!-- <input type="text" class="form-control" id="account_no" placeholder="Account Number" v-model = "account_no"> -->
							
              				<!-- <select-account 
								v-model="account_no"
								:default-value="dataAccount"
								:defaultList = "accountList"
								@input = "input"
							>
							</select-account> -->

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
						<div class="col-2 form-group">
							<label for="date_issued">Date Issued</label>
							<input type="date" class="form-control" id="date_issued" v-model = "date_issued">
						</div>
						<div class="col-4 form-group">
							<label for="voucher_amount">Amount</label>
							<input type="text" class="form-control" id="voucher_amount" v-model = "voucher_amount">
						</div>
						<div class="col-4 form-group">
							<label for="payee">Payee</label>
							<input type="text" class="form-control" id="payee" v-model = "payee">
						</div>
						<div class="col-4 form-group">
							<label for="purpose">Purpose</label>
							<textarea class="form-control" id="purpose" v-model = "purpose"></textarea>
						</div>
		
						<div class="col-12 form-group">
							<button class="btn btn-success btn-block" @click="addNewSection">
								Add New Obligation
							</button>
						</div>
					</div>

					<div v-for="(section, index) in obligations" :key="index">
						<div class="row form-group">
							<div class="col-12 form-group">
								<hr>
								<span  v-if="index>0" class="btn btn-danger float-right" style="cursor:pointer" @click="deleteObligation(section.obligation_id,index)">
									X Remove Obligation
								</span>
								<h4>Obligation {{ index + 1}}</h4>
							</div>
							<div class="col-6 form-group">
								<label for="bank_name">Obligation Number</label>
								<input type="text" class="form-control mb-2" placeholder="00-00-000000" v-model="section.obligation_no">
							</div>							
							<div class="col-6 form-group">
								<label for="bank_name">Total Amount</label>
								<input type="text" class="form-control mb-2" placeholder="0.00" v-model="section.total_amount" disabled>
							</div>
							
							<div class="col-12">
								<h6>Obligation {{ index + 1}} Particulars</h6>
							</div>
						</div>
						<div class="row form-group" v-for="(item, iors) in section.items" :key="iors">
							<div class="col-4 form-group">
								<!-- <label for="obj_code">Object Code</label> -->
								<!-- <input type="text" name="obj_code" class="form-control mb-2" placeholder="Object Code" v-model="item.object_code"> -->
								<!-- <select-objectcode 
									v-model="item.object_code"
									:default-value="dataAccount"
									:defaultList = "objectList"
								>
								</select-objectcode> -->

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
								<textarea name="desc" class="form-control mb-2" v-model="item.description"></textarea>
							</div>
							<div class="col-4 form-group">
								<label for="obj_code">Amount</label>
								<input type="text" name="obj_code" class="form-control mb-2" placeholder="0.00" v-model="item.amount">
							</div>
							<div class="col-4 form-group">
								<!-- <label for="obj_code">Allocation</label>
								<input type="text" name="obj_code" class="form-control mb-2" v-model="item.allocation"> -->
								<!-- <select-allocation 
									v-model="item.allocation"
									:default-value="dataAccount"
									:defaultList = "allocationList"
								>
								</select-allocation> -->

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
								<span class="btn btn-danger float-right" style="cursor:pointer" @click="deleteTransaction(item.trans_id,index,iors)">
									X
								</span>
							</div>
						</div>
						<div class="col-12">
							<button  class="btn btn-success" @click="addNewItem(index)"> <!-- passing the index -->
								Add Particulars
							</button>
						</div>
					</div>

				</div>
			</template>

			<template slot="footer">
				<button type="button" class="btn btn-danger" @click = "closeModal">Cancel</button> 
				
				<div v-if="modalType == 'create'">
					<button type="button" class="btn btn-success" @click = "storeData">Save</button>
				</div>
				<div v-else>
					<button type="button" class="btn btn-primary" @click = "updateData" >Update</button>
				</div>
			</template>
		</CModal>
	</div>
</template>

<script>

	import axios from 'axios'
	import moment from 'moment'
	import SelectAccount from '../list/Accounts.vue'
	import SelectObjectCode from '../list/ObjectCodes.vue'
	import SelectAllocation from '../list/Allocations.vue'

  	export default {
		components: {
			'select-account': SelectAccount,
			'select-objectcode': SelectObjectCode,
			'select-allocation': SelectAllocation,
		},
		props: {
			title: {
				type: String, // not 'number'
				required: true,
				default: "Create Allotment"
			},
			objectList: {
				type: Array, // not 'number'
				required: true,
				default: () => ({})
			},
			allocationList: {
				type: Array, // not 'number'
				required: true,
				default: () => ({})
			},
			ppaList: {
				type: Array, // not 'number'
				required: true,
				default: () => ({})
			},
			accountList: {
				type: Array, // not 'number'
				required: true,
				default: () => ({})
			},
		},
		data () {
			return {
				validationErrors:"",
				closeOnBackdrop: false,
				warningModal: false,
				modalType: "create",
				
				dataAccount:"",
				objectData:"",
				allocationData:"",

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
							total_amount: '',
							description: '',
							amount: '',
							allocation: '',
							ppa: '',
						}]
					}
				]
			}
		},
		methods: {
			format_date(value,format){
				if (value) {
				return moment(String(value)).format(format);
				}
			},
			setValue: function(value) {
				this.modalType 		= value.modalType;
				this.warningModal 	= value.warningModal;
				this.voucher_id 	= value.voucher_id;
				this.check_no 		= value.check_no;
				this.voucher_no 	= value.voucher_no;
				this.account_no 	= this.accountList.find(option => option.AccountNumber === value.account_no);
				this.date_issued 	= value.date_issued;
				this.voucher_amount = value.voucher_amount;
				this.payee 			= value.payee;
				this.purpose 		= value.purpose;
				this.obligations 	= value.obligations;

				this.obligations.forEach(element => {
					element.items.forEach(ors => {
						ors.ppa = this.ppaList.find(option => option.PAPCode === ors.ppa);
						ors.object_code = this.objectList.find(option => option.AccountCode === ors.object_code);
						ors.allocation = this.allocationList.find(option => option.AllocationNo === ors.allocation);
					});
				});
				this.date_issued = this.format_date(this.date_issued,'yyyy-MM-DD');

			},
			closeModal(){
				this.warningModal = false;
				this.validationErrors = "";
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
				console.log(params);

				axios.post(  this.$apiAdress + '/api/vouchers/store?token=' + localStorage.getItem("api_token"),params)
				.then(function (response) {
					self.$emit('updated');
					self.validationErrors = "";
					self.warningModal = false;
					//self.$swal('Success', 'New Account has been added.', 'OK');
					self.$swal.fire({
						position: 'top-end',
						icon: 'success',
						title: 'New Voucher has been added.',
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
				axios.post(  this.$apiAdress + '/api/vouchers/update?token=' + localStorage.getItem("api_token"),params)
				.then(function (response) {
					self.$emit('updated');
					self.validationErrors = "";
					self.warningModal = false;
					//self.$swal('Success', 'Account has been updated.', 'OK');
					self.$swal.fire({
						position: 'top-end',
						icon: 'success',
						title: 'Account has been updated.',
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
			AccountnameWithLang({ AccountNumber, AccountName }) {
                return `${AccountNumber} - ${AccountName}`;
            },
			ObjectCodeName({ AccountCode, AccountTitle }) {
                return `${AccountCode} - ${AccountTitle}`;
            },
			PapCodeName({ PAPCode, PAPTitle }) {
                return `${PAPCode} - ${PAPTitle}`;
            }
		},
		created: function() {
			this.$parent.$on('create', this.setValue);
		},
		// watch: {
		// 	obligations(){
		// 		console.log(this.obligations);
		// 	}
		// }

  	}
</script>

<style scoped>
.modal_body {
  margin: 5px 5px 5px 5px !important;
}

</style>