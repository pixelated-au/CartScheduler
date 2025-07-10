// noinspection NpmUsedModulesInstalled

import { resolve } from "node:path";
import css from "@eslint/css";
import js from "@eslint/js";
import stylistic from "@stylistic/eslint-plugin";
import { configureVueProject, defineConfigWithVueTs, vueTsConfigs } from "@vue/eslint-config-typescript";
import { globalIgnores } from "eslint/config";
import { createTypeScriptImportResolver } from "eslint-import-resolver-typescript";
import { importX } from "eslint-plugin-import-x";
import pluginVue from "eslint-plugin-vue";
import globals from "globals";

configureVueProject({
  scriptLangs: ["ts", "js"],
  rootDir: resolve(import.meta.dirname),
});

export default defineConfigWithVueTs([
  importX.flatConfigs.recommended,
  importX.flatConfigs.typescript,
  globalIgnores(["node_modules", "vendor", "public"]),
  {
    name: "eslint-defaults",
    files: ["./*.{ts,js}", "./resources/js/**/*.{ts,js,vue}"],
    ignores: ["*.d.ts"],
    plugins: { "@stylistic": stylistic, "import-x": importX },
    extends: [pluginVue.configs["flat/essential"], vueTsConfigs.recommended],
    settings: {
      "import-x/resolver-next": [createTypeScriptImportResolver()],
    },
    languageOptions: {
      ecmaVersion: 2022,
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
    files: ["./*.js", "./resources/js/**/*.js,vue"],
    rules: {
      "no-empty": ["warn", { allowEmptyCatch: true }],
      "no-unused-expressions": ["warn", { allowShortCircuit: true, allowTernary: true }],
      "no-unused-vars": "warn",
    },
  },
  {
    name: "eslint-ts",
    rules: {
      "@typescript-eslint/ban-ts-comment": ["warn", { "ts-nocheck": "allow-with-description" }],
      "@typescript-eslint/consistent-type-imports": [
        "warn",
        {
          prefer: "type-imports",
          fixStyle: "separate-type-imports",
          disallowTypeAnnotations: true,
        },
      ],
      "@typescript-eslint/no-empty-object-type": "warn",
      "@typescript-eslint/no-import-type-side-effects": "warn",
      "@typescript-eslint/no-unused-expressions": ["warn", { allowShortCircuit: true, allowTernary: true }],
      "@typescript-eslint/no-unused-vars": "warn",
    },
  },
  {
    name: "eslint-import-x",
    rules: {
      "import-x/consistent-type-specifier-style": ["error", "prefer-top-level"],
      "import-x/default": "off",
      "import-x/namespace": "off",
      "import-x/no-named-as-default": "off",
      "import-x/no-named-as-default-member": "off",
      "import-x/no-unresolved": "off",
      /**
       * import/order:
       * If the ordering of `eslint --fix` isn't working, it's likely because there is an unbound import.
       * Eg import 'xyz.css' as opposed to import abc from 'abc'.
       * @see https://github.com/un-ts/eslint-plugin-import-x/blob/master/docs/rules/order.md#limitations-of---fix
       */
      "import-x/order": [
        "warn",
        {
          groups: [
            "builtin",
            "external",
            "internal",
            "unknown",
            ["parent", "sibling"],
            "index",
            "object",
            "type",
          ],
          alphabetize: {
            order: "asc",
            orderImportKind: "asc",
            caseInsensitive: true,
          },
          sortTypesGroup: true,
        },
      ],
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
      "vue/block-tag-newline": [
        "error",
        {
          blocks: {
            script: {
              singleline: "always",
              multiline: "always",
            },
            template: {
              singleline: "always",
              multiline: "always",
              maxEmptyLines: 0,
            },
          },
        },
      ],
      "vue/first-attribute-linebreak": ["warn", { singleline: "ignore", multiline: "beside" }],
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
      "vue/html-comment-content-spacing": ["warn", "always", { exceptions: ["*", "#", "suppress"] }],
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
      "@stylistic/multiline-ternary": ["warn", "always-multiline"],
      "@stylistic/object-curly-newline": ["off"],
      "@stylistic/object-curly-spacing": ["warn", "always"],
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
        "warn",
        "always",
        {
          line: {
            markers: ["/", "##", "todo", "TODO", "suppress"],
            exceptions: ["-", "+"],
          },
          block: {
            markers: ["!"],
            exceptions: ["*"],
            balanced: true,
          },
        },
      ],
      "@stylistic/space-unary-ops": ["warn"],
    },
  },
]);
