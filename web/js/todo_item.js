var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

// import React from 'react';

/**
 * props:
 *
 * {
 *     item: {
 *         id,
 *         description,
 *         user_id,
 *         is_completed
 *     },
 *     onDelete: Function(id)
 * }
 */
var TodoItem = function (_React$Component) {
    _inherits(TodoItem, _React$Component);

    function TodoItem(props) {
        _classCallCheck(this, TodoItem);

        var _this = _possibleConstructorReturn(this, (TodoItem.__proto__ || Object.getPrototypeOf(TodoItem)).call(this, props));

        _this.state = {
            is_completed: _this.props.item.is_completed == 1 ? true : false
        };
        return _this;
    }

    _createClass(TodoItem, [{
        key: "changeCompleteStatus",
        value: function changeCompleteStatus(is_completed) {
            var id = this.props.item.id;


            changeTodoStatus(id, is_completed);
        }
    }, {
        key: "render",
        value: function render() {
            var _this2 = this;

            var _props$item = this.props.item,
                id = _props$item.id,
                description = _props$item.description,
                user_id = _props$item.user_id;


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
                        onChange: function onChange() {
                            _this2.setState(function (prevState) {
                                var is_completed = !prevState.is_completed;

                                _this2.changeCompleteStatus(is_completed);

                                return {
                                    is_completed: is_completed
                                };
                            });
                        },
                        checked: this.state.is_completed
                    })
                ),
                React.createElement(
                    "td",
                    null,
                    React.createElement(
                        "button",
                        {
                            type: "submit",
                            className: "btn btn-xs btn-danger",
                            onClick: function onClick() {
                                _this2.props.onDelete(id);
                            }
                        },
                        React.createElement("span", { className: "glyphicon glyphicon-remove glyphicon-white" })
                    )
                )
            );
        }
    }]);

    return TodoItem;
}(React.Component);