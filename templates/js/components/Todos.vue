<template>
    <div class="col-md-4 col-md-offset-4">
        <h1>Todo List:</h1>
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
                    v-for="todo in todos"
                    :todo="todo"
                    :key="todo.id"
                    @deleted="loadTodos"
                    @updated="loadTodos"
                />
                <tr>
                    <td colspan="4">
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
    import TodoRow from './TodoRow.vue';
    import api from '../api/todo';
    import { isEmpty } from 'lodash';

    export default {
        components: {
            TodoRow,
        },
        computed: {
            isDescriptionEmpty() {
                return isEmpty(this.description);
            }
        },
        methods: {
            isCompleted(value) {
                console.log(value);
                return false;
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