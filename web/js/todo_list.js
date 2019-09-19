var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

// import React from 'react';

var ID = "todos_list";

var TodoList = function (_React$Component) {
    _inherits(TodoList, _React$Component);

    function TodoList(props) {
        _classCallCheck(this, TodoList);

        var _this = _possibleConstructorReturn(this, (TodoList.__proto__ || Object.getPrototypeOf(TodoList)).call(this, props));

        _this.state = {
            todos: []
        };
        return _this;
    }

    _createClass(TodoList, [{
        key: "componentDidMount",
        value: function componentDidMount() {
            var _this2 = this;

            fetch("/api/todos").then(function (data) {
                return data.json();
            }).then(function (data) {
                console.log(data);
                _this2.setState({
                    todos: data
                });
            }).catch(function (err) {
                console.log(err);
            });
        }
    }, {
        key: "render",
        value: function render() {
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
                        React.createElement("th", null),
                        this.state.todos.map(function (item) {
                            var id = item.id,
                                description = item.description,
                                user_id = item.user_id,
                                is_completed = item.is_completed;

                            return React.createElement(
                                "tr",
                                { key: id },
                                React.createElement(
                                    "td",
                                    null,
                                    id
                                ),
                                React.createElement(
                                    "td",
                                    null,
                                    user_id
                                ),
                                React.createElement(
                                    "td",
                                    null,
                                    React.createElement(
                                        "a",
                                        { href: "/todo/" + id },
                                        description
                                    )
                                ),
                                React.createElement(
                                    "td",
                                    null,
                                    React.createElement("input", {
                                        type: "checkbox",
                                        onChange: function onChange() {},
                                        checked: is_completed == 1
                                    })
                                ),
                                React.createElement(
                                    "td",
                                    null,
                                    React.createElement(
                                        "button",
                                        { type: "submit", className: "btn btn-xs btn-danger" },
                                        React.createElement("span", {
                                            className: "glyphicon glyphicon-remove glyphicon-white" })
                                    )
                                )
                            );
                        })
                    )
                )
            );
        }
    }]);

    return TodoList;
}(React.Component);

var domContainer = document.querySelector('#' + ID);
ReactDOM.render(React.createElement(TodoList, null), domContainer);