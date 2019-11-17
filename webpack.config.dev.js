'use strict'

const webpack = require('webpack')
const { VueLoaderPlugin } = require('vue-loader')
const HtmlWebpackPlugin = require('html-webpack-plugin')
const path = require('path');

module.exports = {
  mode: 'development',
  watch: true,
  entry: {
    todo: './templates/js/todo.js',
    todos: './templates/js/todos.js',
  },

  devServer: {
    hot: true,
    watchOptions: {
      poll: true
    }
  },

  output: {
    path: path.resolve(__dirname, "web/js"),
    filename: "[name].js",
    publicPath: "/assets/",
  },
  module: {
    rules: [
      {
        test: /\.vue$/,
        use: 'vue-loader'
      },
      {
        test: /\.css$/,
        use: [
          'vue-style-loader',
          'css-loader'
        ]
      },
      {
        test: /\.styl(us)?$/,
        use: [
          'vue-style-loader',
          'css-loader',
          'stylus-loader'
        ]
      }
    ]
  },
  plugins: [
    new webpack.HotModuleReplacementPlugin(),
    new VueLoaderPlugin(),
  ]
}