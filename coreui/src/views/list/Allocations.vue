<template>
    <div>
        <label>Allocations</label>
        <multiselect
            v-model="value" 
            track-by="AllocationNo" 
            label="AllocationNo"
            placeholder=""
            :options="options"
            :allow-empty="false"
            :show-labels="false"
            :internalSearch="true"
        >
            <template slot="singleLabel" slot-scope="{ option }">
                {{ option.AllocationNo == 'all' ? '' : option.AllocationNo}}
            </template>
            <template slot="option" slot-scope="{ option }">
                <div class="whitespace-normal">
                    {{ option.AllocationNo == 'all' ? '' : option.AllocationNo}}
                </div>
            </template>
        </multiselect>

        <input type="hidden" name="allocation_no" :value="value.AllocationNo">
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
				default: () => ({})
			},
         },

        data () {
            return {
                allocation_no:"",
                options: [],
                value: [],
            }
        },

        methods: {
            getData(query) {
                if(this.defaultList.length > 0){                   
                    
                    this.options =this.defaultList;

                    if (this.defaultValue) {
                        this.value = this.options.find(option => option.AllocationNo === this.defaultValue);
                    }
                }else{
                    axios.get(`/api/library/allocations?token=` + localStorage.getItem("api_token"))
                    .then(response => {
                        this.options =response.data.allocations;

                        if (this.defaultValue) {
                            this.value = this.options.find(option => option.AllocationNo === this.defaultValue);
                        }
                    });
                }
            },
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
