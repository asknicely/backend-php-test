<template>
    <div class="col-md-4 col-md-offset-4">
        <h1>Todo List:</h1>
        <table class="table table-striped">
            <tbody>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Description</th>
                    <th></th>
                </tr>
                <tr v-for="todo in todos">
                    <td># {{ todo.id }}</td>
                    <td>{{ todo.username }}</td>
                    <td>
                        <a :href="'/todo/' + todo.id">
                            {{ todo.description }}
                        </a>
                    </td>
                    <td>
                        <button v-on:click="deleteTodo(todo.id)" class="btn btn-xs btn-danger">
                            <span class="glyphicon glyphicon-remove glyphicon-white"></span>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <input v-model="description" placeholder="Description..." class="small-6 small-center">
                    </td>
                    <td>
                        <button v-on:click="addTodo()" :disabled="isDescriptionEmpty" class="btn btn-sm btn-primary">Add</button>
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
            isDescriptionEmpty() {
                return isEmpty(this.description);
            }
        },
        methods: {
            deleteTodo(id) {
                api.delete(id).then(response => {
                    this.loadTodos();
                });
            },
            addTodo() {
                const data = {
                    description: this.description,
                };

                api.store(data).then(response => {
                    this.description = '';
                    this.loadTodos();
                });
            },
            loadTodos() {
                api.all().then(response => {
                    this.todos = response.data
                });
            }
        },

        data() {
            return {
                todos: [],
                description: '',
            }
        },

        mounted() {
            this.loadTodos();
        },
    }
</script>