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
                    @updated="loadData"
                />
            </tbody>
        </table>
        <div>
            <a href="/todo" class="btn btn-sm btn-primary">Go back</a>
        </div>
    </div>
</template>
<script>
    import api from '../api/todo';
    import { isEmpty } from 'lodash';
    import TodoRow from './TodoRow.vue';

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
            deleted(id) {
                window.location.href = "/todo";
            },
            loadData() {
                api.show(this.id).then(response => {
                    this.todo = response.data
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