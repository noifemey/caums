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
							<label for="pap_code">PAP Code</label>
							<input type="text" class="form-control" id="pap_code" placeholder="PAP Code" v-model = "pap_code">
						</div>
						<div class="form-group">
							<label for="pap_title">PAP Title</label>
							<input type="text" class="form-control" id="pap_title" placeholder="PAP Title" v-model = "pap_title">
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
				default: "Create PAP"
			},
			today: {
				default: ""
			}
		},
		data () {
			return {
				closeOnBackdrop:false,
				warningModal: false,
				papcode_id: 0,
				pap_code: "",
				pap_title: "",
				validationErrors:""
			}
		},
		methods: {
			setValue: function(value) {
				this.warningModal = value.warningModal;
				this.papcode_id = value.papcode_id;
				this.pap_code = value.pap_code;
				this.pap_title = value.pap_title;
			},
			closeModal(){
				this.warningModal = false;
				this.validationErrors = "";
			},
			storeData() {
				let self = this;
				var params = {
					PAPCode:   this.pap_code,
					PAPTitle : this.pap_title,
				}

				axios.post(  this.$apiAdress + '/api/papcodes/store?token=' + localStorage.getItem("api_token"),params)
				.then(function (response) {
					self.$emit('updated');
					self.validationErrors = "";
					self.warningModal = false;
					self.$swal.fire({
						position: 'top-end',
						icon: 'success',
						title: 'New PAP Code has been added.',
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
					papcode_id: this.papcode_id,
					PAPCode: this.pap_code,
					PAPTitle : this.pap_title,
				}

				axios.post(  this.$apiAdress + '/api/papcodes/update?token=' + localStorage.getItem("api_token"),params)
				.then(function (response) {
					self.$emit('updated');
					self.validationErrors = "";
					self.warningModal = false;
					self.$swal.fire({
						position: 'top-end',
						icon: 'success',
						title: 'PAP code has been updated.',
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