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
                <tr v-for="(todo, index) in todos">
                    <td># {{ index + 1 }}</td>
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
                        <button v-on:click="addTodo()" class="btn btn-sm btn-primary">Add</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
    import axios from "axios";

    export default {
        methods: {
            deleteTodo(id) {
                axios.delete("api/v1/todo/" + id).then(response => {
                    this.loadTodos();
                });
            },
            addTodo() {
                axios.post("api/v1/todo/add", {
                    description: this.description,
                }).then(response => {
                    this.description = '';
                    this.loadTodos();
                });
            },
            loadTodos() {
                axios.get("/api/v1/todo")
                    .then(response => {
                        this.todos = response.data
                    });
            }
        },

        data() {
            return {
                description: '',
                todos: []
            }
        },

        mounted() {
            this.loadTodos();
        },
    }
</script>