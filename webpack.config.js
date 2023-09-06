const Encore = require('@symfony/webpack-encore')
const webpack = require('webpack')
const BrowserSyncPlugin = require('browser-sync-webpack-plugin')
const dotenv = require('dotenv')
const fs = require('fs')
const path = require('path')

const envPath = path.resolve(__dirname, '.env.local')
const dotenvPath = fs.existsSync(envPath) ? envPath : path.resolve(__dirname, '.env')
dotenv.config({ path: dotenvPath })

const port = process.env.WEBPACK_PORT
const adminPort = process.env.WEBPACK_ADMIN_PORT

function applyEncore(encore, port) {
  encore
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
    .addPlugin(
      new BrowserSyncPlugin(
        {
          host: 'localhost',
          port: port,
          ghostMode: false,
          ui: {
            port: parseInt(port) + 1,
          },
          files: [
            {
              match: ['src/**/*.php'],
            },
            {
              match: ['templates/**/*.twig'],
            },
            {
              match: ['assets/**/*.js'],
            },
            {
              match: ['assets/**/*.scss'],
            },
          ],
          notify: false,
        },
        {
          reload: true,
        }
      )
    )
    .addPlugin(
      new webpack.DefinePlugin({
        websocket_debug: process.env.WEBSOCKET_DEBUG,
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
  .addEntry('account-script', './assets/js/page/account.js')

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

  // status
  .addStyleEntry('status-style', './assets/scss/page/status.scss')


applyEncore(Encore, port)
const mainConfig = Encore.getWebpackConfig()
mainConfig.name = 'mainConfig'

Encore.reset()

Encore.setOutputPath('private/build/')
  .setPublicPath('/private/build/')
  // admin
  .addStyleEntry('admin-style', './assets/scss/page/admin.scss')
  .addEntry('admin-script', './assets/js/page/admin.js')

applyEncore(Encore, adminPort)
const adminConfig = Encore.getWebpackConfig()
adminConfig.name = 'adminConfig'

module.exports = [mainConfig, adminConfig]
