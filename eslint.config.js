// noinspection NpmUsedModulesInstalled

import importPlugin from "eslint-plugin-import";
import { defineConfig, globalIgnores } from "eslint/config";
import css from "@eslint/css";
import js from "@eslint/js";
import pluginVue from "eslint-plugin-vue";
import stylistic from "@stylistic/eslint-plugin";
import globals from "globals";

export default defineConfig([
  globalIgnores(["node_modules", "vendor"]),
  ...pluginVue.configs["flat/essential"],
  {
    name: "eslint-defaults",
    files: ["./*.js", "./resources/js/**/*.{js,vue}"],
    plugins: { js, "@stylistic": stylistic },
    extends: ["js/recommended", importPlugin.flatConfigs.recommended],
    languageOptions: {
      ecmaVersion: 2020,
      globals: {
        ...globals.browser,
        ...globals.node,
        route: "readonly",
        axios: "readonly",
      },
    },
  },
  {
    name: "eslint-css",
    files: ["./resources/css/**/*.css"],
    plugins: { css },
    language: "css/css",
    rules: {
      "css/no-empty-blocks": "error",
      "css/no-duplicate-imports": "error",
      "css/no-important": "error",
      "css/no-invalid-at-rules": "error",
      "css/no-invalid-properties": "error",
      "css/use-baseline": "warn",
    },
  },
  {
    name: "eslint-js",
    rules: {
      "no-empty": ["warn", { "allowEmptyCatch": true }],
      "no-unused-vars": "warn",
    },
  },
  {
    name: "eslint-imports",
    rules: {
      "import/consistent-type-specifier-style": ["error", "prefer-top-level"],
      "import/default": "off",
      "import/namespace": "off",
      "import/no-named-as-default": "off",
      "import/no-named-as-default-member": "off",
      "import/no-unresolved": "off",
      /**
       * import/order:
       * If the ordering of `eslint --fix` isn't working, it's likely because there is an unbound import.
       * Eg import 'xyz.css' as opposed to import abc from 'abc'.
       * @see https://github.com/import-js/eslint-plugin-import/blob/main/docs/rules/order.md#limitations-of---fix
       */
      "import/order": "warn",
    },
  },
  {
    name: "eslint-vue",
    rules: {
      "vue/block-order": [
        "error",
        {
          order: ["script:not([setup])", "script[setup]", "template", "style"],
        },
      ],
      "vue/block-tag-newline": ["error", {
        blocks: {
          script: {
            singleline: "always",
            multiline: "always",
          },
        },
      }],
      "vue/first-attribute-linebreak": ["error", { singleline: "ignore", multiline: "below" }],
      "vue/html-closing-bracket-newline": [
        "warn",
        {
          singleline: "never",
          multiline: "never",
          selfClosingTag: {
            singleline: "never",
            multiline: "never",
          },
        },
      ],
      "vue/html-comment-indent": ["warn", 2],
      "vue/html-comment-content-newline": [
        "warn",
        {
          singleline: "never",
          multiline: "ignore",
        },
        {
          exceptions: [],
        },
      ],
      "vue/html-comment-content-spacing": ["warn", "always", { exceptions: ["*", "#"] }],
      "vue/html-indent": [
        "warn",
        2,
        {
          attribute: 2,
          baseIndent: 1,
          closeBracket: 0,
          alignAttributesVertically: true,
          ignores: [],
        },
      ],
      "vue/multiline-html-element-content-newline": [
        "error",
        {
          ignoreWhenEmpty: true,
          ignores: ["pre", "textarea"],
          allowEmptyLines: false,
        },
      ],
      "vue/no-irregular-whitespace": ["error"],
      "vue/no-unused-vars": ["warn"],
      "vue/max-attributes-per-line": ["warn", { singleline: 100, multiline: 1 }],
      "vue/multi-word-component-names": ["off"],
      "vue/no-multi-spaces": ["warn", { ignoreProperties: false }],
      "vue/object-curly-spacing": ["warn", "always"],
      "vue/prefer-use-template-ref": ["warn"],
      "vue/padding-line-between-blocks": ["warn", "always"],
      "vue/padding-line-between-tags": [
        "warn",
        [
          { blankLine: "always", prev: "template", next: "*" },
          { blankLine: "always", prev: "*", next: "template" },
        ],
      ],
      "vue/script-indent": ["warn", 2, { baseIndent: 0, switchCase: 1 }],
      "vue/singleline-html-element-content-newline": [
        "error",
        {
          ignoreWhenNoAttributes: true,
          ignoreWhenEmpty: true,
          ignores: ["pre", "textarea", "template"],
          externalIgnores: [],
        },
      ],
      "vue/space-infix-ops": ["warn"],
      "vue/space-unary-ops": [
        "error",
        {
          words: true,
          nonwords: false,
          overrides: { "?": true },
        },
      ],
    },
  },
  {
    name: "eslint-stylistic",
    rules: {
      "@stylistic/array-bracket-newline": ["off"],
      "@stylistic/array-element-newline": ["error", { consistent: true }],
      "@stylistic/arrow-parens": ["error", "always"],
      "@stylistic/brace-style": ["warn", "1tbs"],
      "@stylistic/comma-dangle": ["warn", "always-multiline"],
      "@stylistic/comma-spacing": ["warn"],
      "@stylistic/function-call-argument-newline": ["warn", "consistent"],
      "@stylistic/function-paren-newline": ["warn", "consistent"],
      "@stylistic/indent": [
        "warn",
        2,
        {
          ignoreComments: true,
          SwitchCase: 1,
        },
      ],
      "@stylistic/no-multiple-empty-lines": ["warn", { max: 1, maxEOF: 1, maxBOF: 0 }],
      "@stylistic/no-trailing-spaces": ["warn"],
      "@stylistic/member-delimiter-style": [
        "warn",
        {
          multiline: {
            delimiter: "semi",
            requireLast: true,
          },
          singleline: {
            delimiter: "semi",
            requireLast: false,
          },
        },
      ],
      "@stylistic/multiline-ternary": ["error", "always-multiline"],
      "@stylistic/object-curly-newline": ["off"],
      "@stylistic/object-curly-spacing": ["error", "always"],
      "@stylistic/padding-line-between-statements": [
        "error",
        { blankLine: "always", prev: "*", next: "import" },
        { blankLine: "always", prev: "import", next: "*" },
        { blankLine: "never", prev: "import", next: "import" },
      ],
      "@stylistic/quotes": ["warn", "double"],
      "@stylistic/semi": ["warn", "always"],
      "@stylistic/space-before-function-paren": [
        "warn",
        {
          anonymous: "never",
          named: "never",
          asyncArrow: "always",
        },
      ],
      "spaced-comment": [
        "error",
        "always",
        {
          line: {
            markers: ["/", "##", "todo", "TODO"],
            exceptions: ["-", "+"],
          },
          block: {
            markers: ["!"],
            exceptions: ["*"],
            balanced: true,
          },
        },
      ],
      "@stylistic/space-unary-ops": ["error"],
    },
  },
]);
