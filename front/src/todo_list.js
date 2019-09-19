// import React from 'react';

// import TodoItem from "./todo_item";

const ID = "todos_list";

class TodoList extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            todos: [],
            toast: null,
        };
    }

    componentDidMount() {
        getTodos().then((todos) => {
            this.setState({
                todos: todos
            })
        }).catch((err) => {
            this.displayToast("Error while fetching todos");
        });
    }

    deleteTodo(id) {
        deleteTodo(id).then((data) => data.json()).then((data) => {
            // Remove this todo from local state
            this.setState({
                todos: this.state.todos.filter(item => item.id !== id)
            });

            this.displayToast("Todo deleted");

        }).catch((err) => {
            this.displayToast("Error while removing todo");
        });
    }

    displayToast(message) {
        this.setState({
            toast: message,
        });

        setTimeout(() => {
            this.setState({
                toast: null,
            });
        }, 3000);
    }

    _renderToast() {
        if (!this.state.toast) {
            return false;
        }
        return (
            <div className="alert alert-warning" role="alert">
                {this.state.toast}
            </div>
        );
    }

    render() {
        return (
            <div>
                <table className="table table-striped">
                    <tbody>
                    <th>#</th>
                    <th>User</th>
                    <th>Description</th>
                    <th>Is Done</th>
                    <th>Delete</th>


                    {
                        this.state.todos.map((item) => {
                            return (
                                <TodoItem
                                    item={item}
                                    onDelete={(id) => {
                                        this.deleteTodo(id);
                                    }}
                                />
                            );
                        })
                    }

                    </tbody>
                </table>

                {this._renderToast()}

            </div>

        );
    }
}

let domContainer = document.querySelector('#' + ID);
ReactDOM.render(<TodoList/>, domContainer);