import Vue from 'vue';
import Todo from './components/Todo.vue';
import VueLodash from 'vue-lodash'

const options = { name: 'lodash' }

Vue.use(VueLodash, options)

if (document.getElementById('todo')) {
    new Vue({
        el: '#todo',
        render: h => h(Todo)
    });
}