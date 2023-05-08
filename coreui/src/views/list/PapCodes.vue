<template>
    <div>
        <label>P.P.A Codes</label>
        <multiselect
            v-model="value" 
            track-by="PAPTitle" 
            label="PAPTitle"
            :custom-label="nameWithLang"
            placeholder=""
            :options="options"
            :allow-empty="false"
            :show-labels="false"
            :internalSearch="true"
        >
            <template slot="singleLabel" slot-scope="{ option }">
                {{ option.PAPCode == 'all' ? '' : option.PAPCode + ' - ' }}{{ option.PAPCode }}
            </template>
            <template slot="option" slot-scope="{ option }">
                <div class="whitespace-normal">
                    {{ option.PAPCode == 'all' ? '' : option.PAPCode + ' - ' }}{{ option.PAPCode }}
                </div>
            </template>
        </multiselect>

        <input type="hidden" name="account_code" :value="value.PAPCode">
    </div>
</template>

<script>

    import axios from 'axios'

    export default {
        props:{
			defaultValue: {
				type: String, // not 'string'
				required: true,
				default: ""
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
                        this.value = this.options.find(option => option.PAPCode === this.defaultValue);
                    }
                }else{
                    axios.get(`/api/library/papcodes?token=` + localStorage.getItem("api_token"))
                    .then(response => {
                        this.options =response.data.papcodes;

                        if (this.defaultValue) {
                            this.value = this.options.find(option => option.PAPCode === this.defaultValue);
                        }
                    });

                }
            },

            nameWithLang ({ PAPCode, PAPTitle }) {
                return `${PAPCode} - ${PAPTitle}`;
            }
        },

        mounted () {
            this.getData();
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
