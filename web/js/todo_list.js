var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

// import React from 'react';

var DOM_ID = "todos_list";

var TodoList = function (_React$Component) {
    _inherits(TodoList, _React$Component);

    function TodoList(props) {
        _classCallCheck(this, TodoList);

        var _this = _possibleConstructorReturn(this, (TodoList.__proto__ || Object.getPrototypeOf(TodoList)).call(this, props));

        _this.state = {
            todos: [],
            toast: null
        };
        return _this;
    }

    _createClass(TodoList, [{
        key: "componentDidMount",
        value: function componentDidMount() {
            this.getAllTodos();
        }
    }, {
        key: "getAllTodos",
        value: function getAllTodos() {
            var _this2 = this;

            getTodos().then(function (todos) {
                _this2.setState({
                    todos: todos
                });
            }).catch(function (err) {
                _this2.displayToast("Error while fetching todos");
            });
        }
    }, {
        key: "deleteTodo",
        value: function (_deleteTodo) {
            function deleteTodo(_x) {
                return _deleteTodo.apply(this, arguments);
            }

            deleteTodo.toString = function () {
                return _deleteTodo.toString();
            };

            return deleteTodo;
        }(function (id) {
            var _this3 = this;

            deleteTodo(id).then(function (data) {
                return data.json();
            }).then(function (data) {
                // Remove this todo from local state
                _this3.setState({
                    todos: _this3.state.todos.filter(function (item) {
                        return item.id !== id;
                    })
                });

                _this3.displayToast("Todo deleted");
            }).catch(function (err) {
                _this3.displayToast("Error while removing todo");
            });
        })
    }, {
        key: "displayToast",
        value: function displayToast(message) {
            var _this4 = this;

            this.setState({
                toast: message
            });

            setTimeout(function () {
                _this4.setState({
                    toast: null
                });
            }, 3000);
        }
    }, {
        key: "_renderToast",
        value: function _renderToast() {
            if (!this.state.toast) {
                return false;
            }
            return React.createElement(
                "div",
                { className: "alert alert-warning", role: "alert" },
                this.state.toast
            );
        }
    }, {
        key: "render",
        value: function render() {
            var _this5 = this;

            return React.createElement(
                "div",
                null,
                React.createElement(
                    "table",
                    { className: "table table-striped" },
                    React.createElement(
                        "tbody",
                        null,
                        React.createElement(
                            "th",
                            null,
                            "#"
                        ),
                        React.createElement(
                            "th",
                            null,
                            "User"
                        ),
                        React.createElement(
                            "th",
                            null,
                            "Description"
                        ),
                        React.createElement(
                            "th",
                            null,
                            "Is Done"
                        ),
                        React.createElement(
                            "th",
                            null,
                            "Delete"
                        ),
                        this.state.todos.map(function (item) {
                            return React.createElement(TodoItem, {
                                item: item,
                                onDelete: function onDelete(id) {
                                    _this5.deleteTodo(id);
                                }
                            });
                        })
                    )
                ),
                React.createElement(TodoAdd, {
                    onAdd: function onAdd(text) {
                        createNewTodo(text).then(function (data) {
                            _this5.displayToast(data.status);
                            _this5.getAllTodos();
                        }).catch(function (err) {
                            _this5.displayToast("Error creating a new todo");
                        });
                    },
                    onInvalidInput: function onInvalidInput() {
                        _this5.displayToast("Can't create empty todo");
                    }
                }),
                this._renderToast()
            );
        }
    }]);

    return TodoList;
}(React.Component);

var domContainer = document.querySelector('#' + DOM_ID);
ReactDOM.render(React.createElement(TodoList, null), domContainer);