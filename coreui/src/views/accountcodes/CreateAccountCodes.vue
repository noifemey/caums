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
							<label for="account_code">Account Code</label>
							<input type="text" class="form-control" id="account_code" placeholder="Account Code" v-model = "account_code">
						</div>
						<div class="form-group">
							<label for="account_title">Account Title</label>
							<input type="text" class="form-control" id="account_title" placeholder="Account Title" v-model = "account_title">
						</div>
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
				closeOnBackdrop: false,
				warningModal: false,
				accountcode_id: 0,
				account_code: "",
				account_title: "",
				validationErrors:""
			}
		},
		methods: {
			setValue: function(value) {
				this.warningModal = value.warningModal;
				this.accountcode_id = value.accountcode_id;
				this.account_code = value.account_code;
				this.account_title = value.account_title;
			},
			closeModal(){
				this.warningModal = false;
				this.validationErrors = "";
			},
			storeData() {
				let self = this;
				var params = {
					AccountCode: this.account_code,
					AccountTitle : this.account_title,
				}

				axios.post(  this.$apiAdress + '/api/accountcodes/store?token=' + localStorage.getItem("api_token"),params)
				.then(function (response) {
					self.$emit('updated');
					self.validationErrors = "";
					self.warningModal = false;
					self.$swal.fire({
						position: 'top-end',
						icon: 'success',
						title: 'New Account Code has been added.',
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
					accountcode_id: this.accountcode_id,
					AccountCode: this.account_code,
					AccountTitle : this.account_title,
				}

				axios.post(  this.$apiAdress + '/api/accountcodes/update?token=' + localStorage.getItem("api_token"),params)
				.then(function (response) {
					self.$emit('updated');
					self.validationErrors = "";
					self.warningModal = false;
					self.$swal.fire({
						position: 'top-end',
						icon: 'success',
						title: 'Account code has been updated.',
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