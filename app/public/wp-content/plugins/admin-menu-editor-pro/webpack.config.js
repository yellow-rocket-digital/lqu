import webpack from 'webpack';
import * as path from "path";
import * as url from "url";

// noinspection JSUnusedGlobalSymbols - Webpack uses this function to get the config.
export default (env, argv) => {
	const currentMode = (argv.mode === 'production') ? 'production' : 'development';
	const __dirname = url.fileURLToPath(new URL('.', import.meta.url));

	return {
		mode: currentMode,
		//Just for testing; add a real entry point later.
		entry: './extras/pro-customizables/ko-components/ame-si-structure/ame-si-structure.ts',
		output: {
			path: path.resolve(__dirname, 'dist'),
			filename: 'bundle.js'
		},
		plugins: [
			new webpack.DefinePlugin({
				AME_IS_PRODUCTION: JSON.stringify(currentMode === 'production'),
			}),
		],
		devtool: 'source-map',
		module: {
			rules: [
				{
					test: /\.ts$/,
					use: {
						loader: 'ts-loader',
						options: {
							configFile: path.resolve(__dirname, 'tsconfig.json')
						}
					},
					exclude: /node_modules/
				}
			]
		},
		resolve: {
			extensions: ['.ts', '.js']
		}
	};
};