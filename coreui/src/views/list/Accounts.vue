<template>
    <div>
        <label>Accounts</label>
        <multiselect
            v-model="value" 
            track-by="AccountName" 
            label="AccountName"
            :custom-label="nameWithLang"
            placeholder=""
            :options="options"
            :allow-empty="false"
            :show-labels="false"
            :internalSearch="true"
        >
            <template slot="singleLabel" slot-scope="{ option }">
                {{ option.AccountNumber == 'all' ? '' : option.AccountNumber + ' - ' }}{{ option.AccountName }}
            </template>
            <template slot="option" slot-scope="{ option }">
                <div class="whitespace-normal">
                    {{ option.AccountNumber == 'all' ? '' : option.AccountNumber + ' - ' }}{{ option.AccountName }}
                </div>
            </template>
        </multiselect>

        <input type="hidden" name="account_code" :value="value.AccountNumber">
    </div>
</template>

<script>

    import axios from 'axios'

    export default {
        props:{
			defaultValue: {
				type: String, // not 'string'
				required: true,
				default: "create"
			},
			defaultList: {
				type: Array, // not 'string'
				default: () => []
			},
         },

        data () {
            return {
                account_code:"",
                options: [],
                value: [],
            }
        },

        methods: {
            getData(query) {
                if(this.defaultList.length > 0){                   
                    
                    this.options =this.defaultList;

                    if (this.defaultValue) {
                        this.value = this.options.find(option => option.AccountNumber === this.defaultValue);
                    }
                }else{
                    axios.get(`/api/library/accounts?token=` + localStorage.getItem("api_token"))
                    .then(response => {
                        // let all = [{
                        //     id: 'all',
                        //     name: 'All',
                        //     code: 'all',
                        //     status: '',
                        // }]

                        //this.options = all.concat(response.data.data);
                        this.options =response.data.accounts;

                        if (this.defaultValue) {
                            this.value = this.options.find(option => option.AccountNumber === this.defaultValue);
                        }
                    });

                }
            },

            nameWithLang ({ AccountNumber, AccountName }) {
                return `${AccountNumber} - ${AccountName}`;
            },
			setValue: function(value) {
                console.log("adsadadas");
                this.value = this.options.find(option => option.AccountNumber === value);
			},
        },
        mounted () {
            this.getData();
        },
		created: function() {
			this.$parent.$on('setAccountValue', this.setValue);
		},
        watch: {
            value() {
                this.$emit('input', this.value);
            }
        }
    }
</script>

<style>
    .multiselect__single {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>