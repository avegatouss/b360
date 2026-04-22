// commitlint.config.js
// Validation Conventional Commits avec scopes B360
// Utilisé en complément du hook commit-msg pour intégration éditeur / CI tiers.

module.exports = {
  extends: ['@commitlint/config-conventional'],
  rules: {
    'type-enum': [
      2,
      'always',
      [
        'feat',
        'fix',
        'refactor',
        'perf',
        'test',
        'docs',
        'chore',
        'build',
        'ci',
        'style',
        'revert',
        'security',
      ],
    ],
    'scope-enum': [
      2,
      'always',
      [
        // Modules existants
        'core', 'auth', 'users', 'instances', 'settings', 'billing',
        'dashboard', 'lang', 'currency', 'modulemanager', 'installer',
        'demo', 'eshop360',

        // Sous-domaines Eshop360 (en cours d'extraction)
        'catalog', 'pricing', 'inventory', 'sales', 'purchase',
        'invoicing', 'finance', 'crm', 'channel', 'reporting',
        'hr', 'projects', 'communication',

        // Plateforme cible
        'platform', 'tenancy', 'iam', 'feature-control', 'observability',
        'workflow', 'integration-hub', 'file-center', 'shared-kernel',
        'ui-kit', 'api',

        // Transverse
        'deps', 'ci', 'docs', 'governance', 'security', 'perf',
        'tests', 'infra', 'release', 'pack',
      ],
    ],
    'scope-empty': [2, 'never'], // scope obligatoire
    'subject-case': [
      2,
      'never',
      ['sentence-case', 'start-case', 'pascal-case', 'upper-case'],
    ],
    'subject-empty': [2, 'never'],
    'subject-full-stop': [2, 'never', '.'],
    'header-max-length': [2, 'always', 72],
    'body-leading-blank': [2, 'always'],
    'body-max-line-length': [1, 'always', 100],
    'footer-leading-blank': [2, 'always'],
  },
};
