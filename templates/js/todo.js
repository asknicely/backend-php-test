import Vue from 'vue';
import Todo from './components/Todo.vue';

new Vue({
    el: 'todo',
    render: h => h(Todo)
});