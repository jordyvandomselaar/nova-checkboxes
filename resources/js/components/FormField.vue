<template>
    <default-field :field="field">
        <template slot="field">
            <ul class="list-reset" v-if="field.options.length > 0">
                <li class="flex items-center" v-for="option in field.options">
                    <checkbox
                        class="py-2 mr-4"
                        @input="handleChange(option.value)"
                        :id="field.name"
                        :name="field.name"
                        :checked="options[option.value]"
                    ></checkbox>
                    <label
                        :for="field.name"
                        v-text="option.label"
                        @click="handleChange(option.value)"
                    ></label>
                </li>
            </ul>

            <slot v-else></slot>

            <p v-if="hasError" class="my-2 text-danger">
                {{ firstError }}
            </p>
        </template>
    </default-field>
</template>

<script>
    import {FormField, HandlesValidationErrors} from 'laravel-nova'

    export default {
        mixins: [FormField, HandlesValidationErrors],

        data: () => ({
            value: '',
            options: []
        }),

        props: ['resourceName', 'resourceId', 'field'],

        computed:{
            optionValues(){
                return this.field.options
                    .map(o => o.value)
                    .reduce((o, key) => ({ ...o, [key]: this.value.includes(key)}), {})
            }
        },

        methods: {
            setInitialValue() {
                this.value = this.field.value || '';
                this.$nextTick(() => {
                    const options = (this.value)
                        ? JSON.parse(this.value)
                        : [];
                    this.options = options;

                    Nova.$emit(`${this.field.attribute}-default-options`, {value: options});
                });
            },

            fill(formData) {
                formData.append(this.field.attribute, this.value || '')
            },

            handleChange(key) {
                this.$set(this.options, key, ! this.options[key])
            }
        },

        watch: {
            'options' : {
                handler: function (newOptions) {
                    const newValue =JSON.stringify(newOptions);
                    this.value =  newValue;
                    Nova.$emit(`${this.field.attribute}-change`, {value: newOptions});
                    this.$emit("change", newValue);
                },
                deep: true
            },
        }
    }
</script>
