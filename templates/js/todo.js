import Vue from 'vue';
import Todo from './components/Todo.vue';

if (document.getElementById('todo')) {
    new Vue({
        el: '#todo',
        render: h => h(Todo)
    });
}