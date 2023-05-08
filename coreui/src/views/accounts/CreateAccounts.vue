<template>
	<div>

		<CModal
		:title="title"
		color="primary"
		:show.sync="warningModal"
		:closeOnBackdrop = "closeOnBackdrop"
		>
			<template slot="body-wrapper">
				<div v-if="validationErrors">
					<ul class="alert alert-danger">
						<li v-for="(value, key, index) in validationErrors" v-bind:key="(value, key, index)">@{{ value }}</li>
					</ul>
				</div>

				<div class="card-body">
				<div class = "row">
					<div class = "col-12">
						<div class="form-group">
							<label for="account_number">Account Number</label>
							<input type="text" class="form-control" id="account_number" placeholder="0000-0000-00" v-model = "account_number">
						</div>
						<div class="form-group">
							<label for="account_name">Account Name</label>
							<input type="text" class="form-control" id="account_name" placeholder="Account Name" v-model = "account_name">
						</div>
						<div class="form-group">
							<label for="bank_name">Bank Name</label>
							<input type="text" class="form-control" id="bank_name" placeholder="Bank Name" v-model = "bank_name">
						</div>
					</div>
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

<script>

	import axios from 'axios'

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
				default: "Create Allotment"
			},
			today: {
				default: ""
			}
		},
		data () {
			return {
				closeOnBackdrop:false,
				warningModal: false,
				account_id: 0,
				account_number: "0000-0000-00",
				account_name: "",
				bank_name: "",
				validationErrors:""
			}
		},
		methods: {
			setValue: function(value) {
				this.warningModal = value.warningModal;
				this.account_id = value.account_id;
				this.account_number = value.account_number;
				this.account_name = value.account_name;
				this.bank_name = value.bank_name;
			},
			closeModal(){
				this.warningModal = false;
				this.validationErrors = "";
			},
			storeAccount() {
				let self = this;
				var params = {
					AccountNumber: this.account_number,
					AccountName : this.account_name,
					BankName : this.bank_name,
				}

				axios.post(  this.$apiAdress + '/api/accounts/store?token=' + localStorage.getItem("api_token"),params)
				.then(function (response) {
					self.$emit('updated');
					self.validationErrors = "";
					self.warningModal = false;
					//self.$swal('Success', 'New Account has been added.', 'OK');
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
					account_id: this.account_id,
					AccountNumber: this.account_number,
					AccountName : this.account_name,
					BankName : this.bank_name,
				}

				axios.post(  this.$apiAdress + '/api/accounts/update?token=' + localStorage.getItem("api_token"),params)
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
			}
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