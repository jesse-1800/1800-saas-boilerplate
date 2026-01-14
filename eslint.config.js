import vuetify from 'eslint-config-vuetify'

export default {
  ...vuetify(),
  extends: [
    ...vuetify().extends,
    'plugin:prettier/recommended' // 👈 adds Prettier plugin and disables conflicting ESLint rules
  ],
  rules: {
    ...vuetify().rules,
    'vue/script-indent': 'off', // 👈 disables annoying indentation rule
    'prettier/prettier': 'warn' // 👈 warns (not errors) on Prettier violations
  }
}
