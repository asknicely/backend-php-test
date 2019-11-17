<template>
    <div class="col-md-4 col-md-offset-4" v-if="isLoaded">
        <h1>Todo:</h1>
        <table class="table table-striped">
            <tbody>
                <tr>
                    <th>#</th>
                    <th>Completed</th>
                    <th>User</th>
                    <th>Description</th>
                    <th></th>
                </tr>
                <todo-row
                    v-if="isLoaded"
                    :todo="todo"
                    @deleted="deleted"
                    @updated="updated"
                />
            </tbody>
        </table>
        <div>
            <a href="/todo" class="btn btn-sm btn-primary">Go back</a>
        </div>
    </div>
</template>
<script>
    import Vue from 'vue';
    import VueToast from 'vue-toast-notification';
    import 'vue-toast-notification/dist/index.css';
    import api from '../api/todo';
    import { isEmpty } from 'lodash';
    import TodoRow from './TodoRow.vue';
    Vue.use(VueToast);

    export default {
        components: {
            TodoRow,
        },
        computed: {
            isLoaded() {
                return !isEmpty(this.todo);
            },
        },
        methods: {
            deleted() {
                Vue.$toast.open('A todo has been deleted');
                window.location.href = "/todo";
            },
            updated() {
                Vue.$toast.open('A todo has been updated');
            },
            loadData() {
                api.show(this.id).then(response => {
                    this.todo = response.data
                }).catch(response => {
                    Vue.$toast.error('Unable to load the data');
                });
            },
            getIdfromUrl() {
                return window.location.pathname.split('/')[2];
            },
        },

        data() {
            return {
                id: null,
                todo: null,
                description: '',
            }
        },

        mounted() {
            this.id = this.getIdfromUrl();
            if (this.id > 0) {
                this.loadData();
            }
        },
    }
</script>