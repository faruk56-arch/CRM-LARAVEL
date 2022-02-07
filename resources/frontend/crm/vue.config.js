module.exports = {
  devServer: {
    proxy: 'http://laravel.test'
  },
  resolve: {
    symlinks: false
  },

  // output built static files to Laravel's public dir.
  // note the "build" script in package.json needs to be modified as well.
  outputDir: '../../../public/assets/crm',

  publicPath: process.env.NODE_ENV === 'production'
    ? '/assets/crm/'
    : '/',

  // modify the location of the generated HTML file.
  indexPath: process.env.NODE_ENV === 'production'
    ? '../../../resources/views/crm.blade.php'
    : 'index.html'
}
