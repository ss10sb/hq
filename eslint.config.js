import pluginJs from "@eslint/js";

export default [
    pluginJs.configs.recommended,
    {
        ignores: [
            "node_modules/**",
            "public/**",
            "vendor/**",
            "bootstrap/cache/**",
            "storage/**",
            "dist/**"
        ],
    },
    {
        files: [
            "resources/js/**/*.js"
        ]
    }
]