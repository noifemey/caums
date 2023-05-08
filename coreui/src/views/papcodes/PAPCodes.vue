<template>
  <CRow>
    <CCol col="12" xl="12">
      <transition name="slide">
      <CCard>
        <CCardHeader>
           <h4>PAP Codes Data Entry</h4> 
        </CCardHeader>
        <CCardBody>
          <div class="row">
            <div class="col-3">
              <button type="button" class="btn btn-dark btn-lg btn-block" @click = "modalCreateShow"> Create</button>
            </div>
            <div class="col-3">
              <button type="button" class="btn btn-dark btn-lg btn-block" @click = "generateReport"> Download </button>
            </div>
          </div>

          <v-client-table :data="table.items" :columns="table.columns" :options="table.options">
            <template slot="pap_code" slot-scope="props">
                {{props.row.PAPCode}}
            </template>
            <template slot="pap_title" slot-scope="props">
                {{props.row.PAPTitle}}
            </template>
            <template slot="show" slot-scope="props">
                <CButton color="primary" @click="showData(props.row)">Show</CButton>
            </template>
            <template slot="edit" slot-scope="props">
                <CButton color="primary" @click = "modalEditShow(props.row)">Edit</CButton>
            </template>
            <template slot="delete" slot-scope="props">
                <CButton color="danger" @click="deleteData( props.row.id )">Delete</CButton>
            </template>
          </v-client-table>

                
          <create-papcodes
            modalType="create"
            title = "Create PAP Code"
            @updated = "getData"
          >
          </create-papcodes>

          <create-papcodes
            modalType="edit"
            title = "Edit PAP Code"
            @updated = "getData"
          >
          </create-papcodes>

        </CCardBody>
      </CCard>
      </transition>
    </CCol>
  </CRow>
</template>

<script>
import axios from 'axios'
import CreateAccountCodes from './CreatePAPCodes.vue';

export default {
  name: 'Users',
  components: {
    'create-papcodes': CreateAccountCodes
  },
  data: () => {
    return {
      you: null,
      editValue : {
        warningModal: true,
				papcode_id: 0,
				pap_code: "0000-0000-00",
				pap_title: ""
      },
      table : {
        columns: ['pap_code', 'pap_title', 'show', 'edit', 'delete'],
        items: [],
        options: {
            filterable: ['PAPCode', 'PAPTitle'],
        }
      }
    }
  },
  methods: {
    modalCreateShow(){
      console.log("Create Show");
      
      this.editValue.warningModal = true;
      this.editValue.papcode_id = 0;
      this.editValue.pap_code = "";
      this.editValue.pap_title = "";

      this.$emit('create', this.editValue);
    },
    modalEditShow(props){
      console.log("Edit Show");
      this.editValue.warningModal = true;
      this.editValue.papcode_id = props.id;
      this.editValue.pap_code = props.PAPCode;
      this.editValue.pap_title = props.PAPTitle;

      this.$emit('edit', this.editValue);
    },
    showData(props) {
      this.$swal.fire({
      title: '<strong>FUND PAP DETAILS</strong>',
      icon: 'info',
      html:
        '<strong>PAP Code: </strong>' +   props.PAPCode + "<br>" +
        '<strong>PAP Title: </strong>' +  props.PAPTitle + "<br>",
        showCloseButton: true,
        showCancelButton: true,
        focusConfirm: false
      })
    },
    deleteData ( id ) {
      let self = this;
      this.$swal.fire({
        title: 'Are you Sure you want to delete this data?',
        showDenyButton: false,
        showCancelButton: true,
        confirmButtonText: `Delete`,
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
          axios.get(  this.$apiAdress + '/api/papcodes/delete/' + id + '?token=' + localStorage.getItem("api_token"))
          .then(function (response) {
              self.$swal.fire({
                position: 'top-end',
                icon: 'success',
                title: 'Successfully deleted data.',
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
    getData (){
      let self = this;
      axios.get(  this.$apiAdress + '/api/papcodes?token=' + localStorage.getItem("api_token"))
      .then(function (response) {
        self.table.items = response.data.papcodes;
        self.you = response.data.you;
      }).catch(function (error) {
        console.log(error);
        self.$router.push({ path: '/login' });
      });
    },
    generateReport () {
      let self = this;
      self.$swal.fire({
        title: 'Please Wait !',
        html: 'Downloading..',// add html attribute if you want or remove
        allowOutsideClick: false,
        showConfirmButton:false,
        onBeforeOpen: () => {
            self.$swal.showLoading()
        },
      });

      axios.get(  this.$apiAdress + '/api/papcodes/generate-pdf/?token=' + localStorage.getItem("api_token"), {
          responseType: 'blob', // had to add this one here
      })
      .then(function (response) {
          console.log(response);
          var fileURL = window.URL.createObjectURL(new Blob([response.data]));
          var fileLink = document.createElement('a');

          fileLink.href = fileURL;
          fileLink.setAttribute('download', 'file.pdf');
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
  mounted: function(){
    this.getData();
  }
}
</script>
