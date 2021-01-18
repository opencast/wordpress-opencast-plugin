const path = require('path');
const webpack = require('webpack');
const assetsPath = path.resolve(__dirname, "assets");
const srcPath = path.resolve(__dirname, "src");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

module.exports = {
    entry: {
        "opencast-base": [assetsPath + '/javascript/opencast-base.js', assetsPath + '/stylesheet/opencast-base.scss'],
        "inlines/episodes": assetsPath + '/javascript/inlines/episodes.js',
        "inlines/single-episode": assetsPath + '/javascript/inlines/single-episode.js',
        "inlines/upload-video": assetsPath + '/javascript/inlines/upload-video.js',
    },
    output: {
        filename: 'js/[name].js',
        path: srcPath,
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-env']
                    }
                }
            },
            {
                test: /\.scss$/,
                use: [
                    {
                        loader: MiniCssExtractPlugin.loader
                    },
                    {
                        loader: "css-loader",
                        options: {
                            importLoaders: 2
                        }
                    },
                    {
                        loader: 'resolve-url-loader'
                    },
                    {
                        loader: 'sass-loader'
                    }
                ]
            },
            { 
                test: /\.(png|woff|woff2|eot|ttf|svg)$/,
                loader: 'file-loader',
                options: {
                    name: '[name].[ext]?[contenthash]',
                    outputPath: 'fonts',
                    publicPath: '../fonts',
                },
            },
        ],
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: 'css/[name].css',
            allChunks: true,
        }),
        
    ]
};