const path = require('path');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const {CleanWebpackPlugin} = require('clean-webpack-plugin');
const webpack = require('webpack');
const TerserPlugin = require("terser-webpack-plugin");
var BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;


module.exports = {
    context: path.resolve(__dirname, 'resource'),
    mode: 'development',
    entry: {
        functionApi: './js/api_function.js',
        index: './js/index.js'
    },
    resolve: {
        extensions: ['.js', '.less'],
        alias: {
            '@models': path.resolve(__dirname, 'resource/js/modules'),
        }
    },
    output: {
        filename: "[name].bundle.js",
        path: path.resolve(__dirname, 'public/dist')
    },
    plugins: [
        new CleanWebpackPlugin(),
        new webpack.ProvidePlugin({
            $: 'jquery',
            jQuery: 'jquery',
        }),
        // new BundleAnalyzerPlugin({
        //     analyzerMode: 'server',
        //     generateStatsFile: true,
        //     statsOptions: { source: false }
        // }),
    ],
    optimization: {
        minimize: true,
        minimizer: [new TerserPlugin({
            terserOptions: {
                mangle: {
                    properties: {
                        regex: /(^P1|^p1|^_p1)[A-Z]\w*/
                    }
                },
                sourceMap: false,
                keep_fnames: false,
                toplevel: true,
            }
        })],
        splitChunks: {
            chunks: 'async',
            minSize: 2000,
            minRemainingSize: 0,
            minChunks: 1,
            maxAsyncRequests: 30,
            maxInitialRequests: 30,
            enforceSizeThreshold: 50000,
            cacheGroups: {
                vendor: {
                    test: /[\\/]node_modules[\\/]/,
                    priority: -10,
                    reuseExistingChunk: true,
                },
                default: {
                    minChunks: 2,
                    priority: -20,
                    reuseExistingChunk: true,
                },
            },
        },
    },
    module: {

        rules: [
            {
                test: /\.less$/i,
                use: [
                    "style-loader",
                    "css-loader",
                    {
                        loader: "less-loader",
                        options: {
                            additionalData: `@env: ${process.env.NODE_ENV};`,
                        }
                    }
                ]
            },
            {
                test: /\.s[ac]ss$/i,
                use: [
                    // Creates `style` nodes from JS strings
                    "style-loader",
                    // Translates CSS into CommonJS
                    "css-loader",
                    // Compiles Sass to CSS
                    "sass-loader",
                ],
            }
        ]
    }
};