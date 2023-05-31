const Encore = require('@symfony/webpack-encore')
const webpack = require('webpack')

function applyConfig(config) {
  config
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
}

Encore.configureDefault = () => {}
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev')
}

Encore.setOutputPath('public/build/') // directory where compiled assets will be stored
  .setPublicPath('/build') // public path used by the web server to access the output path

  // global
  .addStyleEntry('app-style', './assets/index.scss')
  .addEntry('app-script', './assets/index.js')

  // account
  .addStyleEntry('account-style', './assets/scss/page/account.scss')

  // contact
  .addStyleEntry('contact-style', './assets/scss/page/contact.scss')

  // favorite
  .addStyleEntry('favorite-style', './assets/scss/page/favorite.scss')

  // home
  .addStyleEntry('home-style', './assets/scss/page/home.scss')

  // location
  .addStyleEntry('location-style', './assets/scss/page/location.scss')
  .addEntry('location-script', './assets/js/page/location.js')

  // security
  .addStyleEntry('security-style', './assets/scss/page/security.scss')

  // map
  .addStyleEntry('map-style', './assets/scss/page/map.scss')
  .addEntry('map-script', './assets/js/page/map.js')

  // notfound
  .addStyleEntry('error-style', './assets/scss/page/error.scss')

  // friend
  .addStyleEntry('friend-style', './assets/scss/page/friend.scss')

applyConfig(Encore)
const mainConfig = Encore.getWebpackConfig()
mainConfig.name = 'mainConfig'

Encore.reset()

Encore.setOutputPath('build_admin/')
  .setPublicPath('/build_admin/')
  // admin
  .addStyleEntry('admin-style', './assets/scss/page/admin.scss')
  .addEntry('admin-script', './assets/js/page/admin.js')

applyConfig(Encore)
const adminConfig = Encore.getWebpackConfig()
adminConfig.name = 'adminConfig'

module.exports = [mainConfig, adminConfig]
