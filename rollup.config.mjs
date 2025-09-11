import nodeResolve from '@rollup/plugin-node-resolve';

export default {
    input: 'src/Assets/js/main.js',
    output: {
        file: 'public/js/main.js',
        format: 'cjs'
    },
    plugins: [
        nodeResolve()
    ]
};
