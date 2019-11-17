<template>
    <div class="col-md-4 col-md-offset-4" v-if="isLoaded">
        <h1>Todo:</h1>
        <table class="table table-striped">
            <tbody>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Description</th>
                    <th></th>
                </tr>
                <tr>
                    <td># {{ todo.id }}</td>
                    <td>{{ todo.username }}</td>
                    <td>{{ todo.description }}</td>
                    <td>
                        <button v-on:click="deleteTodo(todo.id)" class="btn btn-xs btn-danger">
                            <span class="glyphicon glyphicon-remove glyphicon-white"></span>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
    import api from '../api/todo';
    import { isEmpty } from 'lodash';

    export default {
        computed: {
            isLoaded() {
                return !isEmpty(this.todo);
            },
        },
        methods: {
            deleteTodo(id) {
                api.delete(id).then(response => {
                    window.location.href = "/todo";
                });
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