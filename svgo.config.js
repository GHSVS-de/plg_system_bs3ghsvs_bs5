'use strict'

module.exports = {
  multipass: true,
  js2svg: {
    pretty: true,
    indent: 2
  },
  plugins: [
    {
      name: 'preset-default',
      params: {
        overrides: {
          removeUnknownsAndDefaults: {
            keepRoleAttr: true
          },
          removeViewBox: false,
          sortAttrs: true
        }
      }
    },
    // cleanupListOfValues and removeAttrs plugins are included in svgo
    // but are not part of the preset-default, so we need to enable them separately
    'cleanupListOfValues',
    {
      name: 'removeAttrs',
      params: {
        attrs: [
          'clip-rule',
          'data-name',
          'fill'
        ]
      }
    }
  ]
}
