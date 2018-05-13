import path from 'path';
import HtmlWebpackPlugin from 'html-webpack-plugin';
import ExtractTextPlugin from 'extract-text-webpack-plugin';
import webpack from 'webpack';

const ASSET_OUTPUT_PATH = '../assets/webapp';

/** removes falsy items from array */
const array = (...target) => target.filter((item) => item);

const createStyleLoader = (dev, extractTextPlugin, ...loaders) => dev
    ? ['style-loader'].concat(loaders)
    : extractTextPlugin.extract({
        fallback: 'style-loader',
        use: loaders,
    });

const wrapConfig = (config) => (env, {mode}) => config(mode === 'development');

const libStyle = new ExtractTextPlugin('include.css');
const ownStyle = new ExtractTextPlugin('style.[chunkhash].css');

export default wrapConfig((dev) => ({
    entry: array(
        dev && 'react-hot-loader/patch',
        'babel-polyfill',
        './frontend/',
    ),
    output: {
        path: path.resolve(__dirname, 'www/frontend'),
        publicPath: '/',
        filename: dev ? '[name].js' : '[name].[chunkhash].js',
    },
    plugins: array(
        new HtmlWebpackPlugin({
            template: 'frontend/index.html',
            inject: true,
        }),
        new webpack.DefinePlugin({
            'process.env.NODE_ENV': JSON.stringify(dev ? 'development' : 'production'),
        }),
        dev && new webpack.HotModuleReplacementPlugin(),
        !dev && libStyle,
        !dev && ownStyle,
    ),
    module: {
        rules: [
            {
                test: /\.js$/,
                loader: 'babel-loader',
                exclude: /node_modules/,
                options: {
                    presets: [
                        ['es2015', {modules: false}],
                        'react',
                        'stage-1',
                    ],
                    cacheDirectory: true,
                },
            },
            {
                test: /\.(less|css)$/,
                include: /node_modules/,
                loader: createStyleLoader(dev, libStyle, 'css-loader', 'less-loader'),
            },
            {
                test: /\.less$/,
                include: /frontend/,
                exclude: /include.less/,
                loader: createStyleLoader(dev, ownStyle, {
                        loader: 'css-loader',
                        query: {
                            modules: true,
                            localIdentName: '[name]__[local]__[hash:base64:5]',
                        },
                    },
                    'less-loader',
                ),
            },
            {
                test: /include.less/,
                loader: createStyleLoader(dev, libStyle, 'css-loader', 'less-loader'),
            },
            {
                test: /\.eot$/,
                loader: 'file-loader',
            },
            {
                test: /\.svg$/,
                loader: 'file-loader',
                query: {
                    limit: 10000,
                    mimetype: 'image/svg+xml',
                    outputPath: !dev && ASSET_OUTPUT_PATH,
                },
            },
            {
                test: /\.ttf$/,
                loader: 'file-loader',
                query: {
                    limit: 10000,
                    mimetype: 'application/octet-stream',
                },
            },
            {
                test: /\.woff2?$/,
                loader: 'url-loader',
                query: {
                    mimetype: 'application/font-woff',
                },
            },
            {
                test: /\.md$/,
                loader: 'raw-loader',
            },
            {
                test: /\.yml$/,
                loader: ['json-loader', 'yaml-loader'],
            }
        ],
    },
    devServer: {
        historyApiFallback: true,
        proxy: {
            '/api': 'http://[::1]:8000',
        }
    },
    optimization: {
        noEmitOnErrors: true,
    },
}));
