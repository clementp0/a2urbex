const Encore = require('@symfony/webpack-encore')
const webpack = require('webpack')

if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev')
}

Encore.setOutputPath('public/build/') // directory where compiled assets will be stored
  .setPublicPath('/build') // public path used by the web server to access the output path

  .addEntry('app-script', './assets/index.js')
  .addStyleEntry('app-style', './assets/index.scss')
  .enableStimulusBridge('./assets/controllers.json')
  .splitEntryChunks()
  .enableSingleRuntimeChunk()

  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())

  .configureBabel((config) => {
    config.plugins.push('@babel/plugin-proposal-class-properties')
  })
  .configureBabelPresetEnv((config) => {
    config.useBuiltIns = 'usage'
    config.corejs = 3
  })

  .enableSassLoader()

  .addPlugin(
    new webpack.ProvidePlugin({
      $: 'jquery',
      jQuery: 'jquery',
      'window.jQuery': 'jquery',
    })
  )

module.exports = Encore.getWebpackConfig()
